#!/usr/bin/env bash
# WordPress setup for artifact testing.
# Installs the plugin from the built zip at /tmp/ootb-artifact/plugin.zip
# rather than activating it from a source volume mount.
#
# Usage: bash scripts/wp-artifact-setup.sh
# Requires: docker compose -f docker-compose.artifact.yml up -d already running.
set -euo pipefail

COMPOSE_FILE="docker-compose.artifact.yml"
WP_URL="http://localhost:8080"

wp() {
  docker compose -f "${COMPOSE_FILE}" exec -T cli wp --allow-root --path=/var/www/html "$@"
}

echo "==> Waiting for WordPress and database..."
MAX_TRIES=60
TRIES=0
until curl -sf "${WP_URL}/wp-login.php" > /dev/null 2>&1 || [ $TRIES -ge $MAX_TRIES ]; do
  sleep 2
  TRIES=$((TRIES + 1))
  if [ $((TRIES % 10)) -eq 0 ]; then
    echo "    Still waiting... ($TRIES seconds)"
  fi
done

if [ $TRIES -ge $MAX_TRIES ]; then
  echo "ERROR: WordPress failed to start after ${MAX_TRIES} attempts"
  echo "Run 'docker compose -f ${COMPOSE_FILE} logs wordpress' to see what went wrong"
  exit 1
fi

echo "==> WordPress is ready!"
echo "==> Installing WordPress..."
wp core install \
  --url="${WP_URL}" \
  --title="OOTB Test Site" \
  --admin_user="admin" \
  --admin_password="password" \
  --admin_email="admin@example.com" \
  --skip-email || echo "Already installed."

echo "==> Copying artifact zip into CLI container..."
# docker cp is more reliable than a bind-mount for /tmp paths across environments.
# wp plugin install is intentionally avoided: it routes through WordPress's upgrade
# machinery which requires a writable wp-content/upgrade directory (often absent
# in fresh Docker installs). Extracting directly sidesteps that entirely.
CLI_CONTAINER=$(docker compose -f "${COMPOSE_FILE}" ps -q cli)
docker cp /tmp/ootb-artifact/plugin.zip "${CLI_CONTAINER}:/tmp/plugin.zip"

echo "==> Extracting plugin into wp-content/plugins/..."
# Run as root — the CLI image defaults to www-data, which may not have write
# access to wp-content/plugins/ in a fresh Docker volume. Root always can.
docker compose -f "${COMPOSE_FILE}" exec -T -u root cli php -r "
  \$z = new ZipArchive;
  if (\$z->open('/tmp/plugin.zip') !== true) { fwrite(STDERR, 'ERROR: Could not open zip.' . PHP_EOL); exit(1); }
  \$z->extractTo('/var/www/html/wp-content/plugins/');
  \$z->close();
  echo 'Plugin extracted.' . PHP_EOL;
"
# Restore www-data ownership so WordPress can manage the plugin normally.
docker compose -f "${COMPOSE_FILE}" exec -T -u root cli \
  chown -R www-data:www-data /var/www/html/wp-content/plugins/ootb-openstreetmap

echo "==> Activating plugin..."
wp plugin activate ootb-openstreetmap || {
  echo "ERROR: Plugin activation failed."
  exit 1
}

echo "==> Setting default plugin options..."
wp option update ootb_options '{"prevent_default_gestures":"","api_mapbox":"","api_openai":"","global_mapbox_style_url":""}' --format=json

echo "==> Creating test page..."
MARKER_ICON='%7B%22iconUrl%22%3A%22http%3A%2F%2Flocalhost%3A8080%2Fwp-content%2Fplugins%2Footb-openstreetmap%2Fassets%2Fvendor%2Fleaflet%2Fimages%2Fmarker-icon.png%22%2C%22iconAnchor%22%3A%5B12%2C41%5D%2C%22popupAnchor%22%3A%5B0%2C-41%5D%7D'
MARKERS='%5B%7B%22id%22%3A%22marker-test-1%22%2C%22lat%22%3A%2237.9838%22%2C%22lng%22%3A%2223.7275%22%2C%22title%22%3A%22Test%20Marker%20Athens%22%2C%22content%22%3A%22This%20is%20a%20deterministic%20test%20marker.%22%2C%22icon%22%3A%22%22%2C%22text%22%3A%22This%20is%20a%20deterministic%20test%20marker.%22%7D%5D'
BOUNDS='[37.9838,23.7275]'
SHAPE_STYLE='%7B%22fillColor%22%3A%22%23008EFF%22%2C%22color%22%3A%22%23008EFF%22%2C%22weight%22%3A3%7D'
TEST_PAGE_CONTENT="<!-- wp:ootb/openstreetmap {\"mapId\":\"ootb-test-map-1\",\"lat\":\"37.9838\",\"lng\":\"23.7275\",\"zoom\":13,\"markers\":[{\"id\":\"marker-test-1\",\"lat\":\"37.9838\",\"lng\":\"23.7275\",\"title\":\"Test Marker Athens\",\"content\":\"This is a deterministic test marker.\",\"icon\":\"\"}],\"provider\":\"OpenStreetMap.Mapnik\",\"gestureHandling\":false} -->
<div class=\"wp-block-ootb-openstreetmap\"><div class=\"ootb-openstreetmap--map\" data-provider=\"openstreetmap\" data-maptype=\"marker\" data-showmarkers=\"true\" data-shapestyle=\"${SHAPE_STYLE}\" data-shapetext=\"\" data-markers=\"${MARKERS}\" data-bounds=\"${BOUNDS}\" data-zoom=\"13\" data-minzoom=\"2\" data-maxzoom=\"18\" data-dragging=\"true\" data-touchzoom=\"true\" data-doubleclickzoom=\"true\" data-scrollwheelzoom=\"true\" data-marker=\"${MARKER_ICON}\" style=\"height: 400px\"></div></div>
<!-- /wp:ootb/openstreetmap -->"

EXISTING_ID=$(wp post list --post_type=page --name=test-map --field=ID --format=ids 2>/dev/null || true)

if [ -n "${EXISTING_ID}" ]; then
  wp post update "${EXISTING_ID}" --post_content="${TEST_PAGE_CONTENT}" --post_status=publish
else
  wp post create \
    --post_type=page \
    --post_title="Test Map" \
    --post_name="test-map" \
    --post_status=publish \
    --post_content="${TEST_PAGE_CONTENT}" \
    --porcelain
fi

echo "==> Flushing rewrites..."
wp rewrite structure '/%postname%/' --hard

echo ""
echo "==> Done. WordPress at ${WP_URL} (admin / password)"
echo "    Test page: ${WP_URL}/test-map/"

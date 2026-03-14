#!/usr/bin/env bash
set -euo pipefail

PLUGIN_DIR="/var/www/html/wp-content/plugins/ootb-openstreetmap"
WP_URL="http://localhost:8080"

wp() {
  docker compose exec -T cli wp --allow-root --path=/var/www/html "$@"
}

echo "==> Waiting for WordPress and database..."
MAX_TRIES=60
TRIES=0
until curl -sf "${WP_URL}/wp-login.php" > /dev/null 2>&1 || [ $TRIES -ge $MAX_TRIES ]; do
  sleep 2
  TRIES=$((TRIES + 1))
  if [ $((TRIES % 10)) -eq 0 ]; then
    echo "    Still waiting... ($((TRIES * 2)) seconds elapsed)"
  fi
done

if [ $TRIES -ge $MAX_TRIES ]; then
  echo "ERROR: WordPress failed to start after ${MAX_TRIES} attempts"
  echo "Run 'docker compose logs wordpress' to see what went wrong"
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

echo "==> Activating plugin..."
wp plugin activate ootb-openstreetmap || {
  echo "ERROR: Plugin activation failed."
  exit 1
}

echo "==> Setting default plugin options..."
wp option update ootb_options '{"prevent_default_gestures":"","api_mapbox":"","api_openai":"","global_mapbox_style_url":""}' --format=json

echo "==> Creating test page..."
# Use full block format with inner HTML (not self-closing) so render_callback receives content.
# This mimics what the block editor saves when the block has markers.
# data-marker: url-encoded JSON for default Leaflet icon
# data-markers: url-encoded JSON array with marker (lat, lng, text for popup)
# data-bounds: url-encoded JSON [lat, lng] for map center
MARKER_ICON='%7B%22iconUrl%22%3A%22http%3A%2F%2Flocalhost%3A8080%2Fwp-content%2Fplugins%2Footb-openstreetmap%2Fassets%2Fvendor%2Fleaflet%2Fimages%2Fmarker-icon.png%22%2C%22iconAnchor%22%3A%5B12%2C41%5D%2C%22popupAnchor%22%3A%5B0%2C-41%5D%7D'
MARKERS='%5B%7B%22id%22%3A%22marker-test-1%22%2C%22lat%22%3A%2237.9838%22%2C%22lng%22%3A%2223.7275%22%2C%22title%22%3A%22Test%20Marker%20Athens%22%2C%22content%22%3A%22This%20is%20a%20deterministic%20test%20marker.%22%2C%22icon%22%3A%22%22%2C%22text%22%3A%22This%20is%20a%20deterministic%20test%20marker.%22%7D%5D'
# data-bounds: plain JSON (view.js uses JSON.parse without decodeURIComponent)
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

echo "==> Creating clustering test page..."
# Three markers very close together (within ~15m) so they always cluster regardless of zoom.
# data-enableclustering="true" activates the cluster group.
CLUSTER_MARKERS='%5B%7B%22id%22%3A%22mc-1%22%2C%22lat%22%3A%2237.9838%22%2C%22lng%22%3A%2223.7275%22%2C%22title%22%3A%22C1%22%2C%22content%22%3A%22%22%2C%22icon%22%3A%22%22%2C%22text%22%3A%22%22%7D%2C%7B%22id%22%3A%22mc-2%22%2C%22lat%22%3A%2237.9839%22%2C%22lng%22%3A%2223.7276%22%2C%22title%22%3A%22C2%22%2C%22content%22%3A%22%22%2C%22icon%22%3A%22%22%2C%22text%22%3A%22%22%7D%2C%7B%22id%22%3A%22mc-3%22%2C%22lat%22%3A%2237.9837%22%2C%22lng%22%3A%2223.7274%22%2C%22title%22%3A%22C3%22%2C%22content%22%3A%22%22%2C%22icon%22%3A%22%22%2C%22text%22%3A%22%22%7D%5D'
CLUSTER_BOUNDS='[37.9838,23.7275]'
CLUSTER_PAGE_CONTENT="<!-- wp:ootb/openstreetmap {\"mapId\":\"ootb-cluster-map-1\",\"lat\":\"37.9838\",\"lng\":\"23.7275\",\"zoom\":12,\"markers\":[{\"id\":\"mc-1\",\"lat\":\"37.9838\",\"lng\":\"23.7275\",\"title\":\"C1\",\"content\":\"\",\"icon\":\"\"},{\"id\":\"mc-2\",\"lat\":\"37.9839\",\"lng\":\"23.7276\",\"title\":\"C2\",\"content\":\"\",\"icon\":\"\"},{\"id\":\"mc-3\",\"lat\":\"37.9837\",\"lng\":\"23.7274\",\"title\":\"C3\",\"content\":\"\",\"icon\":\"\"}],\"provider\":\"OpenStreetMap.Mapnik\",\"enableClustering\":true,\"serverSideRender\":false} -->
<div class=\"wp-block-ootb-openstreetmap\"><div class=\"ootb-openstreetmap--map\" data-provider=\"openstreetmap\" data-maptype=\"marker\" data-showmarkers=\"true\" data-shapestyle=\"${SHAPE_STYLE}\" data-shapetext=\"\" data-markers=\"${CLUSTER_MARKERS}\" data-bounds=\"${CLUSTER_BOUNDS}\" data-zoom=\"12\" data-minzoom=\"2\" data-maxzoom=\"18\" data-dragging=\"true\" data-touchzoom=\"true\" data-doubleclickzoom=\"true\" data-scrollwheelzoom=\"true\" data-fullscreen=\"false\" data-enableclustering=\"true\" data-marker=\"${MARKER_ICON}\" style=\"height: 400px\"></div></div>
<!-- /wp:ootb/openstreetmap -->"

EXISTING_CLUSTER_ID=$(wp post list --post_type=page --name=test-map-cluster --field=ID --format=ids 2>/dev/null || true)

if [ -n "${EXISTING_CLUSTER_ID}" ]; then
  wp post update "${EXISTING_CLUSTER_ID}" --post_content="${CLUSTER_PAGE_CONTENT}" --post_status=publish
else
  wp post create \
    --post_type=page \
    --post_title="Test Map Cluster" \
    --post_name="test-map-cluster" \
    --post_status=publish \
    --post_content="${CLUSTER_PAGE_CONTENT}" \
    --porcelain
fi

echo "==> Flushing rewrites..."
wp rewrite structure '/%postname%/' --hard

echo "==> Installing WP test suite..."
docker compose exec -T cli bash -c "cd ${PLUGIN_DIR} && bash bin/install-wp-tests.sh wordpress_test wordpress wordpress db latest" || true

echo ""
echo "==> Done. WordPress at ${WP_URL} (admin / password)"
echo "    Test page: ${WP_URL}/test-map/"
echo "    Run: make test | make playwright | make update-snapshots"

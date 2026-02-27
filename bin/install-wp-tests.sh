#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${1:-wordpress_test}"
DB_USER="${2:-wordpress}"
DB_PASS="${3:-wordpress}"
DB_HOST="${4:-localhost}"
WP_VERSION="${5:-latest}"
WP_TESTS_DIR="${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}"
WP_CORE_DIR="${WP_CORE_DIR:-/tmp/wordpress}"

if [[ "$WP_VERSION" == "latest" ]]; then
  WP_VERSION=$(curl -s https://api.wordpress.org/core/version-check/1.7/ \
    | grep -o '"version":"[^"]*"' | head -1 | cut -d'"' -f4) || true
elif [[ "$WP_VERSION" =~ ^[0-9]+\.[0-9]+$ ]]; then
  # Resolve X.Y to the latest X.Y.Z patch — GitHub tags don't have bare X.Y entries
  RESOLVED=$(curl -s "https://api.wordpress.org/core/version-check/1.7/" \
    | grep -o '"version":"[^"]*"' | cut -d'"' -f4 \
    | grep "^${WP_VERSION}\." | head -1) || true
  [[ -n "$RESOLVED" ]] && WP_VERSION="$RESOLVED"
fi

download() {
  if command -v curl &> /dev/null; then curl -sL "$1" > "$2"
  else wget -nv -O "$2" "$1"; fi
}

if [ ! -d "$WP_CORE_DIR" ]; then
  mkdir -p "$WP_CORE_DIR"
  download "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" /tmp/wordpress.tar.gz
  tar --strip-components=1 -zxf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"
fi

if [ ! -d "$WP_TESTS_DIR/includes" ]; then
  mkdir -p "$WP_TESTS_DIR"
  ARCHIVE="/tmp/wordpress-develop-${WP_VERSION}.tar.gz"
  download "https://github.com/WordPress/wordpress-develop/archive/refs/tags/${WP_VERSION}.tar.gz" "$ARCHIVE"
  tar -zxf "$ARCHIVE" -C "$WP_TESTS_DIR" --strip-components=3 \
    "wordpress-develop-${WP_VERSION}/tests/phpunit/includes"
  tar -zxf "$ARCHIVE" -C "$WP_TESTS_DIR" --strip-components=3 \
    "wordpress-develop-${WP_VERSION}/tests/phpunit/data"
  rm -f "$ARCHIVE"
fi

if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
  download \
    "https://raw.githubusercontent.com/WordPress/wordpress-develop/${WP_VERSION}/wp-tests-config-sample.php" \
    "${WP_TESTS_DIR}/wp-tests-config.php"
  sed -i "s|dirname( __FILE__ ) . '/src/'|'${WP_CORE_DIR}/'|" "${WP_TESTS_DIR}/wp-tests-config.php"
  sed -i "s|youremptytestdbnamehere|${DB_NAME}|"              "${WP_TESTS_DIR}/wp-tests-config.php"
  sed -i "s|yourusernamehere|${DB_USER}|"                     "${WP_TESTS_DIR}/wp-tests-config.php"
  sed -i "s|yourpasswordhere|${DB_PASS}|"                     "${WP_TESTS_DIR}/wp-tests-config.php"
  sed -i "s|localhost|${DB_HOST}|"                            "${WP_TESTS_DIR}/wp-tests-config.php"
fi

mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS" \
  --host="$DB_HOST" 2>/dev/null || true

echo "WP test suite installed at ${WP_TESTS_DIR}"

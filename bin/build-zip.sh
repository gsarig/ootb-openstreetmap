#!/bin/bash

# Exit if any command fails
set -e

# Change to the project root directory
cd "$(dirname "$0")/.."

# Name of the plugin
PLUGIN_SLUG="ootb-openstreetmap"

# 1. Determine Version
# ------------------------------------------------------------------------------

# Use the first argument as the version if provided
if [ ! -z "$1" ]; then
	VERSION="$1"
	echo "Using provided version: $VERSION"
else
	# Otherwise, extract it from the main PHP file
	if [ -f "ootb-openstreetmap.php" ]; then
		# Extract version: looking for " * Version: X.Y.Z"
		# We use awk to find the line with "Version:" and print the last field
		VERSION=$(grep "Version:" ootb-openstreetmap.php | awk '{print $NF}' | tr -d '\r')
		echo "Detected version from file: $VERSION"
	else
		echo "Error: ootb-openstreetmap.php not found!"
		exit 1
	fi
fi

if [ -z "$VERSION" ]; then
	echo "Error: Could not determine version."
	exit 1
fi

ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"
BUILD_DIR="${PLUGIN_SLUG}"

echo "Building $ZIP_NAME..."

# 2. Build Assets
# ------------------------------------------------------------------------------
echo "Installing JS dependencies and building assets..."
npm ci && npm run build

# 3. Cleanup Previous Builds
# ------------------------------------------------------------------------------
rm -rf "$BUILD_DIR"
rm -f "$ZIP_NAME"

# 4. Create Build Directory & Copy Files
# ------------------------------------------------------------------------------
mkdir -p "$BUILD_DIR"

# Check for .distignore
RSYNC_EXCLUDE=""
if [ -f ".distignore" ]; then
	RSYNC_EXCLUDE="--exclude-from=.distignore"
fi

# Sync files to the build directory.
# Exclude root vendor/ only (not assets/vendor/ which has Leaflet) — Composer vendor
# is regenerated from scratch with --no-dev below.
rsync -av $RSYNC_EXCLUDE \
	--exclude="${BUILD_DIR}" \
	--exclude="*.zip" \
	--exclude="/vendor/" \
	./ "$BUILD_DIR/"

# 5. Install Production Composer Dependencies
# ------------------------------------------------------------------------------
# composer.json and composer.lock are distignored, so copy them in temporarily,
# run composer inside the build directory (leaving the workspace vendor/ untouched),
# then remove the manifest files before zipping.
echo "Installing production Composer dependencies..."
cp composer.json composer.lock "$BUILD_DIR/"
(cd "$BUILD_DIR" && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress)
rm "$BUILD_DIR/composer.json" "$BUILD_DIR/composer.lock"

# 6. Create Zip
# ------------------------------------------------------------------------------
# Zip the build directory
zip -q -r "$ZIP_NAME" "$BUILD_DIR"

# 7. Cleanup
# ------------------------------------------------------------------------------
rm -rf "$BUILD_DIR"

echo "-----------------------------------------------------------------"
echo "Success! Plugin zip created: $ZIP_NAME"
echo "-----------------------------------------------------------------"

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

# 2. Cleanup Previous Builds
# ------------------------------------------------------------------------------
rm -rf "$BUILD_DIR"
rm -f "$ZIP_NAME"

# 3. Create Build Directory & Copy Files
# ------------------------------------------------------------------------------
mkdir -p "$BUILD_DIR"

# Check for .distignore
RSYNC_EXCLUDE=""
if [ -f ".distignore" ]; then
	RSYNC_EXCLUDE="--exclude-from=.distignore"
fi

# Sync files to the build directory
# Exclude the build directory itself and the final zip
rsync -av $RSYNC_EXCLUDE \
	--exclude="${BUILD_DIR}" \
	--exclude="*.zip" \
	./ "$BUILD_DIR/"

# 4. Create Zip
# ------------------------------------------------------------------------------
# Zip the build directory
zip -q -r "$ZIP_NAME" "$BUILD_DIR"

# 5. Cleanup
# ------------------------------------------------------------------------------
rm -rf "$BUILD_DIR"

echo "-----------------------------------------------------------------"
echo "Success! Plugin zip created: $ZIP_NAME"
echo "-----------------------------------------------------------------"

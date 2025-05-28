#!/bin/bash

# Define the plugin name and version
PLUGIN_NAME="easy-locations"
VERSION="0.0.2"

# Define the build directory
BUILD_DIR="build"
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"

# Required packaged plugins
REQUIRED_PLUGINS=(
    "assets/packaged-plugins/advanced-custom-fields-pro.zip"
)

# Check for required packaged plugins
for plugin in "${REQUIRED_PLUGINS[@]}"; do
    if [ ! -f "$plugin" ]; then
        echo "Error: Required plugin $plugin not found!"
        exit 1
    fi
done

# Create build directory if it doesn't exist
mkdir -p "$BUILD_DIR"

# Create a temporary directory for building
TEMP_DIR="$BUILD_DIR/easy-locations"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Copy required files and directories
cp -r src assets vendor easy-locations.php uninstall.php README.txt LICENSE.txt index.php "$TEMP_DIR/"

# Remove development files
rm -rf "$TEMP_DIR/.git" "$TEMP_DIR/.gitignore" "$TEMP_DIR/build" "$TEMP_DIR/build.sh" "$TEMP_DIR/composer.json" "$TEMP_DIR/composer.lock" "$TEMP_DIR/node_modules" "$TEMP_DIR/tests"
find "$TEMP_DIR" -name "*.md" -delete
find "$TEMP_DIR" -name "*.log" -delete
# Don't delete zip files in the packaged-plugins directory
find "$TEMP_DIR" -path "*/packaged-plugins/*.zip" -prune -o -name "*.zip" -delete

# Verify packaged plugins are included
if [ ! -f "$TEMP_DIR/assets/packaged-plugins/advanced-custom-fields-pro.zip" ]; then
    echo "Error: Packaged plugins were not copied correctly!"
    exit 1
fi

# Create the zip file
zip -r "../../$BUILD_DIR/$ZIP_NAME" "$TEMP_DIR"

# Clean up
rm -rf "$TEMP_DIR"

echo "Created $ZIP_NAME successfully!" 
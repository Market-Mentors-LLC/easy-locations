#!/bin/bash

# Define the plugin name and version
PLUGIN_NAME="easy-locations"
VERSION="0.3.02"

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
TEMP_DIR="$BUILD_DIR/temp"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR/$PLUGIN_NAME"

# Copy required files and directories
cp -r src assets vendor easy-locations.php uninstall.php README.txt LICENSE.txt index.php "$TEMP_DIR/$PLUGIN_NAME/"

# Remove development files
rm -rf "$TEMP_DIR/$PLUGIN_NAME/.git" "$TEMP_DIR/$PLUGIN_NAME/.gitignore" "$TEMP_DIR/$PLUGIN_NAME/build" "$TEMP_DIR/$PLUGIN_NAME/build.php" "$TEMP_DIR/$PLUGIN_NAME/composer.json" "$TEMP_DIR/$PLUGIN_NAME/composer.lock" "$TEMP_DIR/$PLUGIN_NAME/node_modules" "$TEMP_DIR/$PLUGIN_NAME/tests"
find "$TEMP_DIR/$PLUGIN_NAME" -name "*.md" -delete
find "$TEMP_DIR/$PLUGIN_NAME" -name "*.log" -delete
# Don't delete zip files in the packaged-plugins directory
find "$TEMP_DIR/$PLUGIN_NAME" -path "*/packaged-plugins/*.zip" -prune -o -name "*.zip" -delete

# Verify packaged plugins are included
if [ ! -f "$TEMP_DIR/$PLUGIN_NAME/assets/packaged-plugins/advanced-custom-fields-pro.zip" ]; then
    echo "Error: Packaged plugins were not copied correctly!"
    exit 1
fi

# Create the zip file
cd "$TEMP_DIR" && zip -r "../../$BUILD_DIR/$ZIP_NAME" .

# Clean up
cd ../..
rm -rf "$TEMP_DIR"

echo "Created $ZIP_NAME successfully!" 
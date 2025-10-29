<?php

namespace MarketMentors\EasyLocations\src\integrations\shortcode;

use MarketMentors\EasyLocations\src\models\Location;

class MapSimple
{
  public function __construct()
  {
    add_shortcode('easy-locations-map-simple', [$this, 'render']);
  }

  public function render($atts)
  {
    $atts = shortcode_atts([
      'id' => 'easy_locations_map_simple',
    ], $atts);

    // Get locations from WordPress
    $locations = Location::get_all_locations();

    // Encode locations for JavaScript
    $locations_json = wp_json_encode($locations);

    ob_start();
?>
    <div id="map" style="width: 100%; height: 500px;"></div>

    <style>
      /* Basic CSS for the map container */
      #map {
        width: 100%;
        height: 500px;
        /* Adjust height as needed */
        border-radius: 8px;
        /* Added rounded corners */
        overflow: hidden;
        /* Ensures content respects border-radius */
      }
    </style>

    <script>
      // Get locations from WordPress
      var storeLocations = <?php echo $locations_json; ?>;

      /** @type {'svg' | 'png'} */
      const icon_mode = 'png';

      // Define colors for each category
      var categoryDecorations = {
        "Asphalt Emulsion Plant": {
          color: "#FF0000", // Red
          icon_svg: `<?= file_get_contents(EASY_LOCATIONS_PLUGIN_DIR . 'src/public/media/icons-v2/asphalt.svg'); ?>`,
          icon_png: `<img src="<?= \EASY_LOCATIONS_PLUGIN_URL . 'src/public/media/icons-v2/ASMG_SQ.png'; ?>" alt="map icon" />`
        },
        "Ready Mix Concrete Plant": {
          color: "#00FF00", // Green
          icon_svg: `<?= file_get_contents(EASY_LOCATIONS_PLUGIN_DIR . 'src/public/media/icons-v2/readymix.svg'); ?>`,
          icon_png: `<img src="<?= \EASY_LOCATIONS_PLUGIN_URL . 'src/public/media/icons-v2/ASMG_SQ.png'; ?>" alt="map icon" />`
        },
        "Aggregate Quarry": {
          color: "#0000FF", // Blue
          icon_svg: `<?= file_get_contents(EASY_LOCATIONS_PLUGIN_DIR . 'src/public/media/icons-v2/quarry.svg'); ?>`,
          icon_png: `<img src="<?= \EASY_LOCATIONS_PLUGIN_URL . 'src/public/media/icons-v2/ASMG_SQ.png'; ?>" alt="map icon" />`
        },
        "Office / Operations Facility": {
          color: "#FFFF00", // Yellow
          icon_svg: `<?= file_get_contents(EASY_LOCATIONS_PLUGIN_DIR . 'src/public/media/icons-v2/offices.svg'); ?>`,
          icon_png: `<img src="<?= \EASY_LOCATIONS_PLUGIN_URL . 'src/public/media/icons-v2/ASMG_SQ.png'; ?>" alt="map icon" />`
        },
        "Liquid Asphalt Terminal": {
          color: "#FFA500", // Orange
          icon_svg: `<?= file_get_contents(EASY_LOCATIONS_PLUGIN_DIR . 'src/public/media/icons-v2/liquid.svg'); ?>`,
          icon_png: `<img src="<?= \EASY_LOCATIONS_PLUGIN_URL . 'src/public/media/icons-v2/ASMG_SQ.png'; ?>" alt="map icon" />`
        },
        "Hot Mix Plant": {
          color: "#800080", // Purple
          icon_svg: `<?= file_get_contents(EASY_LOCATIONS_PLUGIN_DIR . 'src/public/media/icons-v2/hotmix.svg'); ?>`,
          icon_png: `<img src="<?= \EASY_LOCATIONS_PLUGIN_URL . 'src/public/media/icons-v2/ASMG_SQ.png'; ?>" alt="map icon" />`
        },
      };

      // Initialize the Google Map - Making it a global function
      window.initMap = async function() {
        const {
          Map
        } = await google.maps.importLibrary("maps");
        const {
          AdvancedMarkerElement,
          PinElement
        } = await google.maps.importLibrary("marker");

        // Calculate the center of all markers and the bounds
        const bounds = new google.maps.LatLngBounds();
        storeLocations.forEach(location => {
          bounds.extend({
            lat: location.lat,
            lng: location.lng
          });
        });

        var mapOptions = {
          center: bounds.getCenter(), // Center the map based on your locations
          zoom: 8, // Initial zoom level (adjust as needed)
          mapId: 'DEMO_MAP_ID' // **ADDED: Required for Advanced Markers**
        };

        var map = new Map(document.getElementById("map"), mapOptions);

        // Fit the map to the bounds of the markers after initialization
        map.fitBounds(bounds);

        // Create and place markers for each store location
        const markers = storeLocations.map((store) => {
          var pinColor = categoryDecorations[store.category].color || "#808080"; // Default to grey if category not found

          let iconContent = null;
          if (icon_mode === 'svg') {
            iconContent = categoryDecorations[store.category].icon_svg || null;
          }
          if (icon_mode === 'png') {
            iconContent = categoryDecorations[store.category].icon_png || null;
          }


          // Create marker with custom SVG icon if available
          const marker = new AdvancedMarkerElement({
            position: {
              lat: store.lat,
              lng: store.lng
            },
            map: map,
            title: store.name, // Tooltip on hover
            content: iconContent ? createCustomIcon(iconContent, pinColor) : createDefaultPin(pinColor),
          });

          // Create an InfoWindow for each marker
          marker.infoWindow = new google.maps.InfoWindow({
            content: `<h3>${store.name}</h3><p>${store.address}</p>`,
          });

          return marker;
        });

        const closeAllInfoWindows = () => {
          for (const marker of markers) {
            marker.infoWindow.close();
          }
        }

        for (const marker of markers) {
          marker.addListener("gmp-click", () => {
            closeAllInfoWindows();
            marker.infoWindow.open(map, marker);
          });
        }

      }

      // Helper function to create a custom SVG icon
      function createCustomIcon(svgContent, color) {
        const div = document.createElement('div');
        div.innerHTML = svgContent;
        const svg = div.firstChild;
        svg.style.width = '40px';
        svg.style.height = '40px';
        svg.style.fill = color;
        return div;
      }

      // Helper function to create default pin if no SVG is available
      function createDefaultPin(color) {
        const pinElement = new PinElement({
          background: color,
          borderColor: '#000',
          glyphColor: '#FFF'
        });
        return pinElement.element;
      }

      // Function to load the Google Maps API script
      function loadScript() {
        const script = document.createElement('script');
        // Replace YOUR_API_KEY with your actual Google Maps JavaScript API key
        // The 'callback=initMap' tells the API to call the initMap function once it's loaded.
        // The '&libraries=marker' loads the necessary library for Advanced Markers and PinElement.
        script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyAvdJa5-XhoMP0ut39PMirYLBIuXKB_8aA&callback=console.debug&libraries=maps,marker&v=beta`;
        script.async = true;
        document.head.appendChild(script);
      }

      // Call the function to load the script when the window loads
      // Added a small delay to potentially help with Divi's loading process
      window.onload = function() {
        setTimeout(loadScript, 100); // Load script after a small delay
      };
    </script>

<?php
    return ob_get_clean();
  }
}

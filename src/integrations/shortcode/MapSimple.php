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

      // Define colors for each category
      var categoryColors = {
        "Asphalt Emulsion Plant": "#FF0000", // Red
        "Ready Mix Concrete Plant": "#00FF00", // Green
        "Aggregate Quarry": "#0000FF", // Blue
        "Office / Operations Facility": "#FFFF00", // Yellow
        "Liquid Asphalt Terminal": "#FFA500", // Orange
        "Hot Mix Plant": "#800080" // Purple
        // Add more categories and colors if needed
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
        storeLocations.forEach(function(store) {
          var pinColor = categoryColors[store.category] || "#808080"; // Default to grey if category not found

          // Use PinElement for colored markers
          const pinElement = new PinElement({
            background: pinColor,
            borderColor: '#000', // Optional border color
            glyphColor: '#FFF' // Optional color for the default dot/glyph
          });

          const marker = new AdvancedMarkerElement({
            position: {
              lat: store.lat,
              lng: store.lng
            },
            map: map,
            title: store.name, // Tooltip on hover
            content: pinElement.element, // Use PinElement for custom appearance
          });

          // Create an InfoWindow for each marker
          const infoWindow = new google.maps.InfoWindow({
            content: `<h3>${store.name}</h3><p>${store.info}</p>`,
          });

          // Add a click listener to open the InfoWindow - **UPDATED for Advanced Markers**
          marker.addListener("gmp-click", () => {
            infoWindow.open(map, marker);
          });
        });
      }

      // Function to load the Google Maps API script
      function loadScript() {
        const script = document.createElement('script');
        // Replace YOUR_API_KEY with your actual Google Maps JavaScript API key
        // The 'callback=initMap' tells the API to call the initMap function once it's loaded.
        // The '&libraries=marker' loads the necessary library for Advanced Markers and PinElement.
        script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDxrZvp13o4vfImn_Ci4ypFbekQVwXF25s&callback=initMap&libraries=marker`;
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

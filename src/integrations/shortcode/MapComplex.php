<?php

namespace MarketMentors\EasyLocations\src\integrations\shortcode;

use MarketMentors\EasyLocations\src\models\Location;

class MapComplex
{
  public function __construct()
  {
    add_shortcode('easy-locations-map-complex', [$this, 'render']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
  }

  public function enqueue_scripts()
  {
    // Add styles to head.
    add_action('wp_head', function () {
      ob_start();
?>
      <style type="text/css">
        #hero .background-overlay,
        #hero .section-adornments-group,
        #hero .section-content-group {
          pointer-events: none;
        }

        .section-background-group {
          height: 540px;
        }

        .section-background-group .map {
          height: 100%;
        }


        @media (max-width: 959px) {
          #hero .background-overlay.hasAngels .transform-wrapper {
            opacity: 0;
          }

          #hero .section-content-group {
            opacity: 0;
          }
        }

        #hero .background-overlay.hasAngels .transform-wrapper {
          transform: translate3D(-40%, var(--translate-y), 0) rotateZ(0) !important;
        }

        @media (min-width: 1280px) {
          #hero .background-overlay.hasAngels .transform-wrapper {
            opacity: 1;
            transform: translate3D(-30%, var(--translate-y), 0) rotateZ(0) !important;
          }
        }

        @media (min-width: 1366px) {
          #hero .background-overlay.hasAngels .transform-wrapper {
            opacity: 1;
            transform: translate3D(-25%, var(--translate-y), 0) rotateZ(0) !important;
          }
        }

        @media (min-width: 1600px) {
          #hero .background-overlay.hasAngels .transform-wrapper {
            opacity: 1;
            transform: translate3D(-20%, var(--translate-y), 0) rotateZ(0) !important;
          }
        }

        :root {
          --marker-yellow: #c47c29;
          --marker-red: #52121b;
          --marker-blue: #003B71;
          /* #3954a5; */
          --marker-green: #03808b;
          --marker-gray: #97989A;
        }

        /* Set the size of the div element that contains the map */
        #map {
          height: 100%;
          /* The height is 400 pixels */
          width: 100%;
          /* The width is the width of the web page */
        }

        .filter-list {
          display: flex;
          flex-wrap: wrap;
          padding: 0;
        }

        .filter-item .material-symbols-sharp {
          color: red;
          font-size: 42px;
        }

        .filter-item {
          position: relative;
          display: inline-flex;
          align-items: center;
          margin: 1rem;
          background-color: transparent;
          cursor: pointer;
          overflow: hidden;
          font-size: 1.125rem;
          user-select: none;
        }

        /* .filter-item::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          bottom: 0;
          right: 0;
          width: 100%;
          height: 100%;
          background-color: transparent;
          transition: all 0.3s ease-in-out;
          z-index: -1;
        } */

        .filter-item.reset {
          padding: 0 1rem;
        }

        .filter-item.active {
          color: #EF3E42;
        }

        .filter-item[data-type="full_service_itm"].active::before {
          background-color: var(--marker-yellow);
        }

        .filter-item[data-type="full_service_branch"].active::before {
          background-color: var(--marker-red);
        }

        .filter-item[data-type="atm_only"].active::before {
          background-color: var(--marker-blue);
        }

        .filter-item[data-type="coming_soon"].active::before {
          background-color: var(--marker-green);
        }

        .filter-item[data-type="safe_deposit_box"].active::before {
          background-color: var(--marker-gray);
        }

        @media (max-width: 959px) {
          .filter-item {
            width: calc(50% - 2rem);
          }
        }

        @media (max-width: 480px) {
          .filter-item {
            width: calc(100% - 2rem);
          }
        }

        .filter-item .icon {
          position: relative;
          margin-right: 1rem;
          z-index: 10;
        }

        .filter-item,
        .filter-item .icon img {
          transition: all 0.3s ease-in-out;
        }

        .filter-item:not(.active):hover {
          color: #777;
        }

        .filter-item:not(.active):hover .icon img {
          filter: brightness(1.2);
        }

        /* .filter-item .icon::before {
          content: '';
          position: absolute;
          top: 50%;
          left: 0;
          width: 16rem;
          height: 16rem;
          transform: translate(-12rem, -55%);
          border-radius: 50%;
          background-color: #eeeeee;
          z-index: 1;
        } */

        @media (min-width: 1280px) {
          .filter-item .icon::before {
            transform: translate(-12.5rem, -55%);
          }
        }

        @media (min-width: 1600px) {
          .filter-item .icon::before {
            transform: translate(-13rem, -55%);
          }
        }

        .filter-item .icon img {
          position: relative;
          display: block;
          width: 30px;
          z-index: 10;
        }

        .locations-list {
          columns: 2;
          padding: 0;
        }

        .locations-list .location {
          display: inline-grid;
          grid-template-columns: 64px auto;
          width: 100%;
          margin: 1rem;
          cursor: pointer;
          user-select: none;
        }

        .locations-list .location:hover {
          filter: brightness(1.2);
        }

        .locations-list .location .additional-meta-drawer {
          grid-column: span 2;
          max-height: 0;
          overflow: hidden;
          transition: all 0.3s ease-in-out;
        }

        .locations-list .location .additional-meta-drawer.open {
          max-height: 500px;
        }

        .locations-list .location .additional-meta-drawer .meta-drawer-links {
          display: flex;
          justify-content: space-between;
          width: 69%;
        }

        @media (max-width: 959px) {
          .locations-list {
            columns: 1;
          }
        }

        @media (min-width: 1366px) {
          .locations-list {
            columns: 3;
          }
        }

        .locations-list .location .icon {
          position: relative;
          display: block;
          width: 64px;
          height: 64px;
          margin-right: 1rem;
        }

        .locations-list .location .icon img {
          position: absolute;
          --bottom-step: 6px;
          --left-step: 12px;
        }

        .locations-list .location .icon img:nth-child(1) {
          bottom: calc(var(--bottom-step) * 0);
          left: calc(var(--left-step) * 0);
          z-index: 10;
        }

        .locations-list .location .icon img:nth-child(2) {
          bottom: calc(var(--bottom-step) * 1);
          left: calc(var(--left-step) * 1);
          z-index: 9;
        }

        .locations-list .location .icon img:nth-child(3) {
          bottom: calc(var(--bottom-step) * 2);
          left: calc(var(--left-step) * 2);
          z-index: 8;
        }


        .locations-list .location .content {
          flex: 1;
        }

        .locations-list .location .content h3 {
          font-size: 1.125rem;
          font-weight: 600;
          margin-bottom: 0.5rem;
        }

        .locations-list .location .content h3,
        .locations-list .location .content p {
          margin: 0;
        }
      </style>
    <?php
      $styles = ob_get_clean();
      echo $styles;
    });
  }

  public function render($atts)
  {
    $maps_api_key = 'AIzaSyDxrZvp13o4vfImn_Ci4ypFbekQVwXF25s';
    $maps_api_key_dev = 'AIzaSyDxrZvp13o4vfImn_Ci4ypFbekQVwXF25s';

    $atts = shortcode_atts([
      'id' => 'easy_locations_map_complex',
    ], $atts);

    // Get locations from WordPress
    $locations = Location::get_all_locations();

    $location_types = [];
    foreach (Location::get_all_location_types() as $location_type) {
      $location_types[$location_type['term']->slug] = $location_type;
    }


    /**
     * Get the filter from the query string.
     * Allows the setting of the default filter upon navigation to the page.
     * Can only be one of the five locations types which are the following:
     * - full_service_itm
     * - full_service_branch
     * - atm_only
     * - coming_soon
     * - safe_deposit_box
     */
    $valid_values = array_keys($location_types);
    $default_filter = null;
    if (isset($_GET['filter'])) {
      $filter = trim($_GET['filter']); // Remove whitespace from the beginning and end
      $filter = stripslashes($filter); // Remove backslashes
      $filter = htmlspecialchars($filter, ENT_QUOTES, 'UTF-8'); // Convert special characters to HTML entities
      if (in_array($filter, $valid_values)) {
        $default_filter = $filter;
      }
    }


    // Add scripts to footer.
    add_action(
      'wp_footer',
      function () use (
        $maps_api_key,
        $maps_api_key_dev,
        $locations,
        $location_types,
        $default_filter
      ) {
        ob_start();
    ?>
      <script>
        (g => {
          var h, a, k, p = "The Google Maps JavaScript API",
            c = "google",
            l = "importLibrary",
            q = "__ib__",
            m = document,
            b = window;
          b = b[c] || (b[c] = {});
          var d = b.maps || (b.maps = {}),
            r = new Set,
            e = new URLSearchParams,
            u = () => h || (h = new Promise(async (f, n) => {
              await (a = m.createElement("script"));
              e.set("libraries", [...r] + "");
              for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
              e.set("callback", c + ".maps." + q);
              a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
              d[q] = f;
              a.onerror = () => h = n(Error(p + " could not load."));
              a.nonce = m.querySelector("script[nonce]")?.nonce || "";
              m.head.append(a)
            }));
          d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
        })
        ({
          key: window.location.hostname !== "localhost" ?
            "<?= $maps_api_key ?>" : "<?= $maps_api_key_dev ?>",
          v: "weekly"
        });
      </script>


      <script type="text/javascript">
        (async () => {
          // Request needed libraries.
          //@ts-ignore
          const {
            Map,
            InfoWindow,
            Icon,
          } = await google.maps.importLibrary("maps");

          const {
            AdvancedMarkerElement
          } = await google.maps.importLibrary("marker");

          const location_types = <?= json_encode($location_types) ?>;

          console.log(location_types);

          const position_ware = {
            lat: 42.260394990146736,
            lng: -72.23952752826688
          };
          const position_Worcester = {
            lat: 42.2625, // 42.262546549204835, 
            lng: -71.8028 // -71.80178577238325
          };
          const position_default = {
            lat: 42.8432136,
            lng: -72.3555698
          };

          /**
           * This function takes the location_hours object from the location post type
           * and returns a string of the business hours.
           * 
           * @param {object} data 
           * @returns {string}
           */
          function displayBusinessHours(data) {
            const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

            let label = `<strong>${data.label}</strong><br />`;
            let hours = '';

            daysOfWeek.forEach(day => {
              const dayData = data.values[`${day.toLowerCase()}_location_hoursValueFields`];
              if (dayData.value === '') return;

              hours += `<span>${day}: ${dayData.value}</span><br />`;
            });

            if (hours === '') {
              return '';
            }

            return label + hours;
          }

          /**
           * This function adds intentful interaction event handling to an element.
           * 
           * Intentful interaction is when the user intends to interact with an UI element.
           * Intent here is defined as the user clicking or tapping the element sharply.
           * This naturally filters out accidental interactions, hesitant interactions, and incorrect interactions such as dragging.
           * 
           * @param {HTMLElement} element
           * @param {function} callback
           * 
           * @returns {void}
           */
          function withIntentfulInteraction(element, callback) {
            let mouseDownTime;

            function startAction(event) {
              // Only proceed if the left button is pressed
              if (event.button !== 0) {
                return;
              }

              event.preventDefault();
              event.stopPropagation();

              // Record the time when the mouse button is pressed down or screen is touched
              mouseDownTime = new Date();
            }

            function endAction(event) {
              // Only proceed if the left button is released
              if (event.button !== 0) {
                return;
              }

              event.preventDefault();
              event.stopPropagation();

              let mouseUpTime = new Date();
              let timeDiff = mouseUpTime - mouseDownTime;

              // If the time difference is less than a certain threshold (e.g., 200 milliseconds), trigger the click event
              if (timeDiff < 333) {
                callback();
              }

            }

            function cancelAction() {
              // If the mouse leaves the element before the mouseup event or touch is cancelled, reset the mousedown time
              mouseDownTime = null;
            }

            // For some reason, the touch events are not working on mobile devices?
            // Determine if the user is using a mouse or touch screen
            // if (window.innerWidth < 960) {
            //   element.addEventListener('touchstart', startAction);
            //   element.addEventListener('touchend', endAction);
            //   element.addEventListener('touchcancel', cancelAction);
            // } else {
            //   element.addEventListener('mousedown', startAction);
            //   element.addEventListener('mouseup', endAction);
            //   element.addEventListener('mouseleave', cancelAction);
            // }
            // These events seem to cover all devices.
            element.addEventListener('mousedown', startAction);
            element.addEventListener('mouseup', endAction);
            element.addEventListener('mouseleave', cancelAction);
          }


          /**
           * This class represents a filter on the map,
           * and contains other useful information and utility
           * methods.
           * 
           * @class Filter
           * @property {object} type
           * @property {boolean} active
           * @property {HTMLElement} element
           * @property {function} handleToggle
           * @property {LocationsManager} locationsManager
           * @property {boolean} visible
           * 
           * @method {void} checkIfShouldShow
           * @method {void} hide
           * @method {void} show
           * @method {void} activate
           * @method {void} deactivate
           * @method {void} toggle
           * @method {HTMLElement} render
           */
          class Filter {

            /**
             * @param {object} type
             */
            constructor(type) {
              this.type = type;
              this.active = true;
              this.element = null;
              this.handleToggle = null;
              this.render();
              this.locationsManager = null;
              this.visible = true;

              this.toggle.bind(this);
              this.render.bind(this);
              this.activate.bind(this);
              this.deactivate.bind(this);
              this.show.bind(this);
              this.hide.bind(this);
              this.checkIfShouldShow.bind(this);
            }

            /**
             * This method checks if the filter should be shown.
             * 
             * @returns {void}
             */
            checkIfShouldShow() {
              console.log('checkIfShouldShow', this.type.term.slug);
              const slugInstances = this.locationsManager.locations.map(
                location => location.types.map(t => t.slug)
              ).flat();

              const inSlugs = slugInstances.includes(this.type.term.slug);

              if (!inSlugs) {
                this.hide();
              } else {
                this.show();
              }
            }

            /**
             * This method hides the filter.
             * 
             * @returns {void}
             */
            hide() {
              this.visible = false;
            }

            /**
             * This method shows the filter.
             * 
             * @returns {void}
             */
            show() {
              this.visible = true;
            }

            /**
             * This method activates the filter.
             * 
             * @returns {void}
             */
            activate() {
              this.active = true;
              this.element.classList.add('active');
            }

            /**
             * This method deactivates the filter.
             * 
             * @returns {void}
             */
            deactivate() {
              this.active = false;
              this.element.classList.remove('active');
            }

            /**
             * This method toggles the filter.
             * 
             * @returns {void}
             */
            toggle() {
              this.locationsManager.filtersList.forEach(filter => {
                filter.deactivate();
              });

              this.activate();
              this.handleToggle(this.type.term.slug);
            }

            /**
             * This method renders the filter.
             * 
             * @returns {HTMLElement}
             */
            render() {
              const locationType = location_types[this.type.term.slug];
              const iconUrl = locationType && locationType.icon && locationType.icon.url ? locationType.icon.url : '';
              const termName = locationType && locationType.term ? locationType.term.name : this.type.term.slug;
              const template = `
            <span class="icon">
              <img src="${iconUrl}" alt="${termName}" width="30px" />
            </span>${termName}
          `;

              const element = document.createElement('li');
              element.innerHTML = template;
              element.classList.add('filter-item');
              if (this.active) {
                element.classList.add('active');
              }
              element.setAttribute('data-active', this.active);
              element.setAttribute('data-filter', this.type.term.slug);
              element.setAttribute('data-type', this.type.term.slug);

              withIntentfulInteraction(
                element,
                () => {
                  this.toggle();
                }
              );

              element.style.setProperty('display', this.visible ? 'inline-flex' : 'none', 'important');

              this.element = element;

              return element;
            }
          }


          /**
           * This class represents a location on the map,
           * and contains other useful information and utility
           * methods.
           * 
           * @class Location
           * @property {google.maps.Map} map
           * @property {string} title
           * @property {object} meta
           * @property {object[]} types
           * @property {LocationsManager} locationsManager
           * @property {object} position
           * @property {google.maps.Marker} marker
           * @property {google.maps.InfoWindow} infoWindow
           * @property {boolean} infoWindowState
           * @property {HTMLElement} locationListElement
           * 
           * @method {void} openInfoWindow
           * @method {void} closeInfoWindow
           * @method {void} toggleInfoWindow
           * @method {void} setInfoWindowContent
           * @method {void} setInfoWindowPosition
           * @method {void} setInfoWindowMap
           * @method {void} setInfoWindowTitle
           * 
           * @method {void} setMarkerMap
           * @method {void} setMarkerPosition
           * @method {void} setMarkerTitle
           * 
           * @method {void} render
           */
          class Location {
            /**
             * @param {google.maps.Map} map
             * @param {string} title
             * @param {object} meta
             * @param {array<object>} types
             * @param {LocationsManager} locationsManager
             * 
             * @throws {Error} Location must have gps coordinates.
             * 
             */
            constructor(map, title, address, types, lat, lng, locationsManager) {
              this.map = map;
              this.title = title;
              this.address = address || '';
              this.types = types || [];
              this.locationsManager = locationsManager;
              this.locationListElement = null;
              this.marker = null;

              if (lat === '' || lng === '') {
                throw new Error('Location must have gps coordinates.');
              }

              this.position = {
                lat: parseFloat(lat),
                lng: parseFloat(lng)
              };

              this.infoWindow = new InfoWindow({
                content: `
            <div class="info-window">
              <h3>${this.title}</h3>
              <p>${this.address}</p>
              <p><a href="https://www.google.com/maps/search/?api=1&query=${this.position.lat},${this.position.lng}" target="_blank">Directions</a></p>
            </div>
          `,
              });

              google.maps.event.addListener(
                this.infoWindow,
                'closeclick',
                () => {
                  this.closeAdditionalMetaDrawer();
                }
              );

              this.infoWindowState = false;


              // if (!location_types[type.slug]) {
              //   throw new Error('Location type is not valid.');
              // }

              const markerImageContainer = document.createElement('div');
              markerImageContainer.setAttribute(
                'style',
                `
            position: relative;
            display: block;
            width: 32px;
            height: 32px;
            --bottom-step: 4px;
            --left-step: 8px;
            `
              );

              for (const type of this.types) {
                if (!type || !type.slug) {
                  console.error({
                    Message: 'Location type is not valid.',
                    type: type,
                    location: this
                  });
                  continue;
                }

                const markerImg = document.createElement("img");

                const locationType = location_types[type.slug];
                const iconUrl = locationType && locationType.icon && locationType.icon.url ? locationType.icon.url : '';
                markerImg.src = iconUrl;
                markerImg.alt = locationType && locationType.term ? locationType.term.name : type.slug;
                markerImg.setAttribute(
                  'style',
                  `
              position: absolute;
              bottom: calc(var(--bottom-step) * ${this.types.indexOf(type)});
              left: calc(var(--left-step) * ${this.types.indexOf(type)});
              width: 24px;
              z-index: ${this.types.length - this.types.indexOf(type)};
              `
                );

                markerImageContainer.appendChild(markerImg);
              }

              const marker = new AdvancedMarkerElement({
                map: map,
                position: this.position,
                title: title,
                content: markerImageContainer
              });

              marker.addListener('click', this.clickHandler.bind(this));

              this.marker = marker;

              this.locationsManager.addLocations([this]);

              this.openInfoWindow.bind(this);
              this.closeInfoWindow.bind(this);
              this.toggleInfoWindow.bind(this);
              this.setInfoWindowContent.bind(this);
              this.setInfoWindowPosition.bind(this);
              this.setInfoWindowMap.bind(this);
              this.setInfoWindowTitle.bind(this);

            }

            /**
             * Handles the click event on the marker.
             * 
             * @returns {void}
             */
            clickHandler() {
              if (this.infoWindowState) {
                this.infoWindow.close();
                this.closeAdditionalMetaDrawer();
                this.infoWindowState = false;
              } else {
                this.locationsManager.closeAllInfoWindows();
                this.infoWindowState = true;
                this.infoWindow.open(
                  this.map,
                  this.marker
                );
                this.openAdditionalMetaDrawer();
              }
            }

            /**
             * Opens the info window.
             * 
             * @returns {void}
             */
            openInfoWindow() {
              //close all other info windows.
              this.locationsManager.closeAllInfoWindows();
              this.infoWindow.open(
                this.map,
                this.marker
              );
              this.infoWindowState = true;
            }

            closeInfoWindow() {
              this.infoWindow.close();
              this.infoWindowState = false;
            }

            /**
             * Toggles the info window.
             * 
             * @returns {void}
             */
            toggleInfoWindow() {
              if (this.infoWindowState) {
                this.closeInfoWindow();
              } else {
                this.openInfoWindow();
              }
            }

            /**
             * Sets the content of the info window.
             * 
             * @param {string} content
             * 
             * @returns {void}
             */
            setInfoWindowContent(content) {
              this.infoWindow.setContent(content);
            }

            /**
             * Sets the position of the info window.
             * 
             * @param {object} position
             * 
             * @returns {void}
             */
            setInfoWindowPosition(position) {
              this.infoWindow.setPosition(position);
            }

            /**
             * Sets the map of the info window.
             * 
             * @param {google.maps.Map} map
             * 
             * @returns {void}
             */
            setInfoWindowMap(map) {
              this.infoWindow.setMap(map);
            }

            /**
             * Sets the title of the info window.
             * 
             * @param {string} title
             * 
             * @returns {void}
             */
            setInfoWindowTitle(title) {
              this.infoWindow.setTitle(title);
            }

            /**
             * Sets the map of the marker.
             * 
             * @param {google.maps.Map} map
             * 
             * @returns {void}
             */
            setMarkerMap(map) {
              this.marker.setMap(map);
            }

            /**
             * Sets the position of the marker.
             * 
             * @param {object} position
             * 
             * @returns {void}
             */
            setMarkerPosition(position) {
              this.marker.setPosition(position);
            }

            /**
             * Sets the title of the marker.
             * 
             * @param {string} title
             * 
             * @returns {void}
             */
            setMarkerTitle(title) {
              this.marker.setTitle(title);
            }

            /**
             * Toggles the active state of the location.
             * 
             * @returns {void}
             */
            toggle() {
              this.active = !this.active;
            }

            /**
             * Opens the additional meta drawer.
             * This has the side effect of closing all other additional meta drawers.
             * 
             * @returns {void}
             */
            openAdditionalMetaDrawer() {
              this.locationsManager.closeAllLocationsDrawers();
              if (!this.locationListElement) {
                return; // Element not rendered yet
              }
              const drawer = this.locationListElement.querySelector('.additional-meta-drawer');
              if (drawer && !drawer.classList.contains('open')) {
                drawer.classList.add('open');
                this.active = true;
              }
            }

            /**
             * Closes the additional meta drawer.
             * 
             * @returns {void}
             */
            closeAdditionalMetaDrawer() {
              if (!this.locationListElement) {
                return; // Element not rendered yet
              }
              const drawer = this.locationListElement.querySelector('.additional-meta-drawer');
              if (drawer && drawer.classList.contains('open')) {
                drawer.classList.remove('open');
                this.active = false;
                this.closeInfoWindow();
              }
            }

            /**
             * Toggles the additional meta drawer.
             * This has the side effect of closing all other additional meta drawers, if the toggle results in the drawer being opened.
             * 
             * @returns {void}
             */
            toggleAdditionalMetaDrawer() {
              if (this.active) {
                this.closeAdditionalMetaDrawer();
              } else {
                this.openAdditionalMetaDrawer();
              }
            }

            /**
             * Renders the location list element.
             * 
             * @returns {HTMLElement}
             */
            render() {
              const template = `
            <div class="icon">
              ${this.types.map(type => {
                const locationType = location_types[type.slug];
                const iconUrl = locationType && locationType.icon && locationType.icon.url ? locationType.icon.url : '';
                const termName = locationType && locationType.term ? locationType.term.name : type.slug;
                return `<img src="${iconUrl}" alt="${termName}" width="30px" />`;
              }).join('')}
            </div>
            <div class="content">
              <h3>${this.title}</h3>
              <p>${this.address ? this.address.split(',')[0] : ''}</p>
            </div>
            <div class="additional-meta-drawer">
              <div class="meta-drawer-links">
                <div class="directions-link"><a href="https://www.google.com/maps/search/?api=1&query=${this.position.lat},${this.position.lng}" target="_blank">Directions</a></div>
                <div class="show-on-map-container"></div>
              </div>
            </div>
          `;


              const element = document.createElement('li');
              this.locationListElement = element;
              element.innerHTML = template;
              element.classList.add('location');
              this.active = false;
              element.setAttribute('data-location-id', this.ID);

              const showOnMapContainer = element.querySelector('.show-on-map-container');
              const showOnMapElement = document.createElement('a');
              showOnMapElement.classList.add('show-on-map');
              showOnMapElement.innerHTML = 'Show on map';
              withIntentfulInteraction(
                showOnMapElement,
                () => {
                  // Center the map in the viewport
                  const mapElement = document.getElementById('map');
                  const mapRect = mapElement.getBoundingClientRect();
                  const windowHeight = window.innerHeight;
                  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                  // Calculate the position to center the map vertically in the viewport
                  const targetScrollTop = scrollTop + mapRect.top - (windowHeight / 2) + (mapRect.height / 2);

                  window.scrollTo({
                    top: targetScrollTop,
                    behavior: 'smooth'
                  });
                }
              );
              showOnMapContainer.appendChild(showOnMapElement);

              withIntentfulInteraction(
                element,
                () => {
                  this.openInfoWindow();
                  this.toggleAdditionalMetaDrawer();
                }
              );

              return element;
            }

          }


          /**
           * This class manages all the locations on the map.
           * 
           * @class LocationsManager
           * @property {Location[]} locations
           * @property {Filter[]} filtersList
           * @property {HTMLElement} filterListElement
           * @property {HTMLElement} locationsListElement
           * @property {google.maps.Map} mapInstance
           * 
           * @method {void} addLocations
           * @method {void} closeAllInfoWindows
           * @method {void} closeAllLocationsDrawers
           * @method {void} renderFiltersList
           * @method {void} renderLocationsList
           * @method {void} updateMapLocations
           * @method {void} addLocations
           */
          class LocationsManager {

            constructor(locations, filtersList, filterListElement, locationsListElement, mapInstance) {
              this.locations = locations;
              this.filtersList = filtersList.map(filter => {
                filter.locationsManager = this;
                filter.handleToggle = this.updateMapLocations.bind(this);
                return filter;
              })
              this.filterListElement = filterListElement;
              this.locationsListElement = locationsListElement;
              this.mapInstance = mapInstance;
            }

            /**
             * This method adds locations to the locations array.
             * 
             * @param {Location[]} locations
             * 
             * @returns {void}
             */
            addLocations(locations) {
              this.locations = this.locations.concat(locations);
            }

            /**
             * This method updates the map to show the locations that have the type slug.
             * 
             * @param {string} slug
             * 
             * @returns {void}
             */
            updateMapLocations(slug) {
              for (const location of this.locations) {
                location.closeInfoWindow();
                location.closeAdditionalMetaDrawer();

                if (slug === null) {
                  location.marker.setMap(location.map);
                  continue;
                }

                const filter = this.filtersList.find(filter => filter.type.term.slug === slug);

                const locationTypesHaveFilterSlug = location.types.some(type => {
                  return type.slug === slug;
                });

                if (filter.active && locationTypesHaveFilterSlug) {
                  location.marker.setMap(location.map);
                } else {
                  location.marker.setMap(null);
                }
              }

              this.renderLocationsList();
            }

            /**
             * This method closes all the info windows on the map.
             * 
             * @returns {void}
             */
            closeAllInfoWindows() {
              this.locations.forEach(location => {
                location.infoWindow.close();
                location.infoWindowState = false;
              });
            }

            /**
             * This method closes all the additional meta drawers on the locations list.
             * 
             * @returns {void}
             */
            closeAllLocationsDrawers() {
              this.locations.forEach(location => {
                location.closeAdditionalMetaDrawer();
              });
            }

            /**
             * This method renders the filters list.
             * 
             * @returns {void}
             */
            renderFiltersList() {
              console.log('renderFiltersList');
              this.filterListElement.innerHTML = '';

              this.filtersList.forEach(filter => {
                this.filterListElement.appendChild(filter.render());
              });

              const resetTemplate = `
            Reset
          `;

              const resetElement = document.createElement('li');
              resetElement.innerHTML = resetTemplate;
              resetElement.classList.add('filter-item');
              resetElement.classList.add('reset');
              withIntentfulInteraction(
                resetElement,
                () => {
                  this.filtersList.forEach(filter => {
                    filter.activate();
                  });
                  this.updateMapLocations(null);

                  // Center the map in the viewport
                  const mapElement = document.getElementById('map');
                  const mapRect = mapElement.getBoundingClientRect();
                  const windowHeight = window.innerHeight;
                  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                  // Calculate the position to center the map vertically in the viewport
                  const targetScrollTop = scrollTop + mapRect.top - (windowHeight / 2) + (mapRect.height / 2);

                  window.scrollTo({
                    top: targetScrollTop,
                    behavior: 'smooth'
                  });

                  map.setCenter(position_default);
                  console.log('setCenter', position_default);
                  map.setZoom(8);
                  console.log('setZoom', 8);
                }
              );

              this.filterListElement.appendChild(resetElement);
            }

            /**
             * This method renders the locations list.
             * 
             * @returns {void}
             */
            renderLocationsList() {
              console.log('renderLocationsList');
              this.locationsListElement.innerHTML = '';

              const activeFilters = this.filtersList.filter(filter => filter.active);

              this.locations.forEach(location => {
                // Check if the location has a type slug that is the same as the active filter slug.
                const hasActiveFilter = location.types.some(type => {
                  return activeFilters.some(filter => filter.type.term.slug === type.slug);
                });

                if (hasActiveFilter) {
                  this.locationsListElement.appendChild(location.render());
                }
              });

            }

          }


          async function initMap() {
            console.log('initMap');
            const isSmallScreen = window.innerWidth < 960;
            const isPhone = window.innerWidth < 640;

            const map = await new Map(document.getElementById("map"), {
              zoom: isPhone ? 9 : 7,
              center: position_default,
              mapId: "easy_locations_map_complex",
              disableDefaultUI: true,
              zoomControl: true,
              scaleControl: true,
              streetViewControl: false,
              rotateControl: true,
              fullscreenControl: false,
              mapTypeControl: false,
              mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                mapTypeIds: ["roadmap", "satellite", "hybrid", "terrain"],
              },
              keyboardShortcuts: false,
            });
            console.log('initMap done', map);
            return map;
          }

          // Initialize and add the map
          let map = await initMap();

          console.log('map', map);

          // Add the markers from the locations post types.
          const locations = <?= json_encode($locations) ?>;
          console.log(locations);
          // Sort the locations by post_title alphabetically acending.
          locations.sort((a, b) => {
            if (a.post_title < b.post_title) {
              return -1;
            }
            if (a.post_title > b.post_title) {
              return 1;
            }
            return 0;
          });

          const filterListElement = document.querySelector('.filter-list');
          const locationsListElement = document.querySelector('.locations-list');



          const locationsManager = new LocationsManager(
            [],
            Object.values(location_types).map(type => new Filter(type)),
            filterListElement,
            locationsListElement,
            map
          );

          console.log('locationsManager', locationsManager);

          const location_markers = locations.map(location => {

            const locations = [];

            if (!location.location_type) {
              // If the tag is not a type, then skip this location because it is not a valid location.
              return;
            }

            for (const type of location.location_type) {
              if (location_types[type.slug]) {

                // Check if the tags contain the type.
                const location_type = location.location_type.find(type => {
                  return location_types[type.slug];
                });

                if (!location_type) {
                  // If the tag is not a type, then skip this location because it is not a valid location.
                  return;
                }
              }
            }

            locations.push(new Location(
              map,
              location.name,
              location.address,
              location.location_type,
              location.lat,
              location.lng,
              locationsManager
            ));

            return locations;

          }).flat().filter(Boolean);

          // Add all locations to the locations manager
          //locationsManager.addLocations(location_markers);

          locationsManager.filtersList.forEach(filter => {
            filter.checkIfShouldShow();
          });

          console.log('location_markers', location_markers);

          locationsManager.renderFiltersList();
          locationsManager.renderLocationsList();

          const default_filter = '<?= $default_filter ?>';
          if (default_filter !== '') {
            const defaultFilter = locationsManager.filtersList.find(filter => filter.type.term.slug === default_filter);
            if (defaultFilter) {
              defaultFilter.toggle();
            }
          } else {
            // This will show all the locations on the map since we are not filtering on first load with a default filter slug.
          }

        })();
      </script>
    <?php
        $scripts = ob_get_clean();
        echo $scripts;
      }
    );



    ob_start();
    ?>
    <div class="easy-locations-map-complex">
      <section id="hero" class="page-section half-window-height no-padding flex justify-center align-center" style="margin-top:0px;
          padding-top:0px;
          padding-bottom:0px;
          margin-bottom:0px;">
        <div class="section-background-group">
          <div class="background-underlay "></div>
          <div class="background-image " data-fixed="true">

          </div>
          <div class="map" id="map"></div>
        </div>
      </section>

      <div class="map-filter">
        <figure class="map-filter">
          <ul class="filter-list">
            <?php
            foreach ($location_types as $key => $type) {
            ?>
              <li class="filter-item active" data-filter="{$key}" data-active="true">
                <span class="icon">
                  <img src="<?= $type['icon'] && $type['icon']['url'] ? $type['icon']['url'] : ''; ?>" alt="<?= $type['term']->name; ?>" width="30px" />
                </span><?= $type['term']->name; ?>
              </li>
            <?php
            }
            ?>
          </ul>
        </figure>
        <hr />
        <ul class="locations-list">
          <!-- Locations dynamically loaded. -->
        </ul>
      </div>

    </div>
<?php
    return ob_get_clean();
  }
}

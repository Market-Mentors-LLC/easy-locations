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
          --marker-green: #03808b;
          --marker-gray: #97989A;
        }

        #map { height: 100%; width: 100%; }

        .filter-list { display: flex; flex-wrap: wrap; padding: 0; }
        .filter-item { position: relative; display: inline-flex; align-items: center; margin: 1rem; background-color: transparent; cursor: pointer; overflow: hidden; font-size: 1.125rem; user-select: none; }
        .filter-item.reset { padding: 0 1rem; }
        .filter-item.active { color: #EF3E42; }
        .filter-item .icon { position: relative; margin-right: 1rem; z-index: 10; }
        .filter-item, .filter-item .icon img { transition: all 0.3s ease-in-out; }
        .filter-item:not(.active):hover { color: #777; }
        .filter-item .icon img { position: relative; display: block; width: 30px; z-index: 10; }

        /* Locations list (grid) */
        .locations-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1rem; padding: 0; }
        @media (max-width: 959px) { .locations-list { grid-template-columns: 1fr; } }
        @media (min-width: 960px) and (max-width: 1365px) { .locations-list { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1366px) { .locations-list { grid-template-columns: repeat(3, 1fr); } }

        .locations-list .location { display: inline-grid; grid-template-columns: 57px auto; width: 100%; margin: .33rem; cursor: pointer; user-select: none; }
        .locations-list .location .icon { position: relative; display: block; width: 64px; height: 64px; margin-right: 1rem; }
        .locations-list .location .icon img { position: absolute; --bottom-step: 6px; --left-step: 12px; }
        .locations-list .location .icon img:nth-child(1) { z-index: 10; }
        .locations-list .location .content { flex: 1; }
        .locations-list .location .content h3 { font-size: 1.125rem; font-weight: 600; margin-bottom: 0rem; }
        .locations-list .location .content h3, .locations-list .location .content p { margin: 0; }

        .locations-list .location .additional-meta-drawer { grid-column: span 2; max-height: 0; overflow: hidden; transition: all 0.3s ease-in-out; }
        .locations-list .location .additional-meta-drawer.open { max-height: 500px; }
        .locations-list .location .additional-meta-drawer .meta-drawer-links { display: flex; justify-content: flex-start; align-items: center; gap: .75rem; flex-wrap: wrap; width: auto; }

        /* keep link color stable */
        .locations-list .location .meta-drawer-links a:hover,
        .locations-list .location .meta-drawer-links a:focus,
        .locations-list .location .meta-drawer-links a:active { color: #000 !important; opacity: 1 !important; text-decoration: none; transform: none !important; }
      </style>
<?php
      $styles = ob_get_clean();
      echo $styles;
    });
  }

  public function render($atts)
  {
    // NOTE: replace with your actual keys as needed
    $maps_api_key = 'AIzaSyDxrZvp13o4vfImn_Ci4ypFbekQVwXF25s';
    $maps_api_key_dev = 'AIzaSyDxrZvp13o4vfImn_Ci4ypFbekQVwXF25s';

    $atts = shortcode_atts([
      'id' => 'easy_locations_map_complex',
    ], $atts);

    // Get locations & types
    $locations = Location::get_all_locations();
    $location_types = [];
    foreach (Location::get_all_location_types() as $location_type) {
      $location_types[$location_type['term']->slug] = $location_type;
    }

    // Default filter from querystring (?filter=slug)
    $valid_values = array_keys($location_types);
    $default_filter = null;
    if (isset($_GET['filter'])) {
      $filter = trim($_GET['filter']);
      $filter = stripslashes($filter);
      $filter = htmlspecialchars($filter, ENT_QUOTES, 'UTF-8');
      if (in_array($filter, $valid_values)) {
        $default_filter = $filter;
      }
    }

    // Add scripts to footer.
    add_action('wp_footer', function () use ($maps_api_key, $maps_api_key_dev, $locations, $location_types, $default_filter) {
      ob_start();
?>
      <script>
        (g => {
          var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window;
          b = b[c] || (b[c] = {});
          var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => {
            await (a = m.createElement("script"));
            e.set("libraries", [...r] + "");
            for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
            e.set("callback", c + ".maps." + q);
            a.src = "https://maps." + c + "apis.com/maps/api/js?" + e;
            d[q] = f;
            a.onerror = () => h = n(Error(p + " could not load."));
            a.nonce = m.querySelector("script[nonce]")?.nonce || "";
            m.head.append(a)
          }));
          d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
        })({
          key: window.location.hostname !== "localhost" ? "<?= $maps_api_key ?>" : "<?= $maps_api_key_dev ?>",
          v: "weekly"
        });
      </script>

      <script type="text/javascript">
        (async () => {
          // Request needed libraries.
          const { Map, InfoWindow } = await google.maps.importLibrary("maps");
          const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

          const location_types = <?= json_encode($location_types) ?>;

          const position_default = { lat: 42.8432136, lng: -72.3555698 };

          function withIntentfulInteraction(element, callback) {
            let mouseDownTime;
            function startAction(event) {
              if (event.button !== 0) return;
              event.preventDefault(); event.stopPropagation();
              mouseDownTime = new Date();
            }
            function endAction(event) {
              if (event.button !== 0) return;
              event.preventDefault(); event.stopPropagation();
              const timeDiff = new Date() - mouseDownTime;
              if (timeDiff < 333) callback();
            }
            function cancelAction() { mouseDownTime = null; }
            element.addEventListener('mousedown', startAction);
            element.addEventListener('mouseup', endAction);
            element.addEventListener('mouseleave', cancelAction);
          }

          class Filter {
            constructor(type) {
              this.type = type;
              this.active = true;
              this.element = null;
              this.handleToggle = null;
              this.locationsManager = null;
              this.visible = true;
              this.render = this.render.bind(this);
              this.toggle = this.toggle.bind(this);
            }
            checkIfShouldShow() {
              const slugInstances = this.locationsManager.locations.map(l => l.types.map(t => t.slug)).flat();
              const inSlugs = slugInstances.includes(this.type.term.slug);
              if (!inSlugs) this.hide(); else this.show();
            }
            hide() { this.visible = false; }
            show() { this.visible = true; }
            activate() { this.active = true; this.element.classList.add('active'); }
            deactivate() { this.active = false; this.element.classList.remove('active'); }
            toggle() {
              this.locationsManager.filtersList.forEach(filter => filter.deactivate());
              this.activate();
              this.handleToggle(this.type.term.slug);
            }
            render() {
              const lt = location_types[this.type.term.slug];
              const iconUrl = lt?.icon?.url || '';
              const termName = lt?.term ? lt.term.name : this.type.term.slug;
              const element = document.createElement('li');
              element.innerHTML = `<span class="icon"><img src="${iconUrl}" alt="${termName}" width="30px" /></span>${termName}`;
              element.classList.add('filter-item');
              if (this.active) element.classList.add('active');
              element.setAttribute('data-active', this.active);
              element.setAttribute('data-filter', this.type.term.slug);
              element.setAttribute('data-type', this.type.term.slug);
              withIntentfulInteraction(element, () => { this.toggle(); });
              element.style.setProperty('display', this.visible ? 'inline-flex' : 'none', 'important');
              this.element = element;
              return element;
            }
          }

          class Location {
            /**
             * @param {google.maps.Map} map
             * @param {string} title
             * @param {string} address
             * @param {Array<object>} types
             * @param {number|string} lat
             * @param {number|string} lng
             * @param {LocationsManager} locationsManager
             * @param {string} [phone]
             */
            constructor(map, title, address, types, lat, lng, locationsManager, phone = '') {
              this.map = map;
              this.title = title;
              this.address = address || '';
              this.types = types || [];
              this.locationsManager = locationsManager;
              this.locationListElement = null;
              this.marker = null;

              // phone helpers
              this.phone = (phone || '').toString().trim();
              this.getTelHref = () => {
                if (!this.phone) return '';
                const raw = this.phone.replace(/[^+\d]/g, '');
                return raw ? `tel:${raw}` : '';
              };
              this.getDisplayPhone = () => this.phone;

              if (lat === '' || lng === '') throw new Error('Location must have gps coordinates.');
              this.position = { lat: parseFloat(lat), lng: parseFloat(lng) };

              this.infoWindow = new InfoWindow({
                content: `
                  <div class="info-window">
                    <h3>${this.title}</h3>
                    <p>${this.address}</p>
                    <p><a href="https://www.google.com/maps/search/?api=1&query=${this.position.lat},${this.position.lng}" target="_blank">Directions</a></p>
                    ${this.getTelHref() ? `<p><a href="${this.getTelHref()}">${this.getDisplayPhone()}</a></p>` : ``}
                  </div>
                `,
              });
              google.maps.event.addListener(this.infoWindow, 'closeclick', () => { this.closeAdditionalMetaDrawer(); });
              this.infoWindowState = false;

              // ----- NEW: build marker content (stacked or single) -----
              this.buildMarkerContent = (filterSlug = null) => {
                const container = document.createElement('div');
                container.setAttribute('style', 'position:relative; display:block; width:32px; height:32px; --bottom-step:4px; --left-step:8px;');

                const typesToRender = (filterSlug === null)
                  ? this.types
                  : this.types.filter(t => t && t.slug === filterSlug);

                typesToRender.forEach((t, i) => {
                  if (!t || !t.slug) return;
                  const lt = location_types[t.slug];
                  const iconUrl = lt?.icon?.url || '';
                  const img = document.createElement('img');
                  img.src = iconUrl;
                  img.alt = lt?.term ? lt.term.name : t.slug;
                  img.dataset.slug = t.slug;

                  const bottom = (filterSlug === null) ? `calc(var(--bottom-step) * ${i})` : '0';
                  const left = (filterSlug === null) ? `calc(var(--left-step) * ${i})` : '0';
                  const z = (filterSlug === null) ? (typesToRender.length - i) : 10;

                  img.setAttribute('style', `position:absolute; bottom:${bottom}; left:${left}; width:24px; z-index:${z};`);
                  container.appendChild(img);
                });

                return container;
              };

              const marker = new AdvancedMarkerElement({
                map: map,
                position: this.position,
                title: title,
                content: this.buildMarkerContent(null) // start stacked
              });
              marker.addListener('click', this.clickHandler.bind(this));
              this.marker = marker;

              // API used by the manager when filters change
              this.updateMarkerIcons = (filterSlug) => {
                this.marker.content = this.buildMarkerContent(filterSlug);
              };

              this.locationsManager.addLocations([this]);

              // binders
              this.openInfoWindow = this.openInfoWindow.bind(this);
              this.closeInfoWindow = this.closeInfoWindow.bind(this);
              this.toggleInfoWindow = this.toggleInfoWindow.bind(this);
              this.setInfoWindowContent = this.setInfoWindowContent.bind(this);
              this.setInfoWindowPosition = this.setInfoWindowPosition.bind(this);
              this.setInfoWindowMap = this.setInfoWindowMap.bind(this);
              this.setInfoWindowTitle = this.setInfoWindowTitle.bind(this);
            }

            clickHandler() {
              if (this.infoWindowState) {
                this.infoWindow.close();
                this.closeAdditionalMetaDrawer();
                this.infoWindowState = false;
              } else {
                this.locationsManager.closeAllInfoWindows();
                this.infoWindowState = true;
                this.infoWindow.open(this.map, this.marker);
                this.openAdditionalMetaDrawer();
              }
            }

            openInfoWindow() {
              this.locationsManager.closeAllInfoWindows();
              this.infoWindow.open(this.map, this.marker);
              this.infoWindowState = true;
            }
            closeInfoWindow() { this.infoWindow.close(); this.infoWindowState = false; }
            toggleInfoWindow() { this.infoWindowState ? this.closeInfoWindow() : this.openInfoWindow(); }
            setInfoWindowContent(c) { this.infoWindow.setContent(c); }
            setInfoWindowPosition(p) { this.infoWindow.setPosition(p); }
            setInfoWindowMap(m) { this.infoWindow.setMap(m); }
            setInfoWindowTitle(t) { this.infoWindow.setTitle(t); }
            setMarkerMap(m) { this.marker.setMap(m); }
            setMarkerPosition(p) { this.marker.setPosition(p); }
            setMarkerTitle(t) { this.marker.setTitle(t); }

            openAdditionalMetaDrawer() {
              this.locationsManager.closeAllLocationsDrawers();
              if (!this.locationListElement) return;
              const drawer = this.locationListElement.querySelector('.additional-meta-drawer');
              if (drawer && !drawer.classList.contains('open')) {
                drawer.classList.add('open');
                this.active = true;
              }
            }
            closeAdditionalMetaDrawer() {
              if (!this.locationListElement) return;
              const drawer = this.locationListElement.querySelector('.additional-meta-drawer');
              if (drawer && drawer.classList.contains('open')) {
                drawer.classList.remove('open');
                this.active = false;
                this.closeInfoWindow();
              }
            }
            toggleAdditionalMetaDrawer() { this.active ? this.closeAdditionalMetaDrawer() : this.openAdditionalMetaDrawer(); }

            render() {
              const callLink = this.getTelHref() ? `<div class="call-link"><a href="${this.getTelHref()}">${this.getDisplayPhone()}</a></div>` : ``;

              // NEW: mirror stacked/single behavior in the list
              const activeSlug = (this.locationsManager && this.locationsManager.activeFilterSlug) ? this.locationsManager.activeFilterSlug : null;
              const listTypes = (activeSlug === null) ? this.types : this.types.filter(t => t.slug === activeSlug);

              const iconsHtml = listTypes.map((type, i) => {
                const lt = location_types[type.slug];
                const iconUrl = lt?.icon?.url || '';
                const termName = lt?.term ? lt.term.name : type.slug;
                const style = (activeSlug === null)
                  ? `position:absolute; bottom:calc(var(--bottom-step) * ${i}); left:calc(var(--left-step) * ${i});`
                  : `position:absolute; bottom:0; left:0;`;
                return `<img src="${iconUrl}" alt="${termName}" width="30px" style="${style}" />`;
              }).join('');

              const template = `
                <div class="icon">${iconsHtml}</div>
                <div class="content">
                  <h3>${this.title}</h3>
                  <p>${this.address ? this.address.split(',')[0] : ''}</p>
                </div>
                <div class="additional-meta-drawer">
                  <div class="meta-drawer-links">
                    <div class="directions-link">
                      <a href="https://www.google.com/maps/search/?api=1&query=${this.position.lat},${this.position.lng}" target="_blank">Directions</a>
                    </div>
                    <div class="show-on-map-container"></div>
                    ${callLink}
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
              withIntentfulInteraction(showOnMapElement, () => {
                const mapElement = document.getElementById('map');
                const mapRect = mapElement.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const targetScrollTop = scrollTop + mapRect.top - (windowHeight / 2) + (mapRect.height / 2);
                window.scrollTo({ top: targetScrollTop, behavior: 'smooth' });
              });
              showOnMapContainer.appendChild(showOnMapElement);

              withIntentfulInteraction(element, () => {
                this.openInfoWindow();
                this.toggleAdditionalMetaDrawer();
              });
              return element;
            }
          }

          class LocationsManager {
            constructor(locations, filtersList, filterListElement, locationsListElement, mapInstance) {
              this.locations = locations;
              this.filtersList = filtersList.map(filter => {
                filter.locationsManager = this;
                filter.handleToggle = this.updateMapLocations.bind(this);
                return filter;
              });
              this.filterListElement = filterListElement;
              this.locationsListElement = locationsListElement;
              this.mapInstance = mapInstance;
              this.bounds = new google.maps.LatLngBounds();

              // NEW: track the currently-applied filter (null = show all)
              this.activeFilterSlug = null;
            }

            addLocations(locs) {
              this.locations = this.locations.concat(locs);
              locs.forEach(location => {
                if (location && location.position && typeof location.position.lat === 'number' && typeof location.position.lng === 'number' && !isNaN(location.position.lat) && !isNaN(location.position.lng)) {
                  this.bounds.extend(location.position);
                }
              });
              if (!this.bounds.isEmpty()) this.mapInstance.fitBounds(this.bounds);
            }

            updateMapLocations(slug) {
              // NEW: remember state for both markers & list rendering
              this.activeFilterSlug = slug;

              for (const location of this.locations) {
                location.closeInfoWindow();
                location.closeAdditionalMetaDrawer();

                // NEW: swap marker icons (stacked vs single)
                location.updateMarkerIcons(slug);

                if (slug === null) {
                  location.marker.setMap(location.map);
                  continue;
                }

                const filter = this.filtersList.find(f => f.type.term.slug === slug);
                const hasSlug = location.types.some(t => t.slug === slug);

                if (filter.active && hasSlug) {
                  location.marker.setMap(location.map);
                } else {
                  location.marker.setMap(null);
                }
              }

              this.renderLocationsList();
            }

            closeAllInfoWindows() {
              this.locations.forEach(location => {
                location.infoWindow.close();
                location.infoWindowState = false;
              });
            }

            closeAllLocationsDrawers() {
              this.locations.forEach(location => location.closeAdditionalMetaDrawer());
            }

            renderFiltersList() {
              this.filterListElement.innerHTML = '';
              this.filtersList.forEach(filter => {
                this.filterListElement.appendChild(filter.render());
              });

              const resetElement = document.createElement('li');
              resetElement.innerHTML = `Reset`;
              resetElement.classList.add('filter-item', 'reset');

              withIntentfulInteraction(resetElement, () => {
                this.filtersList.forEach(filter => filter.activate());
                this.updateMapLocations(null); // clears activeFilterSlug

                const mapElement = document.getElementById('map');
                const mapRect = mapElement.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const targetScrollTop = scrollTop + mapRect.top - (windowHeight / 2) + (mapRect.height / 2);
                window.scrollTo({ top: targetScrollTop, behavior: 'smooth' });

                if (!this.bounds.isEmpty()) {
                  this.mapInstance.fitBounds(this.bounds);
                } else {
                  this.mapInstance.setCenter({ lat: 42.8432136, lng: -72.3555698 });
                  this.mapInstance.setZoom(8);
                }
              });

              this.filterListElement.appendChild(resetElement);
            }

            renderLocationsList() {
              this.locationsListElement.innerHTML = '';
              const activeFilters = this.filtersList.filter(f => f.active);
              this.locations.forEach(location => {
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
              keyboardShortcuts: false,
            });
            return map;
          }

          let map = await initMap();

          const locations = <?= json_encode($locations) ?>;

          // sort for list presentation (optional)
          locations.sort((a, b) => {
            const aType = (a.location_type && a.location_type[0] && a.location_type[0].name) ? a.location_type[0].name.toLowerCase() : '';
            const bType = (b.location_type && b.location_type[0] && b.location_type[0].name) ? b.location_type[0].name.toLowerCase() : '';
            if (aType < bType) return -1;
            if (aType > bType) return 1;
            return a.name.localeCompare(b.name);
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

          const location_markers = locations.map(loc => {
            const hasTypes = Array.isArray(loc.location_type) && loc.location_type.length;
            if (!hasTypes) return null;

            // ensure provided types exist in master list
            for (const t of loc.location_type) {
              if (!location_types[t.slug]) return null;
            }

            return new Location(
              map,
              loc.name,
              loc.address,
              loc.location_type,
              loc.lat,
              loc.lng,
              locationsManager,
              loc.phone || ''
            );
          }).filter(Boolean);

          // show only relevant filters
          locationsManager.filtersList.forEach(f => f.checkIfShouldShow());
          locationsManager.renderFiltersList();
          locationsManager.renderLocationsList();

          // default filter from querystring
          const default_filter = '<?= $default_filter ?>';
          if (default_filter !== '') {
            const df = locationsManager.filtersList.find(f => f.type.term.slug === default_filter);
            if (df) df.toggle();
          }
        })();
      </script>
<?php
      $scripts = ob_get_clean();
      echo $scripts;
    });

    ob_start();
?>
    <div class="easy-locations-map-complex">
      <section id="hero" class="page-section half-window-height no-padding flex justify-center align-center" style="margin-top:0px; padding-top:0px; padding-bottom:0px; margin-bottom:0px;">
        <div class="section-background-group">
          <div class="background-underlay "></div>
          <div class="background-image " data-fixed="true"></div>
          <div class="map" id="map"></div>
        </div>
      </section>

      <div class="map-filter">
        <figure class="map-filter">
          <ul class="filter-list">
            <?php foreach ($location_types as $key => $type) { ?>
              <li class="filter-item active" data-filter="<?= esc_attr($key) ?>" data-active="true">
                <span class="icon">
                  <img src="<?= $type['icon'] && $type['icon']['url'] ? esc_url($type['icon']['url']) : ''; ?>" alt="<?= esc_attr($type['term']->name); ?>" width="30px" />
                </span><?= esc_html($type['term']->name); ?>
              </li>
            <?php } ?>
          </ul>
        </figure>

        <hr />
        <ul class="locations-list"><!-- dynamically rendered --></ul>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}

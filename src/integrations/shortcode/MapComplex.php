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

        :root {
          --marker-yellow: #c47c29;
          --marker-red: #52121b;
          --marker-blue: #003B71;
          --marker-green: #03808b;
          --marker-gray: #97989A;
        }

        #map { height: 100%; width: 100%; }

        /* Important: Keeps markers from being cut off when they overlap */
        .gm-style-aware-marker {
            overflow: visible !important;
        }

        .filter-list { display: flex; flex-wrap: wrap; padding: 0; }
        .filter-item { position: relative; display: inline-flex; align-items: center; margin: 1rem; background-color: transparent; cursor: pointer; overflow: hidden; font-size: 1.125rem; user-select: none; }
        .filter-item.reset { padding: 0 1rem; }
        .filter-item.active { color: #EF3E42; }
        .filter-item .icon { position: relative; margin-right: 1rem; z-index: 10; }
        .filter-item, .filter-item .icon img { transition: all 0.3s ease-in-out; }
        .filter-item:not(.active):hover { color: #777; }
        .filter-item .icon img { position: relative; display: block; width: 30px; z-index: 10; }

        .locations-list { display: block; padding: 0; }
        
        .locations-list h2.section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 0.5rem;
            color: #333;
            text-transform: capitalize;
        }

        .locations-section-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); 
            gap: 1.5rem; 
            padding: 0;
            margin-bottom: 2rem;
            list-style: none;
        }

        @media (max-width: 959px) { .locations-section-grid { grid-template-columns: 1fr; } }
        @media (min-width: 960px) and (max-width: 1365px) { .locations-section-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1366px) { .locations-section-grid { grid-template-columns: repeat(3, 1fr); } }

        .locations-section-grid .location { display: inline-grid; grid-template-columns: 57px auto; width: 100%; margin: 0; cursor: pointer; user-select: none; align-items: start; }
        
        /* Set icon container to be relative for the stack */
        .locations-section-grid .location .icon { position: relative; display: block; width: 64px; height: 64px; margin-right: 1rem; }
        
        .locations-section-grid .location .content { flex: 1; display: flex; flex-direction: column; justify-content: flex-start;}
        .locations-section-grid .location .content h3 { font-size: 1.125rem; font-weight: 600; margin-bottom: 0px; margin-top:0; line-height: 1.2; }
        
        .locations-section-grid .location .content .meta-block {
            display: flex;
            flex-direction: column;
            gap: 3px; 
            font-size: 0.95rem;
            color: #333;
            line-height: 1.4;
        }

        .locations-section-grid .location .content .meta-block div.address-line {
            color: #000;
            font-weight: 400;
        }
        
        .locations-section-grid .location .content .meta-block a {
            color: #EF3E42; 
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
        }
        
        .locations-section-grid .location .content .meta-block a:hover {
            text-decoration: underline;
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

    $locations = Location::get_all_locations();
    $location_types = [];
    foreach (Location::get_all_location_types() as $location_type) {
      $location_types[$location_type['term']->slug] = $location_type;
    }

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
            constructor(map, title, address, types, lat, lng, locationsManager, phone = '') {
              this.map = map;
              this.title = title;
              this.address = address || '';
              this.types = types || [];
              this.locationsManager = locationsManager;
              this.marker = null;

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
                    <p><a href="https://www.google.com/maps/dir/?api=1&destination=${this.position.lat},${this.position.lng}" target="_blank">Directions</a></p>
                    ${this.getTelHref() ? `<p><a href="${this.getTelHref()}">${this.getDisplayPhone()}</a></p>` : ``}
                  </div>
                `,
              });
              this.infoWindowState = false;

              // --- MAP MARKER STACKED LOGIC ---
              this.buildMarkerContent = (filterSlug = null) => {
                const container = document.createElement('div');
                container.style.position = 'relative';
                container.style.width = '40px';
                container.style.height = '40px';

                const typesToRender = (filterSlug === null)
                  ? this.types
                  : this.types.filter(t => t && t.slug === filterSlug);

                typesToRender.forEach((t, i) => {
                  if (!t || !t.slug) return;
                  const lt = location_types[t.slug];
                  const iconUrl = lt?.icon?.url || '';
                  const img = document.createElement('img');
                  img.src = iconUrl;
                  
                  // DROPPED LOGIC: 
                  // Background icons (higher 'i') shift to the right and down.
                  // We reverse the z-index so the first icon (i=0) stays on top.
                  const xOffset = (filterSlug === null) ? (i * 6) : 0;
                  const yOffset = (filterSlug === null) ? (i * -6) : 0;
                  const z = 10 - i;

                  img.setAttribute('style', `
                    position: absolute; 
                    width: 30px; 
                    left: ${xOffset}px; 
                    bottom: ${yOffset}px; 
                    z-index: ${z};
                  `);
                  container.appendChild(img);
                });

                return container;
              };

              const marker = new AdvancedMarkerElement({
                map: map,
                position: this.position,
                title: title,
                content: this.buildMarkerContent(null) 
              });
              marker.addListener('click', this.clickHandler.bind(this));
              this.marker = marker;

              this.updateMarkerIcons = (filterSlug) => {
                this.marker.content = this.buildMarkerContent(filterSlug);
              };

              this.locationsManager.addLocations([this]);

              this.openInfoWindow = this.openInfoWindow.bind(this);
              this.closeInfoWindow = this.closeInfoWindow.bind(this);
              this.toggleInfoWindow = this.toggleInfoWindow.bind(this);
            }

            clickHandler() {
              if (this.infoWindowState) {
                this.infoWindow.close();
                this.infoWindowState = false;
              } else {
                this.locationsManager.closeAllInfoWindows();
                this.infoWindowState = true;
                this.infoWindow.open(this.map, this.marker);
              }
            }

            openInfoWindow() {
              this.locationsManager.closeAllInfoWindows();
              this.infoWindow.open(this.map, this.marker);
              this.infoWindowState = true;
            }
            closeInfoWindow() { this.infoWindow.close(); this.infoWindowState = false; }
            toggleInfoWindow() { this.infoWindowState ? this.closeInfoWindow() : this.openInfoWindow(); }

            // --- SIDEBAR LIST STACKED LOGIC ---
            render(specificSlug = null) {
              const listTypes = (specificSlug === null) ? this.types : this.types.filter(t => t.slug === specificSlug);

              const iconsHtml = listTypes.map((type, i) => {
                const lt = location_types[type.slug];
                const iconUrl = lt?.icon?.url || '';
                const termName = lt?.term ? lt.term.name : type.slug;
                
                // DROPPED LOGIC:
                // Shifts right (left offset) and down (top offset).
                const xOffset = (specificSlug === null) ? (i * 6) : 0;
                const yOffset = (specificSlug === null) ? (i * 6) : 0;
                const z = 10 - i;
                
                const style = `position:absolute; left:${xOffset}px; top:${yOffset}px; z-index:${z}; width:30px;`;
                return `<img src="${iconUrl}" alt="${termName}" style="${style}" />`;
              }).join('');

              let addressHtml = '';
              if (this.address) {
                  const parts = this.address.split(',');
                  if (parts.length > 1) {
                      const street = parts.shift().trim();
                      const rest = parts.join(',').trim();
                      addressHtml = `<div class="address-line">${street}</div><div class="address-line">${rest}</div>`;
                  } else {
                      addressHtml = `<div class="address-line">${this.address}</div>`;
                  }
              }

              const template = `
                <div class="icon" style="position:relative; width:45px; height:45px;">${iconsHtml}</div>
                <div class="content">
                  <h3>${this.title}</h3>
                  <div class="meta-block">
                     ${addressHtml}
                     <a href="https://www.google.com/maps/dir/?api=1&destination=${this.position.lat},${this.position.lng}" target="_blank">Directions</a>
                     ${this.phone ? `<a href="${this.getTelHref()}">${this.getDisplayPhone()}</a>` : ''}
                  </div>
                </div>
              `;

              const element = document.createElement('li');
              element.innerHTML = template;
              element.classList.add('location');

              withIntentfulInteraction(element, () => {
                const mapElement = document.getElementById('map');
                const mapRect = mapElement.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const targetScrollTop = scrollTop + mapRect.top - (windowHeight / 2) + (mapRect.height / 2);
                window.scrollTo({ top: targetScrollTop, behavior: 'smooth' });
                this.openInfoWindow();
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
              this.activeFilterSlug = null;
            }

            addLocations(locs) {
              this.locations = this.locations.concat(locs);
              locs.forEach(location => {
                if (location && location.position && typeof location.position.lat === 'number') {
                  this.bounds.extend(location.position);
                }
              });
              if (!this.bounds.isEmpty()) this.mapInstance.fitBounds(this.bounds);
            }

            updateMapLocations(slug) {
              this.activeFilterSlug = slug;
              for (const location of this.locations) {
                location.closeInfoWindow();
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
                this.updateMapLocations(null); 
                if (!this.bounds.isEmpty()) this.mapInstance.fitBounds(this.bounds);
              });

              this.filterListElement.appendChild(resetElement);
            }

            renderLocationsList() {
              this.locationsListElement.innerHTML = '';
              const activeFilters = this.filtersList.filter(f => f.active);
              
              activeFilters.forEach(filter => {
                  const filterSlug = filter.type.term.slug;
                  const filterName = filter.type.term.name;
                  const sectionLocations = this.locations.filter(loc => loc.types.some(t => t.slug === filterSlug));
                  
                  if(sectionLocations.length === 0) return; 
                  
                  const title = document.createElement('h2');
                  title.innerText = filterName;
                  title.className = 'section-title';
                  this.locationsListElement.appendChild(title);
                  
                  const grid = document.createElement('ul');
                  grid.className = 'locations-section-grid';
                  
                  sectionLocations.forEach(loc => {
                      // Only pass null if we want the stacked look
                      grid.appendChild(loc.render(this.activeFilterSlug ? filterSlug : null));
                  });
                  this.locationsListElement.appendChild(grid);
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
          const locations_data = <?= json_encode($locations) ?>;

          const filterListElement = document.querySelector('.filter-list');
          const locationsListElement = document.querySelector('.locations-list');

          const locationsManager = new LocationsManager(
            [],
            Object.values(location_types).map(type => new Filter(type)),
            filterListElement,
            locationsListElement,
            map
          );

          locations_data.forEach(loc => {
            const hasTypes = Array.isArray(loc.location_type) && loc.location_type.length;
            if (!hasTypes) return;
            new Location(map, loc.name, loc.address, loc.location_type, loc.lat, loc.lng, locationsManager, loc.phone || '');
          });

          locationsManager.filtersList.forEach(f => f.checkIfShouldShow());
          locationsManager.renderFiltersList();
          locationsManager.renderLocationsList();

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
          <div class="map" id="map"></div>
        </div>
      </section>

      <div class="map-filter">
        <figure class="map-filter">
          <ul class="filter-list"></ul>
        </figure>
        <hr />
        <div class="locations-list"></div>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
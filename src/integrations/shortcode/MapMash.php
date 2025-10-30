<?php

namespace MarketMentors\EasyLocations\src\integrations\shortcode;

use MarketMentors\EasyLocations\src\models\Location;

class MapMash
{
  public function __construct()
  {
    add_shortcode('easy-locations-map-mash', [$this, 'render']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
  }

  public function enqueue_scripts()
  {
    // Inline styles (kept lean)
    add_action('wp_head', function () {
      ob_start(); ?>
      <style type="text/css">
        :root {
          --filter-gap: .75rem;
        }

        .easy-locations-map-mash { position: relative; }

        /* Controls live ABOVE the map now */
        .map-controls {
          display: block;
          width: 100%;
          margin: 0 0 12px 0;
        }

        .filter-list {
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          gap: var(--filter-gap);
          list-style: none;
          padding: 0;
          margin: 0;
        }

       .filter-item {
  display: inline-flex;
  align-items: center;
  padding: .3rem .3rem;
  font-size: 1.2rem;
  color: #b5b5b5; /* default grey text */
  opacity: 0.6; /* slightly dimmed inactive state */
  cursor: pointer;
  user-select: none;
  transition:
    color .2s ease,
    opacity .2s ease,
    filter .2s ease;
}

.filter-item .icon img {
  width: 50px;
  height: 50px;
  display: block;
  filter: grayscale(100%) brightness(0.6); /* greyscale inactive icons */
}

/* Hover (not active) */
.filter-item:hover {
  border-color: #cfcfcf;
  opacity: 0.8;
}

/* Active state */
.filter-item.active {
  color: #252525;
  opacity: 1;
}

.filter-item.active .icon img {
  filter: none; /* restores full color */
}

/* Optional: Reset button styling */
.filter-item.reset {
  border-color: transparent;
  background: transparent;
  color: #252525;
  opacity: 1;
  padding-left: 0;
  box-shadow: none;
}


        /* Map section */
        #hero.page-section { margin:0; padding:0; }
        .section-background-group { height: 540px; }
        .section-background-group .map { height: 100%; width: 100%; }

        /* Optional: reduce map height on small screens */
        @media (max-width: 640px) {
          .section-background-group { height: 420px; }
        }
      </style>
      <?php
      echo ob_get_clean();
    });
  }

  public function render($atts)
  {
    // NOTE: replace with your actual keys as needed
    $maps_api_key = 'AIzaSyAvdJa5-XhoMP0ut39PMirYLBIuXKB_8aA';
    $maps_api_key_dev = 'AIzaSyAvdJa5-XhoMP0ut39PMirYLBIuXKB_8aA';

    $atts = shortcode_atts([
      'id' => 'easy_locations_map_mash',
    ], $atts);

    // Fetch data
    $locations = Location::get_all_locations();
    $location_types = [];
    foreach (Location::get_all_location_types() as $location_type) {
      $location_types[$location_type['term']->slug] = $location_type;
    }

    // Default filter from querystring (?filter=slug)
    $valid_values = array_keys($location_types);
    $default_filter = null;
    if (isset($_GET['filter'])) {
      $filter = htmlspecialchars(stripslashes(trim($_GET['filter'])), ENT_QUOTES, 'UTF-8');
      if (in_array($filter, $valid_values, true)) $default_filter = $filter;
    }

    // Footer scripts
    add_action('wp_footer', function () use ($maps_api_key, $maps_api_key_dev, $locations, $location_types, $default_filter) {
      ob_start(); ?>
      <script>
        // Google Maps JS API loader (unchanged)
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

      <script>
        (async () => {
          const { Map, InfoWindow } = await google.maps.importLibrary("maps");
          const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

          const location_types = <?= json_encode($location_types) ?>;
          const position_default = { lat: 42.8432136, lng: -72.3555698 };

          // Simple click helper (keeps your "intentful" feel)
          function withIntentfulInteraction(el, cb) {
            let down;
            el.addEventListener('mousedown', e => { if (e.button!==0) return; e.preventDefault(); down = Date.now(); });
            el.addEventListener('mouseup', e => { if (e.button!==0) return; e.preventDefault(); if (Date.now() - down < 333) cb(); });
            el.addEventListener('mouseleave', () => down = null);
          }

          class Filter {
            constructor(type) {
              this.type = type;
              this.active = true;
              this.element = null;
              this.locationsManager = null;
              this.handleToggle = null;
              this.visible = true;
            }
            hide() { this.visible = false; }
            show() { this.visible = true; }
            activate() { this.active = true; this.element?.classList.add('active'); }
            deactivate() { this.active = false; this.element?.classList.remove('active'); }
            toggle() {
              // Single-select behavior like your example
              this.locationsManager.filtersList.forEach(f => f.deactivate());
              this.activate();
              this.handleToggle(this.type.term.slug);
            }
            render() {
              const lt = location_types[this.type.term.slug] || {};
              const iconUrl = lt.icon?.url || '';
              const termName = lt.term?.name || this.type.term.slug;
              const el = document.createElement('li');
              el.className = 'filter-item' + (this.active ? ' active' : '');
              el.setAttribute('data-type', this.type.term.slug);
              el.innerHTML = `<span class="icon"><img src="${iconUrl}" alt="${termName}"/></span>${termName}`;
              withIntentfulInteraction(el, () => this.toggle());
              el.style.display = this.visible ? 'inline-flex' : 'none';
              this.element = el;
              return el;
            }
          }

          class SimpleLocation {
            /**
             * @param {google.maps.Map} map
             * @param {object} loc
             * @param {LocationsManager} locationsManager
             */
            constructor(map, loc, locationsManager) {
              this.map = map;
              this.locationsManager = locationsManager;

              this.title   = loc.name || '';
              this.address = loc.address || '';
              this.types   = Array.isArray(loc.location_type) ? loc.location_type : [];
              this.phone   = (loc.phone || '').toString().trim();

              if (!loc.lat || !loc.lng) throw new Error('Location must have coordinates.');
              this.position = { lat: parseFloat(loc.lat), lng: parseFloat(loc.lng) };

              // info window
              const telHref = this.phone ? `tel:${this.phone.replace(/[^+\d]/g, '')}` : '';
              const phoneHTML = telHref ? `<p><a href="${telHref}">${this.phone}</a></p>` : '';
              this.infoWindow = new InfoWindow({
                content: `
                  <div class="info-window">
                    <h3>${this.title}</h3>
                    <p>${this.address}</p>
                    <p><a href="https://www.google.com/maps/search/?api=1&query=${this.position.lat},${this.position.lng}" target="_blank" rel="noopener">Directions</a></p>
                    ${phoneHTML}
                  </div>
                `,
              });

              // build stacked/single icons
              this.buildMarkerContent = (filterSlug = null) => {
                const container = document.createElement('div');
                container.style.cssText = 'position:relative;display:block;width:32px;height:32px;--bottom-step:4px;--left-step:8px;';
                const typesToRender = (filterSlug === null) ? this.types : this.types.filter(t => t?.slug === filterSlug);
                typesToRender.forEach((t, i) => {
                  const lt = location_types[t.slug] || {};
                  const img = document.createElement('img');
                  img.src = lt.icon?.url || '';
                  img.alt = lt.term?.name || t.slug;
                  const bottom = (filterSlug === null) ? `calc(var(--bottom-step) * ${i})` : '0';
                  const left   = (filterSlug === null) ? `calc(var(--left-step) * ${i})` : '0';
                  const z      = (filterSlug === null) ? (typesToRender.length - i) : 10;
                  img.style.cssText = `position:absolute;bottom:${bottom};left:${left};width:65px;z-index:${z};`;
                  container.appendChild(img);
                });
                return container;
              };

              this.marker = new AdvancedMarkerElement({
                map: this.map,
                position: this.position,
                title: this.title,
                content: this.buildMarkerContent(null)
              });

              this.marker.addListener('click', () => {
                this.locationsManager.closeAllInfoWindows();
                this.infoWindow.open(this.map, this.marker);
              });

              // API for filter updates
              this.updateMarkerIcons = (filterSlug) => {
                this.marker.content = this.buildMarkerContent(filterSlug);
              };
            }
          }

          class LocationsManager {
            constructor(mapInstance, filtersList) {
              this.mapInstance = mapInstance;
              this.filtersList = filtersList.map(f => {
                f.locationsManager = this;
                f.handleToggle = this.updateMarkers.bind(this);
                return f;
              });
              this.locations = [];
              this.bounds = new google.maps.LatLngBounds();
              this.activeFilterSlug = null; // null = show all
            }
            add(loc) {
              this.locations.push(loc);
              this.bounds.extend(loc.position);
            }
            fit() {
              if (!this.bounds.isEmpty()) this.mapInstance.fitBounds(this.bounds);
            }
            updateMarkers(slug) {
              this.activeFilterSlug = slug;
              this.locations.forEach(loc => {
                // swap icons
                loc.updateMarkerIcons(slug);
                // show/hide markers
                const hasSlug = loc.types.some(t => t.slug === slug);
                if (slug === null || hasSlug) {
                  loc.marker.map = this.mapInstance;
                } else {
                  loc.marker.map = null;
                }
              });
            }
            renderFilters(containerEl) {
              containerEl.innerHTML = '';
              this.filtersList.forEach(f => containerEl.appendChild(f.render()));
              // Reset
              const reset = document.createElement('li');
              reset.className = 'filter-item reset';
              reset.textContent = 'Select any of our materials by clicking the icons below. Click here to reset the map.';
              withIntentfulInteraction(reset, () => {
                this.filtersList.forEach(f => f.activate());
                this.updateMarkers(null);
                this.fit();
              });
              containerEl.appendChild(reset);
            }
            closeAllInfoWindows() {
              this.locations.forEach(l => l.infoWindow.close());
            }
          }

          // Init map
          const isPhone = window.innerWidth < 640;
          const map = await new Map(document.getElementById("map"), {
            zoom: isPhone ? 9 : 7,
            center: position_default,
            mapId: "easy_locations_map_mash",
            disableDefaultUI: true,
            zoomControl: true,
            scaleControl: true,
            streetViewControl: false,
            rotateControl: true,
            fullscreenControl: false,
            mapTypeControl: false,
            keyboardShortcuts: false,
          });

          // Prepare filters manager
          const filterListEl = document.querySelector('.filter-list');
          const locationsManager = new LocationsManager(
            map,
            Object.values(location_types).map(type => new Filter(type))
          );
          locationsManager.renderFilters(filterListEl);

          // Build markers
          const rawLocations = <?= json_encode($locations) ?>;
          rawLocations.forEach(loc => {
            const hasTypes = Array.isArray(loc.location_type) && loc.location_type.length;
            if (!hasTypes) return;
            // ensure type exists
            for (const t of loc.location_type) {
              if (!location_types[t.slug]) return;
            }
            const l = new SimpleLocation(map, loc, locationsManager);
            locationsManager.add(l);
          });
          locationsManager.fit();

          // Default filter via querystring
          const default_filter = '<?= $default_filter ?: '' ?>';
          if (default_filter) {
            const df = locationsManager.filtersList.find(f => f.type.term.slug === default_filter);
            if (df) df.toggle();
          }
        })();
      </script>
      <?php
      echo ob_get_clean();
    });

    // Markup: FILTERS ABOVE MAP, no locations list
    ob_start(); ?>
    <div class="easy-locations-map-mash">
      <div class="map-controls">
        <ul class="filter-list">
          <?php foreach ($location_types as $key => $type) { ?>
            <li class="filter-item active" data-filter="<?= esc_attr($key) ?>" data-active="true">
              <span class="icon">
                <img src="<?= $type['icon']['url'] ?? '' ?>" alt="<?= esc_attr($type['term']->name) ?>" />
              </span><?= esc_html($type['term']->name) ?>
            </li>
          <?php } ?>
          <li class="filter-item reset">Reset</li>
        </ul>
      </div>

      <section id="hero" class="page-section no-padding">
        <div class="section-background-group">
          <div class="map" id="map"></div>
        </div>
      </section>
    </div>
    <?php
    return ob_get_clean();
  }
}

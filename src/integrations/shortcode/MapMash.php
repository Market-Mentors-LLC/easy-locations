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
    // Inline styles (unchanged except for what you already had)
    add_action('wp_head', function () {
      ob_start(); ?>
      <style type="text/css">
        :root { --filter-gap: .75rem; }
        .easy-locations-map-mash { position: relative; }

        .map-controls { display:block; width:100%; border-bottom:#252525; margin:0 0 12px 0; }

        .map-reset-text { text-align:center; margin-top:1rem; margin-bottom:-10px; font-family:'Oswald',sans-serif; font-weight:bold; }
        .map-reset-text a { cursor:pointer; color:#1e73be; }

        .reset-map-link { cursor:pointer; color:#1e73be; }

        .filter-list { display:flex; flex-wrap:wrap; align-items:center; justify-content:center; gap:var(--filter-gap); list-style:none; padding:0; margin:0; }

        .filter-item { display:inline-flex; align-items:center; padding:.3rem .3rem; font-size:1.2rem; color:#b5b5b5; opacity:.6; cursor:pointer; user-select:none; font-family:'Oswald',sans-serif; font-weight:bold; transition: color .2s, opacity .2s, filter .2s; }
        .filter-item .icon img { width:60px; display:block; filter:opacity(50%); }
        .filter-item:hover { opacity:.8; }
        .filter-item.active { color:#252525; opacity:1; }
        .filter-item.active .icon img { filter:none; }
        .filter-item.reset { border-color:transparent; background:transparent; color:#252525; opacity:1; padding-left:0; box-shadow:none; }

        #hero.page-section { margin:0; padding:0; }
        .section-background-group { height:540px; }
        .section-background-group .map { height:100%; width:100%; }

        @media (max-width: 640px) { .section-background-group { height:420px; } }
      </style>
      <?php echo ob_get_clean();
    });
  }

  public function render($atts)
  {
    // NOTE: replace with your actual keys as needed
    $maps_api_key = 'AIzaSyAvdJa5-XhoMP0ut39PMirYLBIuXKB_8aA';
    $maps_api_key_dev = 'AIzaSyAvdJa5-XhoMP0ut39PMirYLBIuXKB_8aA';

    $atts = shortcode_atts(['id' => 'easy_locations_map_mash'], $atts);

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
        // Google Maps JS API loader
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

          // Small helper for click/tap feel
          function withIntentfulInteraction(el, cb) {
            let down;
            el.addEventListener('mousedown', e => { if (e.button!==0) return; e.preventDefault(); down = Date.now(); });
            el.addEventListener('mouseup',   e => { if (e.button!==0) return; e.preventDefault(); if (Date.now() - down < 333) cb(); });
            el.addEventListener('mouseleave', () => down = null);
          }

          /** -------- FILTER (now multi-select) -------- */
          class Filter {
            constructor(type) {
              this.type = type;
              this.active = true;    // start active
              this.element = null;
              this.locationsManager = null;
              this.onToggle = null;
              this.visible = true;
            }
            setActive(isActive) {
              this.active = isActive;
              if (this.element) this.element.classList.toggle('active', this.active);
            }
            toggle() {
              this.setActive(!this.active);
              this.onToggle?.(this.type.term.slug, this.active);
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

          /** -------- LOCATION / MARKER -------- */
          class SimpleLocation {
            constructor(map, loc, locationsManager) {
              this.map = map;
              this.locationsManager = locationsManager;

              this.title   = loc.name || '';
              this.address = loc.address || '';
              this.types   = Array.isArray(loc.location_type) ? loc.location_type : [];
              this.phone   = (loc.phone || '').toString().trim();
              if (!loc.lat || !loc.lng) throw new Error('Location must have coordinates.');
              this.position = { lat: parseFloat(loc.lat), lng: parseFloat(loc.lng) };

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

              // Build stacked/single icons, filtered by active set if provided
              this.buildMarkerContent = (activeSet = null) => {
                const container = document.createElement('div');
                container.style.cssText = 'position:relative;display:block;width:45px;height:45px;--bottom-step:4px;--left-step:8px;';
                const typesToRender = (activeSet === null)
                  ? this.types
                  : this.types.filter(t => activeSet.has(t.slug));
                typesToRender.forEach((t, i) => {
                  const lt = location_types[t.slug] || {};
                  const img = document.createElement('img');
                  img.src = lt.icon?.url || '';
                  img.alt = lt.term?.name || t.slug;
                  const bottom = `calc(var(--bottom-step) * ${i})`;
                  const left   = `calc(var(--left-step) * ${i})`;
                  const z      = (typesToRender.length - i);
                  img.style.cssText = `position:absolute;bottom:${bottom};left:${left};width:80px;z-index:${z};`;
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

              this.updateMarkerIcons = (activeSet) => {
                this.marker.content = this.buildMarkerContent(activeSet);
              };
            }
          }

          /** -------- LOCATIONS MANAGER (maintains active Set) -------- */
         class LocationsManager {
  constructor(mapInstance, filtersList) {
    this.mapInstance = mapInstance;

    // Tracks whether we've already performed the special “first click collapses to one” behavior.
    this.firstInteractionDone = false;

    this.filtersList = filtersList.map(f => {
      f.locationsManager = this;
      // Intercept filter toggles to apply first-click logic
      f.onToggle = (slug, requestedActiveState) => {
        if (!this.firstInteractionDone && this.isAllActive()) {
          this.firstInteractionDone = true;
          this.activateOnly(slug);
        } else {
          this.setActive(slug, requestedActiveState);
        }
      };
      return f;
    });

    this.locations = [];
    this.bounds = new google.maps.LatLngBounds();

    // Start with everything active
    this.activeSlugs = new Set(Object.keys(location_types));
  }

  isAllActive() {
    return this.activeSlugs.size === Object.keys(location_types).length;
  }

  add(loc) {
    this.locations.push(loc);
    this.bounds.extend(loc.position);
  }

  fit() {
    if (!this.bounds.isEmpty()) this.mapInstance.fitBounds(this.bounds);
  }

  setActive(slug, isActive) {
    if (isActive) this.activeSlugs.add(slug);
    else this.activeSlugs.delete(slug);
    this.updateAllMarkers();
    // Keep filter pill visuals in sync
    this.filtersList.forEach(f => {
      if (f.type.term.slug === slug) f.setActive(isActive);
    });
  }

  activateAll() {
    this.activeSlugs = new Set(Object.keys(location_types));
    this.filtersList.forEach(f => f.setActive(true));
    this.firstInteractionDone = false; // enable the special behavior again after reset
    this.updateAllMarkers();
  }

  activateOnly(slug) {
    this.activeSlugs = new Set([slug]);
    this.filtersList.forEach(f => f.setActive(f.type.term.slug === slug));
    this.updateAllMarkers();
  }

  updateAllMarkers() {
    const active = this.activeSlugs;
    const showNothing = active.size === 0;

    this.locations.forEach(loc => {
      // Update stacked icons to show only active types
      loc.updateMarkerIcons(active);

      // Show marker if ANY of its types are active (AND not showing none)
      const hasAnyActive = loc.types.some(t => active.has(t.slug));
      const shouldShow = !showNothing && hasAnyActive;
      loc.marker.map = shouldShow ? this.mapInstance : null;
    });
  }

  renderFilters(containerEl) {
    containerEl.innerHTML = '';
    this.filtersList.forEach(f => containerEl.appendChild(f.render()));

    // Reset link
    const resetContainer = document.querySelector('.map-reset-text');
    if (resetContainer) {
      resetContainer.innerHTML = 'Select any of our materials by clicking the icons below. <a class="reset-map-link">Click here</a> to reset the map.';
      const resetLink = resetContainer.querySelector('.reset-map-link');
      if (resetLink) {
        withIntentfulInteraction(resetLink, () => {
          this.activateAll();
          this.fit();
        });
      }
    }
  }

  closeAllInfoWindows() { this.locations.forEach(l => l.infoWindow.close()); }
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
          locationsManager.updateAllMarkers(); // initial render

          // Default filter via querystring: if present, start with ONLY that type active
          const default_filter = '<?= $default_filter ?: '' ?>';
if (default_filter) {
  locationsManager.activateOnly(default_filter);
  locationsManager.firstInteractionDone = true; // subsequent clicks toggle normally
}
        })();
      </script>
      <?php echo ob_get_clean();
    });

    // Markup (unchanged except for your reset text container)
    ob_start(); ?>
    <div class="easy-locations-map-mash">
      <div class="map-controls">
        <p class="map-reset-text"></p>
        <ul class="filter-list"></ul>
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

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



          /** -------- FILTER (delegate clicks to manager) -------- */
class Filter {
  constructor(type) {
    this.type = type;
    this.active = true;            // start active (show all)
    this.element = null;
    this.locationsManager = null;
    this.onClick = null;           // manager will attach
    this.visible = true;
  }
  setActive(isActive) {
    this.active = isActive;
    if (this.element) this.element.classList.toggle('active', this.active);
  }
  render() {
    const lt = location_types[this.type.term.slug] || {};
    const iconUrl = lt.icon?.url || '';
    const termName = lt.term?.name || this.type.term.slug;
    const el = document.createElement('li');
    el.className = 'filter-item' + (this.active ? ' active' : '');
    el.setAttribute('data-type', this.type.term.slug);
    el.innerHTML = `<span class="icon"><img src="${iconUrl}" alt="${termName}"/></span>${termName}`;
    withIntentfulInteraction(el, () => this.onClick?.(this)); // <— delegate
    el.style.display = this.visible ? 'inline-flex' : 'none';
    this.element = el;
    return el;
  }
}

/** -------- LOCATIONS MANAGER (first-click-isolate) -------- */
class LocationsManager {
  constructor(mapInstance, filtersList) {
    this.mapInstance = mapInstance;
    this.filtersList = filtersList.map(f => {
      f.locationsManager = this;
      f.onClick = this.handleFilterClick.bind(this);
      return f;
    });
    this.locations = [];
    this.bounds = new google.maps.LatLngBounds();

    this.allSlugs = new Set(Object.keys(location_types));
    this.activeSlugs = new Set(this.allSlugs); // start with “all on”
    this.pristine = true; // <— first click should isolate
  }

  add(loc) {
    this.locations.push(loc);
    this.bounds.extend(loc.position);
  }
  fit() { if (!this.bounds.isEmpty()) this.mapInstance.fitBounds(this.bounds); }

  isAllActive() { return this.activeSlugs.size === this.allSlugs.size; }

  handleFilterClick(filterObj) {
    const slug = filterObj.type.term.slug;

    if (this.pristine && this.isAllActive()) {
      // FIRST CLICK: isolate
      this.activateOnly(slug);
      this.pristine = false;
      return;
    }

    // Afterwards: normal toggle
    const willBeActive = !filterObj.active;
    this.setActive(slug, willBeActive);
  }

  setActive(slug, isActive) {
    if (isActive) this.activeSlugs.add(slug);
    else this.activeSlugs.delete(slug);
    // reflect state on the chip
    const f = this.filtersList.find(x => x.type.term.slug === slug);
    f?.setActive(isActive);
    this.updateAllMarkers();
  }

  activateAll() {
    this.activeSlugs = new Set(this.allSlugs);
    this.filtersList.forEach(f => f.setActive(true));
    this.updateAllMarkers();
    this.pristine = true; // reset re-arms first-click-isolate
  }

  activateOnly(slug) {
    this.activeSlugs = new Set([slug]);
    this.filtersList.forEach(f => f.setActive(f.type.term.slug === slug));
    this.updateAllMarkers();
    this.pristine = false;
  }

  updateAllMarkers() {
    const active = this.activeSlugs;
    const showNothing = active.size === 0;

    this.locations.forEach(loc => {
      // update icon stack to only active types
      loc.updateMarkerIcons(active);
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

          // Default filter via querystring: start isolated and mark as not pristine
const default_filter = '<?= $default_filter ?: '' ?>';
if (default_filter) {
  locationsManager.activateOnly(default_filter);
  locationsManager.pristine = false;
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

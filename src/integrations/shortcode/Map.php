<?php

namespace MarketMentors\EasyLocations\src\integrations\shortcode;

class Map
{
  public function __construct()
  {
    add_shortcode('easy_locations_map', [$this, 'render']);
  }

  public function render($atts)
  {
    $atts = shortcode_atts([
      'id' => 'easy_locations_map',
    ], $atts);

    return 'Hello World';
  }
}

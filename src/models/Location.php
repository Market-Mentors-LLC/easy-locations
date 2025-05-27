<?php

declare(strict_types=1);

namespace MarketMentors\EasyLocations\src\models;

class Location
{
  public function __construct()
  {
    add_action('init', [$this, 'register_post_type']);
    add_action('acf/init', [$this, 'register_fields']);
  }

  public function register_post_type()
  {
    $labels = [
      'name'               => 'Locations',
      'singular_name'      => 'Location',
      'menu_name'          => 'Locations',
      'add_new'           => 'Add New',
      'add_new_item'      => 'Add New Location',
      'edit_item'         => 'Edit Location',
      'new_item'          => 'New Location',
      'view_item'         => 'View Location',
      'search_items'      => 'Search Locations',
      'not_found'         => 'No locations found',
      'not_found_in_trash' => 'No locations found in Trash',
    ];

    $args = [
      'labels'              => $labels,
      'public'              => true,
      'has_archive'         => true,
      'publicly_queryable'  => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => ['slug' => 'locations'],
      'capability_type'    => 'post',
      'supports'           => ['title', 'editor', 'thumbnail'],
      'menu_icon'          => 'dashicons-location',
    ];

    register_post_type('location', $args);
  }

  public function register_fields()
  {
    if (function_exists('acf_add_local_field_group')) {
      acf_add_local_field_group([
        'key' => 'group_location_fields',
        'title' => 'Location Details',
        'fields' => [
          [
            'key' => 'field_latitude',
            'label' => 'Latitude',
            'name' => 'latitude',
            'type' => 'number',
            'required' => 1,
            'instructions' => 'Enter the location latitude',
          ],
          [
            'key' => 'field_longitude',
            'label' => 'Longitude',
            'name' => 'longitude',
            'type' => 'number',
            'required' => 1,
            'instructions' => 'Enter the location longitude',
          ],
          [
            'key' => 'field_category',
            'label' => 'Category',
            'name' => 'category',
            'type' => 'text',
            'required' => 1,
            'instructions' => 'Enter the location category (e.g., "Hot Mix Plant", "Office / Operations Facility")',
          ],
          [
            'key' => 'field_address',
            'label' => 'Address',
            'name' => 'address',
            'type' => 'text',
            'required' => 1,
            'instructions' => 'Enter the full address',
          ],
        ],
        'location' => [
          [
            [
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'location',
            ],
          ],
        ],
      ]);
    }
  }

  public static function get_all_locations()
  {
    $locations = [];
    $args = [
      'post_type' => 'location',
      'posts_per_page' => -1,
      'post_status' => 'publish',
    ];

    $query = new \WP_Query($args);

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $locations[] = [
          'name' => get_the_title(),
          'lat' => (float)get_field('latitude'),
          'lng' => (float)get_field('longitude'),
          'category' => get_field('category'),
          'info' => get_field('address'),
        ];
      }
    }
    wp_reset_postdata();

    return $locations;
  }
}

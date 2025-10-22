<?php

declare(strict_types=1);

namespace MarketMentors\EasyLocations\src\models;

class Location
{
  public function __construct()
  {
    add_action('acf/init', [$this, 'register_fields']);
    add_action('acf/init', [$this, 'register_post_type']);
    add_action('acf/init', [$this, 'register_taxonomies']);
  }

  public static function register_post_type()
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

  public function register_taxonomies()
  {
    register_taxonomy('location-type', [
      'location',
    ], [
      'labels' => [
        'name' => 'Location Types',
        'singular_name' => 'Location Type',
        'menu_name' => 'Location Types',
        'all_items' => 'All Location Types',
        'edit_item' => 'Edit Location Type',
        'view_item' => 'View Location Type',
        'update_item' => 'Update Location Type',
        'add_new_item' => 'Add New Location Type',
        'new_item_name' => 'New Location Type Name',
        'search_items' => 'Search Location Types',
        'popular_items' => 'Popular Location Types',
        'separate_items_with_commas' => 'Separate location types with commas',
        'add_or_remove_items' => 'Add or remove location types',
        'choose_from_most_used' => 'Choose from the most used location types',
        'not_found' => 'No location types found',
        'no_terms' => 'No location types',
        'items_list_navigation' => 'Location Types list navigation',
        'items_list' => 'Location Types list',
        'back_to_items' => '← Go to location types',
        'item_link' => 'Location Type Link',
        'item_link_description' => 'A link to a location type',
      ],
      'description' => 'The Location Type is the categorization of the location.',
      'public' => true,
      'show_in_menu' => true,
      'show_in_rest' => true,
    ]);
  }

  public function register_fields()
  {
    if (function_exists('acf_add_local_field_group')) {
      acf_add_local_field_group([
        'key' => 'group_location_fields',
        'title' => 'Location Details',
        'fields' => [
          [
            'key' => 'field_category',
            'label' => 'Category',
            'name' => 'category',
            'type' => 'text',
            'required' => 1,
            'instructions' => 'Enter the location category (e.g., "Hot Mix Plant", "Office / Operations Facility")',
          ],
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
          // Location.php  (inside acf_add_local_field_group([... 'fields' => [ ... ] ]))








[
  'key' => 'field_phone',
  'label' => 'Phone',
  'name'  => 'phone',
  'type'  => 'text',
  'required' => 0,
  'instructions' => 'Enter a phone number (e.g., 555-123-4567).',
  'wrapper' => [
    'width' => '',
    'class' => '',
    'id'    => '',
  ],
  'placeholder' => '(555) 123-4567',
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

      acf_add_local_field_group(array(
        'key' => 'group_6880e6b68c628',
        'title' => 'Location Type fields',
        'fields' => array(
          array(
            'key' => 'field_6880e6b66811b',
            'label' => 'Icon',
            'name' => 'icon',
            'aria-label' => '',
            'type' => 'image',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'return_format' => 'array',
            'library' => 'all',
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => '',
            'allow_in_bindings' => 0,
            'preview_size' => 'medium',
          ),
        ),
        'location' => array(
          array(
            array(
              'param' => 'taxonomy',
              'operator' => '==',
              'value' => 'location-type',
            ),
          ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
      ));
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
          'location_type' => get_the_terms(get_the_ID(), 'location-type'),
          'address' => get_field('address'),
          'phone' => (string) get_field('phone'), // <-- make sure this is here
        ];
      }
    }
    wp_reset_postdata();

    return $locations;
  }

  public static function get_all_location_types()
  {
    $location_types = get_terms([
      'taxonomy' => 'location-type',
      'hide_empty' => false,
    ]);

    $result = [];
    foreach ($location_types as $term) {
      $icon = get_field('icon', 'location-type_' . $term->term_id);
      $result[] = [
        'term' => $term,
        'icon' => $icon,
      ];
    }
    return $result;
  }
}

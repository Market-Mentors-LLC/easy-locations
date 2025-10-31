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

  /** Register CPT */
  public static function register_post_type()
  {
    $labels = [
      'name'               => 'Locations',
      'singular_name'      => 'Location',
      'menu_name'          => 'Locations',
      'add_new'            => 'Add New',
      'add_new_item'       => 'Add New Location',
      'edit_item'          => 'Edit Location',
      'new_item'           => 'New Location',
      'view_item'          => 'View Location',
      'search_items'       => 'Search Locations',
      'not_found'          => 'No locations found',
      'not_found_in_trash' => 'No locations found in Trash',
    ];

    $args = [
      'labels'             => $labels,
      'public'             => true,
      'has_archive'        => true,
      'publicly_queryable' => true,
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

  /** Register taxonomy */
  public function register_taxonomies()
  {
    register_taxonomy('location-type', ['location'], [
      'labels' => [
        'name'                       => 'Location Types',
        'singular_name'              => 'Location Type',
        'menu_name'                  => 'Location Types',
        'all_items'                  => 'All Location Types',
        'edit_item'                  => 'Edit Location Type',
        'view_item'                  => 'View Location Type',
        'update_item'                => 'Update Location Type',
        'add_new_item'               => 'Add New Location Type',
        'new_item_name'              => 'New Location Type Name',
        'search_items'               => 'Search Location Types',
        'popular_items'              => 'Popular Location Types',
        'separate_items_with_commas' => 'Separate location types with commas',
        'add_or_remove_items'        => 'Add or remove location types',
        'choose_from_most_used'      => 'Choose from the most used location types',
        'not_found'                  => 'No location types found',
        'no_terms'                   => 'No location types',
        'items_list_navigation'      => 'Location Types list navigation',
        'items_list'                 => 'Location Types list',
        'back_to_items'              => '← Go to location types',
        'item_link'                  => 'Location Type Link',
        'item_link_description'      => 'A link to a location type',
      ],
      'description' => 'The Location Type is the categorization of the location.',
      'public'      => true,
      'show_in_menu'=> true,
      'show_in_rest'=> true,
    ]);
  }

  /** ACF field groups */
  public function register_fields()
  {
    if (!function_exists('acf_add_local_field_group')) return;

    /**
     * Post fields (Location details)
     * + Contact block (photo, name, email)
     */
    acf_add_local_field_group([
      'key'    => 'group_location_fields',
      'title'  => 'Location Details',
      'fields' => [
        [
          'key'           => 'field_category',
          'label'         => 'Category (optional)',
          'name'          => 'category',
          'type'          => 'text',
          'required'      => 0,
          'instructions'  => 'Optional label for the subheading above the word “Plant”. If empty, the first Location Type name will be used.',
        ],
        [
          'key'           => 'field_latitude',
          'label'         => 'Latitude',
          'name'          => 'latitude',
          'type'          => 'number',
          'required'      => 1,
          'instructions'  => 'Enter the location latitude.',
        ],
        [
          'key'           => 'field_longitude',
          'label'         => 'Longitude',
          'name'          => 'longitude',
          'type'          => 'number',
          'required'      => 1,
          'instructions'  => 'Enter the location longitude.',
        ],
        [
          'key'           => 'field_phone',
          'label'         => 'Phone',
          'name'          => 'phone',
          'type'          => 'text',
          'required'      => 0,
          'instructions'  => 'Enter a phone number (e.g., 555-123-4567).',
          'placeholder'   => '(555) 123-4567',
        ],
        [
          'key'           => 'field_address',
          'label'         => 'Address',
          'name'          => 'address',
          'type'          => 'text',
          'required'      => 1,
          'instructions'  => 'Enter the full address.',
        ],
        [
          'key'          => 'field_contact_heading',
          'label'        => 'Contact (for info window)',
          'name'         => 'contact_group',
          'type'         => 'group',
          'layout'       => 'block',
          'sub_fields'   => [
            [
              'key'           => 'field_contact_photo',
              'label'         => 'Contact Photo',
              'name'          => 'contact_photo',
              'type'          => 'image',
              'return_format' => 'array',
              'preview_size'  => 'thumbnail',
              'library'       => 'all',
            ],
            [
              'key'   => 'field_contact_name',
              'label' => 'Contact Name',
              'name'  => 'contact_name',
              'type'  => 'text',
            ],
            [
              'key'   => 'field_contact_email',
              'label' => 'Contact Email',
              'name'  => 'contact_email',
              'type'  => 'email',
            ],
          ],
        ],
      ],
      'location' => [
        [
          [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'location',
          ],
        ],
      ],
    ]);

    /**
     * Taxonomy fields (Location Type icons + popup logo)
     * filter_icon = icon row above the map
     * pin_icon    = marker pin on the map
     * popup_logo  = small logo in info window (top-left)
     */
    acf_add_local_field_group([
      'key'    => 'group_6880e6b68c628',
      'title'  => 'Location Type fields',
      'fields' => [
        [
          'key'           => 'field_filter_icon',
          'label'         => 'Filter Icon',
          'name'          => 'filter_icon',
          'type'          => 'image',
          'return_format' => 'array',
          'library'       => 'all',
          'preview_size'  => 'medium',
          'instructions'  => 'Shown in the icon row above the map.',
        ],
        [
          'key'           => 'field_pin_icon',
          'label'         => 'Map Pin',
          'name'          => 'pin_icon',
          'type'          => 'image',
          'return_format' => 'array',
          'library'       => 'all',
          'preview_size'  => 'medium',
          'instructions'  => 'Used for the markers on the map.',
        ],
        [
          'key'           => 'field_popup_logo',
          'label'         => 'Popup Logo',
          'name'          => 'popup_logo',
          'type'          => 'image',
          'return_format' => 'array',
          'library'       => 'all',
          'preview_size'  => 'medium',
          'instructions'  => 'Logo shown in the top-left of the info window.',
        ],
      ],
      'location' => [
        [
          [
            'param'    => 'taxonomy',
            'operator' => '==',
            'value'    => 'location-type',
          ],
        ],
      ],
      'position'        => 'normal',
      'style'           => 'default',
      'label_placement' => 'top',
      'active'          => true,
    ]);
  }

  /** Data helpers */
  public static function get_all_locations(): array
  {
    $locations = [];
    $query = new \WP_Query([
      'post_type'      => 'location',
      'posts_per_page' => -1,
      'post_status'    => 'publish',
    ]);

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();

        $contact = (array) get_field('contact_group') ?: [];
        $photo   = isset($contact['contact_photo']) && is_array($contact['contact_photo']) ? $contact['contact_photo'] : null;

        $locations[] = [
          'id'            => get_the_ID(),
          'name'          => get_the_title(),
          'lat'           => (float) get_field('latitude'),
          'lng'           => (float) get_field('longitude'),
          'location_type' => get_the_terms(get_the_ID(), 'location-type'),
          'address'       => (string) get_field('address'),
          'phone'         => (string) get_field('phone'),
          'category'      => (string) get_field('category'),
          'contact'       => [
            'name'  => isset($contact['contact_name'])  ? (string) $contact['contact_name']  : '',
            'email' => isset($contact['contact_email']) ? (string) $contact['contact_email'] : '',
            'photo' => $photo, // array or null
          ],
        ];
      }
    }
    wp_reset_postdata();

    return $locations;
  }

  public static function get_all_location_types(): array
  {
    $location_types = get_terms([
      'taxonomy'   => 'location-type',
      'hide_empty' => false,
    ]);

    $result = [];
    foreach ($location_types as $term) {
      $filter_icon = get_field('filter_icon', 'location-type_' . $term->term_id);
      $pin_icon    = get_field('pin_icon',    'location-type_' . $term->term_id);
      $popup_logo  = get_field('popup_logo',  'location-type_' . $term->term_id);

      // Back-compat: fall back to legacy single 'icon' if present
      if (!$filter_icon || !$pin_icon) {
        $legacy_icon = get_field('icon', 'location-type_' . $term->term_id);
        $filter_icon = $filter_icon ?: $legacy_icon;
        $pin_icon    = $pin_icon    ?: $legacy_icon;
      }

      $result[] = [
        'term'        => $term,
        'filter_icon' => $filter_icon,
        'pin_icon'    => $pin_icon,
        'popup_logo'  => $popup_logo,
      ];
    }

    return $result;
  }
}

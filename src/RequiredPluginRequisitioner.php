<?php

declare(strict_types=1);

namespace MarketMentors\EasyLocations\src;

// Use the global plugin_dir_path function
require_once \plugin_dir_path(dirname(__FILE__)) . 'vendor/tgmpa/tgm-plugin-activation/class-tgm-plugin-activation.php';

class RequiredPluginRequisitioner
{

  /**
   * The plugins to be required.
   * 
   * Array of plugin arrays. Required keys are name and slug. If the source is NOT from the .org repo, then source is also required.
   * 
   * @since 0.0.1
   * 
   * @var array
   */
  private array $plugins = [
    /*
        This is an example of how to include a plugin from the WordPress Plugin Repository.

        [
          'name'      => 'BuddyPress',
          'slug'      => 'buddypress',
          'required'  => false,
        ],

        This is an example of how to include a plugin pre-packaged with a theme.

        [
          'name'               => 'TGM Example Plugin', // The plugin name.
          'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
          'source'             => plugin_dir_path(__FILE__) . '/assets/packaged-plugins/tgm-example-plugin.zip', // The plugin source.
          'required'           => true, // If false, the plugin is only 'recommended' instead of required.
          'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
          'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
          'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
          'external_url'       => '', // If set, overrides default API URL and points to an external URL.
          'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
        ],
        
      */];

  public function __construct(
    array $plugins = [],
  ) {
    $this->plugins = $plugins;
  }

  /**
   * Array of configuration settings. Amend each line as needed.
   *
   * TGMPA will start providing localized text strings soon. If you already have translations of our standard
   * strings available, please help us make TGMPA even better by giving us access to these translations or by
   * sending in a pull-request with .po file(s) with the translations.
   *
   * Only uncomment the strings in the config array if you want to customize the strings.
   */
  public static function Config(): array
  {
    return [
      'id'           => 'easy-locations',               // Unique ID for hashing notices for multiple instances of TGMPA.
      'default_path' => '',                      // Default absolute path to bundled plugins.
      'menu'         => 'tgmpa-install-plugins', // Menu slug.
      'parent_slug'  => 'themes.php',            // Parent menu slug.
      'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
      'has_notices'  => true,                    // Show admin notices or not.
      'dismissable'  => false,                   // If false, a user cannot dismiss the nag message.
      'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
      'is_automatic' => true,                    // Automatically activate plugins after installation or not.
      'message'      => '',                      // Message to output right before the plugins table.
      'strings'      => [
        'page_title'                      => \__('Install Required Plugins', 'easy-locations'),
        'menu_title'                      => \__('Install Plugins', 'easy-locations'),
        /* translators: %s: plugin name. */
        'installing'                      => \__('Installing Plugin: %s', 'easy-locations'),
        /* translators: %s: plugin name. */
        'updating'                        => \__('Updating Plugin: %s', 'easy-locations'),
        'oops'                            => \__('Something went wrong with the plugin API.', 'easy-locations'),
        'notice_can_install_required'     => \_n_noop(
          /* translators: 1: plugin name(s). */
          'This theme requires the following plugin: %1$s.',
          'This theme requires the following plugins: %1$s.',
          'easy-locations'
        ),
        'notice_can_install_recommended'  => \_n_noop(
          /* translators: 1: plugin name(s). */
          'This theme recommends the following plugin: %1$s.',
          'This theme recommends the following plugins: %1$s.',
          'easy-locations'
        ),
        'notice_ask_to_update'            => \_n_noop(
          /* translators: 1: plugin name(s). */
          'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
          'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
          'easy-locations'
        ),
        'notice_ask_to_update_maybe'      => \_n_noop(
          /* translators: 1: plugin name(s). */
          'There is an update available for: %1$s.',
          'There are updates available for the following plugins: %1$s.',
          'easy-locations'
        ),
        'notice_can_activate_required'    => \_n_noop(
          /* translators: 1: plugin name(s). */
          'The following required plugin is currently inactive: %1$s.',
          'The following required plugins are currently inactive: %1$s.',
          'easy-locations'
        ),
        'notice_can_activate_recommended' => \_n_noop(
          /* translators: 1: plugin name(s). */
          'The following recommended plugin is currently inactive: %1$s.',
          'The following recommended plugins are currently inactive: %1$s.',
          'easy-locations'
        ),
        'install_link'                    => \_n_noop(
          'Begin installing plugin',
          'Begin installing plugins',
          'easy-locations'
        ),
        'update_link'                       => \_n_noop(
          'Begin updating plugin',
          'Begin updating plugins',
          'easy-locations'
        ),
        'activate_link'                   => \_n_noop(
          'Begin activating plugin',
          'Begin activating plugins',
          'easy-locations'
        ),
        'return'                          => \__('Return to Required Plugins Installer', 'easy-locations'),
        'plugin_activated'                => \__('Plugin activated successfully.', 'easy-locations'),
        'activated_successfully'          => \__('The following plugin was activated successfully:', 'easy-locations'),
        /* translators: 1: plugin name. */
        'plugin_already_active'           => \__('No action taken. Plugin %1$s was already active.', 'easy-locations'),
        /* translators: 1: plugin name. */
        'plugin_needs_higher_version'     => \__('Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'easy-locations'),
        /* translators: 1: dashboard link. */
        'complete'                        => \__('All plugins installed and activated successfully. %1$s', 'easy-locations'),
        'dismiss'                         => \__('Dismiss this notice', 'easy-locations'),
        'notice_cannot_install_activate'  => \__('There are one or more required or recommended plugins to install, update or activate.', 'easy-locations'),
        'contact_admin'                   => \__('Please contact the administrator of this site for help.', 'easy-locations'),

        'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
      ],
    ];
  }

  /**
   * Register the required plugins for this theme.
   *
   * The variables passed to the `tgmpa()` function should be:
   * - an array of plugin arrays;
   * - optionally a configuration array.
   * If you are not changing anything in the configuration array, you can remove the array and remove the
   * variable from the function call: `tgmpa( $plugins );`.
   * In that case, the TGMPA default settings will be used.
   *
   * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
   */
  public function register(): void
  {
    \add_action(
      'tgmpa_register',
      function () {
        tgmpa($this->plugins, self::Config());
      }
    );
  }
}

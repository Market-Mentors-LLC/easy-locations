<?php

declare(strict_types=1);

namespace MarketMentors\EasyLocations\src;

/**
 * UpdaterConfig class.
 * 
 * This class is used to configure the updater.
 * 
 * @package MarketMentors\EasyLocations
 * 
 * @since 0.0.1
 * 
 * @author Market Mentors, LLC.
 * 
 * @property string $metadataUrl The URL of the plugin's metadata file.
 * @property string $fullPath The full path to the main plugin file.
 * @property string $branch The branch to use.
 * @property string $authToken The authentication token.
 * @property string $slug The plugin slug.
 * @property int $checkPeriod The number of hours between checks for updates.
 * @property string $optionName The name of the option that will store the
 * information about the last update check.
 * @property string $muPluginFile The name of the mu-plugin file that will be used to load the plugin.
 * 
 */
final class UpdaterConfig
{
  public string $metadataUrl;
  public string $fullPath;
  public string $branch;
  public string $authToken;
  public string $slug;
  public int $checkPeriod;
  public string $optionName;
  public string $muPluginFile;

  /**
   * UpdaterConfig constructor.
   * 
   * @since 0.0.1
   * 
   * @param string $metadataUrl
   * @param string $fullPath
   * @param string $branch
   * @param string $authToken
   * @param string $slug
   * @param int $checkPeriod
   * @param string $optionName
   * @param string $muPluginFile
   */
  public function __construct(
    string $metadataUrl,
    string $fullPath,
    string $branch,
    string $authToken = '',
    string $slug = '',
    int $checkPeriod = 12,
    string $optionName = '',
    string $muPluginFile = ''
  ) {
    $this->metadataUrl = $metadataUrl;
    $this->fullPath = $fullPath;
    $this->branch = $branch;
    $this->authToken = $authToken;
    $this->slug = $slug;
    $this->checkPeriod = $checkPeriod;
    $this->optionName = $optionName;
    $this->muPluginFile = $muPluginFile;
  }
}

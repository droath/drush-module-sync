<?php

namespace Droath\ModuleSync;

use Symfony\Component\Yaml\Yaml;

/**
 * Define sync configuration class.
 */
class SyncConfig {

  /**
   * Configuration.
   *
   * @var array
   */
  protected $config;

  const DEFAULT_VERSION = 1;
  const DEFAULT_FILENAME = 'module-sync.yml';

  use DrupalFileLookupTrait;

  /**
   * Constructor for module sync configuration.
   */
  public function __construct($path = NULL) {
    if (!isset($path)) {
      $path = $this->configPathDiscovery();
    }

    $this->config = $this->parseConfig($path);
  }

  /**
   * Get configuration version.
   *
   * @return int
   *   The config version.
   */
  public function getVersion() {
    return isset($this->config['version'])
      ? $this->config['version']
      : self::DEFAULT_VERSION;
  }

  /**
   * Get configuration scopes.
   *
   * @return array
   *   An array of available scopes.
   */
  public function getScopes() {
    return array_keys($this->config['scope']);
  }

  /**
   * Has valid config scope.
   *
   * @param string $scope
   *   The scope name.
   *
   * @return bool
   *   Has configuration scope defined.
   */
  public function hasScope($scope) {
    $scopes = $this->getScopes();
    return in_array($scope, $scopes);
  }

  /**
   * Get configuration exclude types.
   *
   * @return array
   *   An array of configuration exclude types.
   */
  public function getExcludeTypes() {
    return isset($this->config['exclude_types'])
      ? $this->config['exclude_types']
      : [];
  }

  /**
   * Get modules by scope.
   *
   * @param string $scope
   *   The scope name.
   *
   * @return array
   *   An array of Drupal modules for a given scope.
   */
  public function getModulesByScope($scope) {
    if (!$this->hasScope($scope)) {
      throw new \InvalidArgumentException(
        'Invalid scope has been passed.'
      );
    }
    $modules = $this->getModules();

    return !empty($modules) ? $modules[$scope] : [];
  }

  /**
   * Get modules keyed by their scope.
   *
   * @return array
   *   An array of Drupal modules keyed by their scope value.
   */
  protected function getModules() {
    $modules = [];

    foreach ($this->config['scope'] as $scope => $info) {
      if (!isset($info['modules']) && empty($this->config['base'])) {
        continue;
      }
      $modules[$scope] = !empty($info['modules'])
        ? $info['modules']
        : [];

      if (isset($info['extend_base']) && $info['extend_base']) {
        $modules[$scope] = array_merge($modules[$scope], $this->config['base']);
      }
    }

    return $modules;
  }

  /**
   * Configuration path discovery.
   */
  protected function configPathDiscovery() {
    $filepath = $this->lookupFilePath();
    $filename = $filepath . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;

    if (!file_exists($filename)) {
      throw new \InvalidArgumentException(
        'Undefined or non-existent configuration path.'
      );
    }

    return $filename;
  }

  /**
   * Parse configuration.
   *
   * @param string $config_path
   *   The valid path to the YAML configuration.
   *
   * @return array
   *   An array representation of the YAML configuration.
   */
  protected function parseConfig($config_path) {
    if (!file_exists($config_path) || !is_file($config_path)) {
      throw new \InvalidArgumentException(
        'Invalid path to the module-sync YAML configuration.'
      );
    }

    return Yaml::parse(file_get_contents($config_path));
  }

}

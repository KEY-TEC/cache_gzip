<?php
/**
 * @file
 * Contains \Drupal\cache_gzip\Cache\GzipBackendFactory.
 */

namespace Drupal\cache_gzip\Cache;

use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Factory for cache gzip backend.
 */
class GzipBackendFactory implements CacheFactoryInterface {

  use ContainerAwareTrait;

  /**
   * @var array
   */
  protected $settings = [];

  /**
   * @var \Drupal\cache_gzip\Cache\GZipBackendDecorator[]
   */
  protected $bins;

  /**
   * GzipBackendFactory constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings->get('cache_gzip', []);
  }

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    // Reuse generated backends to avoid reinitializations.
    if (!isset($this->bins[$bin])) {
      $backend = $this->getCacheBackend($bin, 'cache.backend.database');
      $this->bins[$bin] = new GZipBackendDecorator($backend);
    }
    return $this->bins[$bin];
  }

  /**
   * Create cache backend for bin from the given config array.
   *
   * @param string $bin
   *   Holds cache bin name to create the backend for.
   * @param string $backend
   *   Holds the name of the backend factory service.
   *
   * @return \Drupal\Core\Cache\CacheBackendInterface
   *   The cache backend initialised for this bin.
   *
   * @throws \Exception
   */
  protected function getCacheBackend($bin, $backend) {
    $factory = $this->container->get($backend);
    // Check if we got a cache factory here.
    if (!$factory instanceof CacheFactoryInterface) {
      throw new \Exception(sprintf('Services "%s" does not implement CacheFactoryInterface', $backend));
    }

    return $factory->get($bin);
  }
}

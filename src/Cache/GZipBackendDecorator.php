<?php
/**
 * @file
 * Contains \Drupal\cache_gzip\Cache\GZipBackendDecorator.
 */

namespace Drupal\cache_gzip\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Cache gzip decorator.
 */
class GZipBackendDecorator implements CacheBackendInterface {

  const PREFIX_KEY = 'gz_ser';

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $sourceBackend;

  /**
   * GZipBackendDecorator constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $source_backend
   */
  public function __construct(CacheBackendInterface $source_backend) {

    $this->sourceBackend = $source_backend;
  }

  /**
   * {@inheritdoc}
   */
  public function get($cid, $allow_invalid = FALSE) {
    $cids = [$cid];
    $cache = $this->getMultiple($cids, $allow_invalid);
    return reset($cache);
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(&$cids, $allow_invalid = FALSE) {
    $items = $this->sourceBackend->getMultiple($cids, $allow_invalid);
    foreach ($items as &$item) {
      if (!empty($item->data) && isset($item->data[GZipBackendDecorator::PREFIX_KEY])) {
        $uncompressed = gzuncompress($item->data[GZipBackendDecorator::PREFIX_KEY]);
        $item->data = unserialize($uncompressed);
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function set($cid, $data, $expire = Cache::PERMANENT, array $tags = []) {
    $this->setMultiple([
      $cid => [
        'data' => $data,
        'expire' => $expire,
        'tags' => $tags,
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $items) {
    foreach ($items as &$item) {
      if (!empty($item['data'])) {
        if (!is_string($item['data'])) {
          $serialized = serialize($item['data']);
          $compressed = [GZipBackendDecorator::PREFIX_KEY => gzcompress($serialized)];
          $item['data'] = $compressed;
        }
        else {
          $item['data'] = gzcompress($item['data']);
        }
      }
    }
    return $this->sourceBackend->setMultiple($items);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($cid) {
    return $this->sourceBackend->delete($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $cids) {
    return $this->sourceBackend->deleteMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    return $this->sourceBackend->deleteAll();
  }

  /**
   * {@inheritdoc}
   */
  public function invalidate($cid) {
    return $this->sourceBackend->invalidate($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateMultiple(array $cids) {
    return $this->sourceBackend->invalidateMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateAll() {
    return $this->sourceBackend->invalidateAll();
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    return $this->sourceBackend->garbageCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function removeBin() {
    return $this->sourceBackend->removeBin();

  }

}

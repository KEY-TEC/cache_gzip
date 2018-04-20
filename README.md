# cache_gzip

The module provides a cache backend decorator which compress uncompress the data using gzcompress and gzuncompress before it is cached.
This saves up 80% of the cached data. 
## Installation

1. Download cache_gzip module or [add it via composer](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies)
2. Enable `cache_gzip` module (e.g. `drush en cache_gzip`)
3. Change the cache backend for your bin (e.g. `dynamic_page_cache`) in your _settings.php_
```php
<?php
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.gzip_decorator';
?>
```
4. Configure your decorated cache backend for your bin in your _settings.php_:

```php
<?php
  $settings['cache_gzip'] = ['backend' => 'cache.backend.database'];
?>
```

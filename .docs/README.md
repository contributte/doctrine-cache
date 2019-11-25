# Nettrine Cache

[Doctrine\Cache](https://www.doctrine-project.org/projects/doctrine-cache/en/1.8/index.html) for Nette Framework.

## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Usage](#usage)

## Setup

Install package

```bash
composer require nettrine/cache
```

Register extension

```yaml
extensions:
    nettrine.cache: Nettrine\Cache\DI\CacheExtension
```

## Configuration

Extension wil try to choose a cache driver automatically but you may need to specify one.

`PhpFileCache` and eventually `ApcuCache` are the automatically chosen defaults.

```yaml
nettrine.cache:
    driver: Doctrine\Common\Cache\MemcachedCache()
```

Doctrine provide many drivers you can use by default:

- `Doctrine\Common\Cache\ApcuCache`
- `Doctrine\Common\Cache\ArrayCache`
- `Doctrine\Common\Cache\ChainCache`
- `Doctrine\Common\Cache\CouchbaseBucketCache`
- `Doctrine\Common\Cache\FilesystemCache`
- `Doctrine\Common\Cache\MemcachedCache`
- `Doctrine\Common\Cache\MongoDBCache`
- `Doctrine\Common\Cache\PhpFileCache`
- `Doctrine\Common\Cache\PredisCache`
- `Doctrine\Common\Cache\RedisCache`
- `Doctrine\Common\Cache\SQLite3Cache`
- `Doctrine\Common\Cache\VoidCache`
- `Doctrine\Common\Cache\WinCacheCache`
- `Doctrine\Common\Cache\ZendDataCache`

## Usage

See [Doctrine\Cache docs](https://www.doctrine-project.org/projects/doctrine-cache/en/1.8/index.html), this is just a DI integration.

```php
use Doctrine\Common\Cache\Cache;

class MyClass {
	
	/** @var Cache */
	private $cache;
	
	public function __construct(Cache $cache) {
		$this->cache = $cache;		
	}
	
}
```

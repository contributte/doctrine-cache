# Nettrine Cache

[Doctrine/Cache](https://www.doctrine-project.org/projects/cache.html) for Nette Framework.


## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Usage](#usage)
- [Examples](#examples)


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

**Schema definition**

 ```yaml
nettrine.cache:
  driver: <class|service>
```

**Under the hood**

Extension will try to choose a cache driver automatically but you may need to specify one.

`PhpFileCache` and eventually `ApcuCache` are the automatically chosen by default. Overrides it
using `driver` key.

```yaml
nettrine.cache:
  driver: Doctrine\Common\Cache\ArrayCache()
```

Doctrine provides many drivers, see more at [doctrine/cache documentation](https://www.doctrine-project.org/projects/doctrine-cache/en/1.8/index.html).

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

You can count on [Nette Dependency Injection](https://doc.nette.org/en/3.0/dependency-injection).

```php
use Doctrine\Common\Cache\Cache;

class MyWorker {

  /** @var Cache */
  private $cache;

  public function __construct(Cache $cache) {
    $this->cache = $cache;
  }

}
```

Register reader `MyWorker` under services in NEON file.

```yaml
services:
  - MyWorker
```

## Examples

You can find more examples in [planette playground](https://github.com/planette/playground) repository.

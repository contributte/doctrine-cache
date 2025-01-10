# Contributte Doctrine Cache

Integration of [Doctrine Cache](https://www.doctrine-project.org/projects/cache.html) for Nette Framework.

## Content

- [Installation](#installation)
- [Configuration](#configuration)
  - [Minimal configuration](#minimal-configuration)
  - [Advanced configuration](#advanced-configuration)
- [Usage](#usage)
- [Examples](#examples)

## Installation

Install package using composer.

```bash
composer require nettrine/cache
```

Register prepared [compiler extension](https://doc.nette.org/en/dependency-injection/nette-container) in your `config.neon` file.

```neon
extensions:
  nettrine.cache: Nettrine\Cache\DI\CacheExtension
```

## Configuration

### Minimal configuration

```neon
nettrine.cache:
  driver: Symfony\Component\Cache\Adapter\FilesystemAdapter(%tempDir%/cache/nettrine-cache)
```

### Advanced configuration

 ```yaml
nettrine.cache:
  driver: <class|service>
```

> [!WARNING]
> Cache adapter must implement `Psr\Cache\CacheItemPoolInterface` interface.
> Use any PSR-6 + PSR-16 compatible cache library like `symfony/cache` or `nette/caching`.

In the simplest case, you can define only `adapter`.

```neon
nettrine.cache:
  # Create cache manually
  adapter: App\CacheService(%tempDir%/cache/orm)

  # Use registered cache service
  adapter: @cacheService
```

> [!IMPORTANT]
> You should always use cache for production environment. It can significantly improve performance of your application.
> Pick the right cache adapter for your needs.
> For example from symfony/cache:
>
> - `FilesystemAdapter` - if you want to cache data on disk
> - `ArrayAdapter` - if you want to cache data in memory
> - `ApcuAdapter` - if you want to cache data in memory and share it between requests
> - `RedisAdapter` - if you want to cache data in memory and share it between requests and servers
> - `ChainAdapter` - if you want to cache data in multiple storages

The extension will automatically guess the best cache adapter for you.

- `FilesystemAdapter` - if you have `tempDir` defined
- `ArrayAdapter` - if you are in CLI mode
- `ApcuAdapter` - if you have `apcu` extension enabled
- **defined** - if you have defined `adapter` in configuration

## Usage

There is no need to use cache directly. It is used by other packages like:

- [DBAL](https://github.com/contributte/doctrine-dbal)
- [ORM](https://github.com/contributte/doctrine-orm)
- [Migrations](https://github.com/contributte/doctrine-migrations)
- [Annotations](https://github.com/contributte/doctrine-annotations)
- [Fixtures](https://github.com/contributte/doctrine-fixtures)

## Examples

> [!TIP]
> Take a look at more examples in [contributte/doctrine](https://github.com/contributte/doctrine/tree/master/.docs).

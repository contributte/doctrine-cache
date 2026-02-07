![](https://heatbadger.now.sh/github/readme/contributte/doctrine-cache/?deprecated=1)

<p align=center>
    <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
    <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
    <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
    Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

## Disclaimer

| :warning: | This project is no longer being maintained. Please use [symfony/cache](https://symfony.com/doc/current/components/cache.html).|
|---|---|

| Composer | [`nettrine/cache`](https://packagist.org/packages/nettrine/cache) |
|---| --- |
| Version | ![](https://badgen.net/packagist/v/nettrine/cache) |
| PHP | ![](https://badgen.net/packagist/php/nettrine/cache) |
| License | ![](https://badgen.net/github/license/contributte/doctrine-cache) |

## About

Integration of [Doctrine Cache](https://www.doctrine-project.org/projects/cache.html) for Nette Framework.

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
  adapter: Symfony\Component\Cache\Adapter\FilesystemAdapter(directory: %tempDir%/cache/nettrine-cache)
```

### Advanced configuration

 ```yaml
nettrine.cache:
  adapter: <class|service>
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

## Versions

| State  | Version | Branch   | Nette  | PHP     |
|--------|---------|----------|--------|---------|
| dev    | `^0.6`  | `master` | `3.2+` | `>=8.2` |
| stable | `^0.5`  | `master` | `3.2+` | `>=8.2` |

## Development

This package was maintained by these authors.

<a href="https://github.com/f3l1x">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.

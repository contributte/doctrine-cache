<?php declare(strict_types = 1);

namespace Nettrine\Cache\DI;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\VoidCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\ORM\Configuration;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nettrine\Cache\InvalidStateException;

class DoctrineCacheExtension extends CompilerExtension
{

	/** @var string[] */
	public const DRIVERS = [
		'apcu' => ApcuCache::class,
		'array' => ArrayCache::class,
		'filesystem' => FilesystemCache::class,
		'memcache' => MemcacheCache::class,
		'memcached' => MemcachedCache::class,
		'redis' => RedisCache::class,
		'void' => VoidCache::class,
		'xcache' => XcacheCache::class,
	];

	/** @var string[] */
	private const CACHE_SETTERS = [
		'metadata' => 'setMetadataCacheImpl',
		'query' => 'setQueryCacheImpl',
		'result' => 'setResultCacheImpl',
		'hydration' => 'setHydrationCacheImpl',
	];

	/** @var array */
	public $defaults = [
		'default' => 'filesystem',
		'metadata' => [
			'active' => true,
			'type' => null,
			'class' => null,
		],
		'query' => [
			'active' => false,
			'type' => null,
			'class' => null,
		],
		'result' => [
			'active' => false,
			'type' => null,
			'class' => null,
		],
		'hydration' => [
			'active' => false,
			'type' => null,
			'class' => null,
		],
	];

	/** @var ServiceDefinition[] */
	private $created = [];

	protected function getFactory(array $options, string $default)
	{
		if (isset($options['type'])) {
			if (!isset(self::DRIVERS[$options['type']])) {
				throw new InvalidStateException(sprintf('Unsupported cache driver "%s"', $options['type']));
			}

			return self::DRIVERS[$options['type']];
		}

		if (isset($options['class'])) {
			return $options['class'];
		}

		return $default;
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		if (!isset(self::DRIVERS[$config['default']])) {
			throw new InvalidStateException(sprintf('Unsupported cache driver "%s"', $config['default']));
		}
		$default = self::DRIVERS[$config['default']];
		unset($config['default']);

		foreach ($config as $name => $options) {
			if (!$options['active']) {
				continue;
			}

			$service = $builder->addDefinition($this->prefix($name))
				->setType(Cache::class)
				->setFactory($factory = $this->getFactory($options, $default))
				->setAutowired(false);

			if ($factory === FilesystemCache::class) {
				$service->setArguments([$builder->parameters['tempDir'] . '/cache/Doctrine.' . ucfirst($name)]);
			}

			$this->created[$name] = $service;
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$service = $builder->getDefinitionByType(Configuration::class);

		foreach ($this->created as $name => $definition) {
			$service->addSetup(self::CACHE_SETTERS[$name], [$definition]);
		}
	}

}

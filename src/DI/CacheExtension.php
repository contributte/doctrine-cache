<?php declare(strict_types = 1);

namespace Nettrine\Cache\DI;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\InvalidStateException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class CacheExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'driver' => Expect::anyOf(
				Expect::string(),
				Expect::array(),
				Expect::type(Statement::class)
			)->nullable(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if ($config->driver === null) {
			// auto choose
			$driverDefinition = $builder->addDefinition($this->prefix('driver'))
				->setType(Cache::class);

			if (isset($builder->parameters['tempDir'])) {
				$driverDefinition->setFactory(PhpFileCache::class, [
					$builder->parameters['tempDir'] . '/cache/nettrine.cache',
				]);
			} elseif (function_exists('apcu_exists')) {
				$driverDefinition->setFactory(ApcuCache::class);
			} else {
				throw new InvalidStateException(sprintf(
					'Unable to find an available cache driver, please provide one via \'%s\' configuration.',
					sprintf('%s > driver', $this->name)
				));
			}
		} else {
			$builder->addDefinition($this->prefix('driver'))
				->setFactory($config->driver);
		}
	}

}

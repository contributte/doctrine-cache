<?php declare(strict_types = 1);

namespace Nettrine\Cache\DI;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\InvalidStateException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tracy\Debugger;

/**
 * @property-read stdClass $config
 */
final class CacheExtension extends CompilerExtension
{

	public function __construct(private ?bool $debugMode = null)
	{
		if ($this->debugMode === null) {
			$this->debugMode = class_exists(Debugger::class) && Debugger::$productionMode === false;
		}
	}

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

			if ($this->debugMode === true) {
				/** @phpstan-ignore classConstant.deprecatedClass */
				if (class_exists(ArrayCache::class)) {
					/** @phpstan-ignore classConstant.deprecatedClass */
					$driverDefinition->setFactory(ArrayCache::class);
				} else {
					$driverDefinition->setFactory(DoctrineProvider::class . '::wrap', [
						new Statement(ArrayAdapter::class),
					]);
				}
			} elseif (function_exists('apcu_exists')) {
				/** @phpstan-ignore classConstant.deprecatedClass */
				if (class_exists(ApcuCache::class)) {
					/** @phpstan-ignore classConstant.deprecatedClass */
					$driverDefinition->setFactory(ApcuCache::class);
				} else {
					$driverDefinition->setFactory(DoctrineProvider::class . '::wrap', [
						new Statement(ApcuAdapter::class),
					]);
				}
			} elseif (isset($builder->parameters['tempDir'])) {
				/** @phpstan-ignore classConstant.deprecatedClass */
				if (class_exists(PhpFileCache::class)) {
					/** @phpstan-ignore classConstant.deprecatedClass */
					$driverDefinition->setFactory(PhpFileCache::class, [
						$builder->parameters['tempDir'] . '/cache/nettrine.cache',
					]);
				} else {
					$driverDefinition->setFactory(DoctrineProvider::class . '::wrap', [
						new Statement(FilesystemAdapter::class, [
							'directory' => $builder->parameters['tempDir'] . '/cache/nettrine.cache',
						]),
					]);
				}
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

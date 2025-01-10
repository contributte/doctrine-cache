<?php declare(strict_types = 1);

namespace Nettrine\Cache\DI;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\InvalidStateException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;
use Symfony\Component\Cache\Adapter\AdapterInterface;
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
			'adapter' => Expect::anyOf(
				Expect::string(),
				Expect::type(Statement::class)
			)->nullable(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if ($config->adapter === null) {
			// auto choose
			$adapterDefinition = $builder->addDefinition($this->prefix('adapter'))
				->setType(AdapterInterface::class);

			if ($this->debugMode === true) {
				$adapterDefinition->setFactory(ArrayAdapter::class);
			} elseif (isset($builder->parameters['tempDir'])) {
				$adapterDefinition->setFactory(FilesystemAdapter::class, [
					'directory' => $builder->parameters['tempDir'] . '/cache/nettrine.cache',
				]);
			} elseif (function_exists('apcu_exists')) {
				$adapterDefinition->setFactory(ApcuAdapter::class);
			} else {
				throw new InvalidStateException(sprintf(
					'Unable to find an available cache adapter, please provide one via \'%s\' configuration.',
					sprintf('%s > adapter', $this->name)
				));
			}

			$builder->addDefinition($this->prefix('driver'))
				->setType(Cache::class)
				->setFactory(DoctrineProvider::class . '::wrap', [
					new Statement($adapterDefinition),
				]);
		} else {
			$adapterDefinition = $builder->addDefinition($this->prefix('adapter'))
				->setFactory($config->adapter)
				->setType(AdapterInterface::class);

			$builder->addDefinition($this->prefix('driver'))
				->setType(Cache::class)
				->setFactory(DoctrineProvider::class . '::wrap', [
					new Statement($adapterDefinition),
				]);
		}
	}

}

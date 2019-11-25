<?php declare(strict_types = 1);

namespace Tests\Nettrine\Cache\Unit\DI;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\InvalidStateException;
use Nettrine\Cache\DI\CacheExtension;
use Tests\Nettrine\Cache\Toolkit\NeonLoader;
use Tests\Nettrine\Cache\Toolkit\TestCase;

final class CacheExtensionTest extends TestCase
{

	public function testExplicitDriver(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addConfig(NeonLoader::load('
			nettrine.cache:
				driver: Doctrine\Common\Cache\VoidCache()
		'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		$this->assertInstanceOf(VoidCache::class, $container->getByType(Cache::class));
		$this->assertInstanceOf(VoidCache::class, $container->getService('nettrine.cache.driver'));
	}

	public function testAutoChooseFile(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => TEMP_PATH,
				],
			]);
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		$this->assertInstanceOf(PhpFileCache::class, $container->getByType(Cache::class));
		$this->assertInstanceOf(PhpFileCache::class, $container->getService('nettrine.cache.driver'));
	}

	public function testAutoChooseApcu(): void
	{
		if (!function_exists('apcu_exists')) {
			$this->markTestSkipped('Cannot test without apcu available');
		}

		$loader = new ContainerLoader(__DIR__ . '/../../tmp', true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		$this->assertInstanceOf(ApcuCache::class, $container->getByType(Cache::class));
		$this->assertInstanceOf(ApcuCache::class, $container->getService('nettrine.cache.driver'));
	}

	public function testNoneAvailable(): void
	{
		if (function_exists('apcu_exists')) {
			$this->markTestSkipped('Exception unreachable when apcu is available');
		}

		$this->expectException(InvalidStateException::class);
		$this->expectExceptionMessage('Unable to find an available cache driver, please provide one via \'nettrine.cache > driver\' configuration.');

		$loader = new ContainerLoader(__DIR__ . '/../../tmp', true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		new $class();
	}

}

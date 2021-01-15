<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Exception;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\InvalidStateException;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\Tests;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class CacheExtensionTest extends TestCase
{

	public function testExplicitDriver(): void
	{
		$loader = new ContainerLoader(Tests::TEMP_PATH, true);
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

		Assert::type(VoidCache::class, $container->getByType(Cache::class));
		Assert::type(VoidCache::class, $container->getService('nettrine.cache.driver'));
	}

	public function testAutoChooseFile(): void
	{
		$loader = new ContainerLoader(Tests::TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Tests::TEMP_PATH,
				],
			]);
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		Assert::type(PhpFileCache::class, $container->getByType(Cache::class));
		Assert::type(PhpFileCache::class, $container->getService('nettrine.cache.driver'));
	}

	/**
	 * @phpExtension apcu
	 */
	public function testAutoChooseApcu(): void
	{
		if (!function_exists('apcu_exists')) {
			Environment::skip('Exception unreachable when apcu is available');
		}

		$loader = new ContainerLoader(Tests::TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);

		Assert::type(ApcuCache::class, $container->getByType(Cache::class));
		Assert::type(ApcuCache::class, $container->getService('nettrine.cache.driver'));
	}

	public function testNoneAvailable(): void
	{
		if (function_exists('apcu_exists')) {
			Environment::skip('Exception unreachable when apcu is available');
		}

		Assert::type(new Exception(), new InvalidStateException());

		Assert::exception(function (): void {
			$loader = new ContainerLoader(Tests::TEMP_PATH, true);
			$class = $loader->load(static function (Compiler $compiler): void {
				$compiler->addExtension('nettrine.cache', new CacheExtension());
				$compiler->addDependencies([__FILE__]);
			}, __METHOD__);

			$container = new $class();
			Assert::true($container instanceof Container);
		}, InvalidStateException::class, 'Unable to find an available cache driver, please provide one via \'nettrine.cache > driver\' configuration.');
	}

}

(new CacheExtensionTest())->run();

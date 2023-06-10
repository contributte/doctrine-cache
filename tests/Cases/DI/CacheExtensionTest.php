<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Nette\DI\Compiler;
use Nette\InvalidStateException;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class CacheExtensionTest extends TestCase
{

	public function testExplicitDriver(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('nettrine.cache', new CacheExtension());
				$compiler->addConfig(Neonkit::load('
					nettrine.cache:
						driver: Doctrine\Common\Cache\VoidCache()
				'));
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		Assert::type(VoidCache::class, $container->getByType(Cache::class));
		Assert::type(VoidCache::class, $container->getService('nettrine.cache.driver'));
	}

	public function testAutoChooseFile(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('nettrine.cache', new CacheExtension());
				$compiler->addConfig([
					'parameters' => [
						'tempDir' => Environment::getTestDir(),
					],
				]);
				$compiler->addDependencies([__FILE__]);
			})
			->build();

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

		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('nettrine.cache', new CacheExtension());
				$compiler->addDependencies([__FILE__]);
			})
			->build();

		Assert::type(ApcuCache::class, $container->getByType(Cache::class));
		Assert::type(ApcuCache::class, $container->getService('nettrine.cache.driver'));
	}

	/**
	 * @phpExtension apcu
	 */
	public function testNoneAvailable(): void
	{
		if (function_exists('apcu_exists')) {
			Environment::skip('Exception unreachable when apcu is available');
		}

		Assert::exception(function (): void {
			ContainerBuilder::of()
				->withCompiler(function (Compiler $compiler): void {
					$compiler->addExtension('nettrine.cache', new CacheExtension());
					$compiler->addDependencies([__FILE__]);
				})
				->build();
		}, InvalidStateException::class, 'Unable to find an available cache driver, please provide one via \'nettrine.cache > driver\' configuration.');
	}

}

(new CacheExtensionTest())->run();

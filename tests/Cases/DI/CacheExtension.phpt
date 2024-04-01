<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Common\Cache\VoidCache;
use Nette\DI\Compiler;
use Nettrine\Cache\DI\CacheExtension;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension(debugMode: true));
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
				],
			]);
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	Assert::type(ArrayCache::class, $container->getByType(Cache::class));
	Assert::type(ArrayCache::class, $container->getService('nettrine.cache.driver'));

	// Use this checks after dropping 1.0 support
	//Assert::type(DoctrineProvider::class, $container->getByType(Cache::class));
	//Assert::type(ArrayAdapter::class, $container->getService('nettrine.cache.driver')->getPool());
});

Toolkit::test(function (): void {
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

	// Use this checks after dropping 1.0 support
	//Assert::type(DoctrineProvider::class, $container->getByType(Cache::class));
	//Assert::type(FilesystemAdapter::class, $container->getService('nettrine.cache.driver')->getPool());
});

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addConfig(Neonkit::load('
					nettrine.cache:
						driver: Doctrine\Common\Cache\Psr6\DoctrineProvider::wrap(Symfony\Component\Cache\Adapter\NullAdapter())
				'));
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	Assert::type(DoctrineProvider::class, $container->getByType(Cache::class));
	Assert::type(NullAdapter::class, $container->getService('nettrine.cache.driver')->getPool());
});

// Drop this test after dropping 1.0 support
Toolkit::test(function (): void {
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
});

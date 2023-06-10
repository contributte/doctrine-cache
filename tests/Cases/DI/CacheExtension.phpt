<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Nette\DI\Compiler;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

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
});

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

<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Nette\DI\Compiler;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

if (!function_exists('apcu_exists')) {
	Environment::skip('Autoselect driver unreachable when apcu is not available');
}

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('nettrine.cache', new CacheExtension());
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	Assert::type(ApcuCache::class, $container->getByType(Cache::class));
	Assert::type(ApcuCache::class, $container->getService('nettrine.cache.driver'));

	// Use this checks after dropping 1.0 support
	//Assert::type(DoctrineProvider::class, $container->getByType(Cache::class));
	//Assert::type(ApcuAdapter::class, $container->getService('nettrine.cache.driver')->getPool());
});

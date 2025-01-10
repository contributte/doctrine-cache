<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Nette\DI\Compiler;
use Nettrine\Cache\DI\CacheExtension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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

	Assert::type(DoctrineProvider::class, $container->getByType(Cache::class));
	Assert::type(FilesystemAdapter::class, $container->getService('nettrine.cache.driver')->getPool());
});

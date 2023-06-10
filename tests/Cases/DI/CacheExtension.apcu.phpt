<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Nette\DI\Compiler;
use Nette\InvalidStateException;
use Nettrine\Cache\DI\CacheExtension;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

if (function_exists('apcu_exists')) {
	Environment::skip('Exception unreachable when apcu is available');
}

Toolkit::test(function (): void {
	Assert::exception(function (): void {
		ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('nettrine.cache', new CacheExtension());
				$compiler->addDependencies([__FILE__]);
			})
			->build();
	}, InvalidStateException::class, 'Unable to find an available cache driver, please provide one via \'nettrine.cache > driver\' configuration.');
});

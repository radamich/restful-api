<?php

namespace {;

    use Movisio\RestfulApi\Application\Converters\IConverter;
    use Movisio\RestfulApi\DI\RestfulApiExtension;
    use Nette\Configurator;
    use Nette\DI\Compiler;
    use Nette\DI\CompilerExtension;
    use Nette\DI\ContainerBuilder;
    use Nette\DI\Definitions\Definition;
    use Nette\NotImplementedException;
    use Nette\Schema\Schema;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class RestfulApiExtensionTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactoryReturn() : void
        {
            $extension = new RestfulApiExtension();
            Assert::type(CompilerExtension::class, $extension);

            Assert::type(Schema::class, $extension->getConfigSchema());

            $mockCompiler = Mockery::mock(Compiler::class);
            $mockBuilder = Mockery::mock(ContainerBuilder::class);
            $mockDefinition = Mockery::mock(Definition::class);

            $mockCompiler->shouldReceive('getContainerBuilder')->andReturn($mockBuilder);
            $mockBuilder->shouldReceive('addDefinition')->andReturn($mockDefinition);
            $mockBuilder->shouldReceive('getDefinition')->andReturn($mockDefinition);
            $mockDefinition->shouldReceive('setFactory')->andReturn($mockDefinition);
            $mockDefinition->shouldReceive('setArguments')->andReturn($mockDefinition);
            $mockDefinition->shouldReceive('addSetup')->andReturn($mockDefinition);

            $extension->setCompiler($mockCompiler, 'test');
            $extension->setConfig([
                'convention' => null,
                'timeFormat' => null,
            ]);

            Assert::noError(function () use($extension) {
                $extension->loadConfiguration();
            });

            $extension->setConfig([
                'convention' => 'camelCase',
                'timeFormat' => null,
            ]);

            Assert::noError(function () use($extension) {
                $extension->loadConfiguration();
            });

            $mockCompiler->shouldReceive('addExtension');

            Assert::noError(function () use($extension, $mockCompiler) {
                $mockConfigurator = Mockery::mock(Configurator::class);
                $extension->install($mockConfigurator);
                current($mockConfigurator->onCompile)($mockConfigurator, $mockCompiler);
            });

            $extension->setConfig([
                'convention' => 'unknown_converter',
                'timeFormat' => null,
            ]);

            Assert::exception(function () use($extension) {
                $extension->loadConfiguration();
            }, NotImplementedException::class);

            $mockConverter = Mockery::mock(IConverter::class);
            $mockBuilder->shouldReceive('findByTag')->with('restful-api.converter')->andReturn([$mockConverter]);
            Assert::noError(function () use($extension) {
                $extension->beforeCompile();
            });
        }
    }

    (new RestfulApiExtensionTest)->run();
}



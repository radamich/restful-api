<?php

namespace {;

    use Movisio\RestfulApi\Application\Converters\IConverter;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class ResourceConverterTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testConverter() : void
        {
            $converter = new \Movisio\RestfulApi\Application\Converters\ResourceConverter();
            Assert::equal([], $converter->convertResource([]));
            Assert::equal([1], $converter->convertResource([1]));
            Assert::equal(['1'], $converter->convertResource(['1']));

            $mockConverter = Mockery::mock(IConverter::class);
            $mockConverter->shouldReceive('convertResource')->with(['test'])->andReturn(['set']);
            $converter->addConverter($mockConverter);
            Assert::equal(['set'], $converter->convertResource(['test']));
        }
    }

    (new ResourceConverterTest)->run();
}



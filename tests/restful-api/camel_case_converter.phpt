<?php

namespace {;

    use Drahak\Restful\Converters\CamelCaseConverter;
    use Movisio\RestfulApi\Application\Converters\IConverter;
    use Tester\Assert;
    use Symfony\Component\Console\Tester\CommandTester;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class CamelCaseConverterTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testConverter() : void
        {
            $converter = new \Movisio\RestfulApi\Application\Converters\CamelCaseConverter();
            Assert::type(IConverter::class, $converter);
            Assert::equal([], $converter->convertResource([]));
            Assert::equal([1], $converter->convertResource([1]));
            Assert::equal(['1'], $converter->convertResource(['1']));
            Assert::equal(['a'], $converter->convertResource(['a']));
            Assert::equal(['A'], $converter->convertResource(['A']));
            Assert::equal(['_'], $converter->convertResource(['_']));
            Assert::equal(['a_a'], $converter->convertResource(['a_a']));
            Assert::equal(['_1'], $converter->convertResource(['_1']));
            Assert::equal(['1' => 'test'], $converter->convertResource(['1' => 'test']));
            Assert::equal(['a' => 'test'], $converter->convertResource(['a' => 'test']));
            Assert::equal(['a' => 'test'], $converter->convertResource(['A' => 'test']));
            Assert::equal(['_' => 'test'], $converter->convertResource(['_' => 'test']));
            Assert::equal(['A' => 'test'], $converter->convertResource(['_a' => 'test']));
            Assert::equal(['_1' => 'test'], $converter->convertResource(['_1' => 'test']));
            Assert::equal(['a_1' => 'test'], $converter->convertResource(['a_1' => 'test']));
            Assert::equal(['a_1B' => 'test'], $converter->convertResource(['a_1_b' => 'test']));
            Assert::equal(['aB' => 'test'], $converter->convertResource(['a_b' => 'test']));
            Assert::equal(['phase_1' => 'test'], $converter->convertResource(['phase_1' => 'test']));
            Assert::equal(['phaseAbc' => 'test'], $converter->convertResource(['phase_abc' => 'test']));
            Assert::equal(['phaseAbc' => ['bC' => 'test']], $converter->convertResource(['phase_abc' => ['b_c' => 'test']]));
            Assert::equal(['phaseAbc' => ['bC' => 'test']], $converter->convertResource(['Phase_abc' => ['b_c' => 'test']]));
        }
    }

    (new CamelCaseConverterTest)->run();
}



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
    class DateTimeConverterTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testConverter() : void
        {
            $converter = new \Movisio\RestfulApi\Application\Converters\DateTimeConverter();
            Assert::type(IConverter::class, $converter);
            Assert::equal([], $converter->convertResource([]));
            Assert::equal([1], $converter->convertResource([1]));
            Assert::equal(['1'], $converter->convertResource(['1']));
            Assert::equal(['2011-01-03T00:00:00+01:00'], $converter->convertResource([new DateTime('2011-01-03')]));
            Assert::equal(['2011-01-03T13:22:45+01:00'], $converter->convertResource([new DateTime('2011-01-03 13:22:45')]));
            Assert::equal(['2011-01-03T13:22:45+01:00'], $converter->convertResource([new \Nette\Utils\DateTime('2011-01-03 13:22:45')]));
            Assert::equal(['2011-01-03T13:22:45+01:00'], $converter->convertResource([new DateTimeImmutable('2011-01-03 13:22:45')]));
            Assert::equal(['a_1_b' => 'test'], $converter->convertResource(['a_1_b' => 'test']));
            Assert::equal(['phase_abc' => ['b_c' => 'test']], $converter->convertResource(['phase_abc' => ['b_c' => 'test']]));
            Assert::equal(['phase_abc' => ['b_c' => '2011-01-03T13:22:45+01:00']], $converter->convertResource(['phase_abc' => ['b_c' => new DateTime('2011-01-03 13:22:45')]]));
            Assert::equal(['phase_abc' => ['b_c' => ['x' => '2011-01-03T13:22:45+01:00']]], $converter->convertResource(['phase_abc' => ['b_c' => new ArrayObject(['x' => new DateTimeImmutable('2011-01-03 13:22:45')])]]));


            $converter = new \Movisio\RestfulApi\Application\Converters\DateTimeConverter('H:i');
            Assert::equal(['1'], $converter->convertResource(['1']));
            Assert::equal(['00:00'], $converter->convertResource([new DateTime('2011-01-03')]));
            Assert::equal(['13:22'], $converter->convertResource([new DateTimeImmutable('2011-01-03 13:22:45')]));
        }
    }

    (new DateTimeConverterTest)->run();
}



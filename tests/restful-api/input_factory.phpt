<?php

namespace {;

    use Movisio\RestfulApi\BadRequestException;
    use Movisio\RestfulApi\Http\IInput;
    use Movisio\RestfulApi\Http\InputFactory;
    use Nette\Http\IRequest;
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
    class InputFactoryTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactoryReturn() : void
        {
            $mockIRequest = \Mockery::mock(IRequest::class);
            $mockIRequest->shouldReceive('getPost');
            $mockIRequest->shouldReceive('getQuery');
            $mockIRequest->shouldReceive('getRawBody');
            $mockIRequest->shouldReceive('getHeader');

            $factory = new InputFactory($mockIRequest);
            Assert::type(IInput::class, $factory->create());
        }

        public function testFactoryJson() : void
        {
            $mockIRequest = \Mockery::mock(IRequest::class);
            $mockIRequest->shouldReceive('getPost');
            $mockIRequest->shouldReceive('getQuery');
            $mockIRequest->shouldReceive('getRawBody')->andReturn('test', 'test', '["abc"]', '{"0": "abc"}', '["abc", {"x": 1}]');
            $mockIRequest->shouldReceive('getHeader')->andReturn('abc', 'application/json');

            $factory = new InputFactory($mockIRequest);
            Assert::exception(function () use ($factory) {
                $input = $factory->create();
            }, BadRequestException::class, '# content type #');
            Assert::exception(function () use ($factory) {
                $input = $factory->create();
            }, BadRequestException::class, 'Syntax error');
            $input = $factory->create();
            Assert::equal(['abc'], $input->getData());
            $input = $factory->create();
            Assert::equal(['abc'], $input->getData());
            $input = $factory->create();
            Assert::equal(['abc', ['x' => 1]], $input->getData());
        }

        public function testFactoryQuery() : void
        {
            $mockIRequest = \Mockery::mock(IRequest::class);
            $mockIRequest->shouldReceive('getPost');
            $mockIRequest->shouldReceive('getQuery');
            $mockIRequest->shouldReceive('getRawBody')->andReturn('abc=xy', 'abc[]=xy');
            $mockIRequest->shouldReceive('getHeader')->andReturn('application/x-www-form-urlencoded');

            $factory = new InputFactory($mockIRequest);
            $input = $factory->create();
            Assert::equal(['abc' => 'xy'], $input->getData());
            $input = $factory->create();
            Assert::equal(['abc' => ['xy']], $input->getData());
        }
    }

    (new InputFactoryTest)->run();
}



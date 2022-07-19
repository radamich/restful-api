<?php

namespace {;

    use Movisio\RestfulApi\Application\Responses\ErrorResponse;
    use Nette\Application\Response;
    use Nette\Http\IRequest;
    use Nette\Http\IResponse;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class ErrorResponseTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testInput() : void
        {
            $mockResponse = Mockery::mock(Response::class);

            $eResponse = new ErrorResponse($mockResponse);
            Assert::equal(500, $eResponse->getCode());

            $eResponse = new ErrorResponse($mockResponse, 301);
            Assert::equal(301, $eResponse->getCode());

            $mockHttpRequest = Mockery::mock(IRequest::class);
            $mockHttpResponse = Mockery::mock(IResponse::class);
            $mockHttpResponse->shouldReceive('setCode')->with(301);
            $mockResponse->shouldReceive('send')->with($mockHttpRequest, $mockHttpResponse);

            $eResponse->send($mockHttpRequest, $mockHttpResponse);
        }
    }

    (new ErrorResponseTest)->run();
}



<?php

namespace {;

    use Movisio\RestfulApi\Http\ApiRequestFactory;
    use Nette\Http\IRequest;
    use Nette\Http\Request;
    use Nette\Http\RequestFactory;
    use Nette\Http\UrlScript;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class ApiRequestFactoryTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactory() : void
        {
            $mockFactory = \Mockery::mock(RequestFactory::class);
            $mockRequest = Mockery::mock(Request::class);

            $mockFactory->shouldReceive('fromGlobals')->andReturn($mockRequest);
            $mockRequest->shouldReceive('getUrl')->andReturn(new UrlScript('/'));
            $mockRequest->shouldReceive('getQuery')->andReturn('', 'b', 'b');
            $mockRequest->shouldReceive('getPost')->andReturn(null);
            $mockRequest->shouldReceive('getCookies')->andReturn([]);
            $mockRequest->shouldReceive('getFiles')->andReturn([]);
            $mockRequest->shouldReceive('getHeaders')->andReturn([]);
            $mockRequest->shouldReceive('getMethod')->andReturn(IRequest::GET, IRequest::POST);
            $mockRequest->shouldReceive('getHeader')->andReturn(null, 'a', null);
            $mockRequest->shouldReceive('getRemoteAddress')->andReturn(null);

            $factory = new ApiRequestFactory($mockFactory);
            Assert::type(IRequest::class, $factory->createHttpRequest());
            Assert::type(IRequest::class, $factory->createHttpRequest());
            Assert::type(IRequest::class, $factory->createHttpRequest());
        }
    }

    (new ApiRequestFactoryTest)->run();
}



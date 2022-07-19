<?php

namespace {;

    use Movisio\RestfulApi\Application\Routes\ResourceRoute;
    use Nette\Http\Request;
    use Nette\Http\UrlScript;
    use Nette\Routing\Router;
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
    class ResourceRouteTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactoryReturn() : void
        {
            $route = new ResourceRoute('');
            Assert::type(Router::class, $route);

            Assert::equal(ResourceRoute::GET, $route->getFlags());
            Assert::true($route->isMethod(ResourceRoute::GET));
            Assert::false($route->isMethod(ResourceRoute::POST));

            $route = new ResourceRoute('', [], ResourceRoute::POST);

            Assert::equal(ResourceRoute::POST, $route->getFlags());
            Assert::false($route->isMethod(ResourceRoute::GET));
            Assert::true($route->isMethod(ResourceRoute::POST));

            $mockRequest = Mockery::mock(Request::class);
            $mockRequest->shouldReceive('getMethod')->andReturn(Request::PATCH, 'xyz');
            Assert::equal(ResourceRoute::PATCH,$route->getMethod($mockRequest));
            Assert::null($route->getMethod($mockRequest));

            $route = new ResourceRoute('/', 'module:presenter:action', ResourceRoute::POST);
            $mockRequest = Mockery::mock(Request::class);
            $mockRequest->shouldReceive('getUrl')->andReturn(new UrlScript('/'), new UrlScript('/'), new UrlScript('/abc'));
            $mockRequest->shouldReceive('getQuery')->andReturn([]);
            $mockRequest->shouldReceive('getMethod')->andReturn(Request::HEAD, Request::POST);
            Assert::null($route->match($mockRequest));
            Assert::equal(['presenter'=>'module:presenter', 'action'=>'action'], $route->match($mockRequest));
            Assert::null($route->match($mockRequest));
        }
    }

    (new ResourceRouteTest)->run();
}



<?php

namespace Nette\Application {
    class Request {}
}

namespace {;

    use Movisio\RestfulApi\Application\Converters\ResourceConverter;
    use Movisio\RestfulApi\Application\Responses\ErrorResponse;
    use Movisio\RestfulApi\Application\UI\ResourcePresenter;
    use Nette\Application\IPresenter;
    use Nette\Application\Request;
    use Nette\Application\Responses\JsonResponse;
    use Nette\NotImplementedException;
    use Nette\Schema\ValidationException;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    function throwDebug(\Throwable $e) {
    }

    /**
     * @testCase
     */
    class ResourcePresenterTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactoryReturn() : void
        {
            $presenter = new ResourcePresenter();
            Assert::type(IPresenter::class, $presenter);

            $mockContext = \Mockery::mock(Nette\DI\Container::class);
            $mockPresenterFactory = \Mockery::mock(Nette\Application\IPresenterFactory::class);
            $mockRouter = \Mockery::mock(Nette\Routing\Router::class);

            $mockHttpRequest = \Mockery::mock(Nette\Http\IRequest::class);
            $mockHttpRequest->shouldReceive('isAjax');
            $mockHttpRequest->shouldReceive('isMethod');
            $mockHttpRequest->shouldReceive('getHeader');

            $mockHttpResponse = \Mockery::mock(Nette\Http\IResponse::class);
            $mockHttpResponse->shouldReceive('isSent');
            $mockHttpResponse->shouldReceive('addHeader');
            $mockHttpResponse->shouldReceive('setHeader');
            $mockHttpResponse->shouldReceive('getCode')->andReturn(200);
            $mockHttpResponse->shouldReceive('setCode');

            $mockSessionSection = \Mockery::mock('overload:' . Nette\Http\SessionSection::class);
            $mockSessionSection->hash = 'testHash';

            $mockSession = \Mockery::mock('overload:' . Nette\Http\Session::class);
            $mockSession->shouldReceive('getSection')->andReturn($mockSessionSection);

            $mockUser = \Mockery::mock(Nette\Security\User::class);

            $mockAuthenticationContext = \Mockery::mock(Drahak\Restful\Security\AuthenticationContext::class);
            $mockAuthenticationContext->shouldReceive('authenticate');

            $mockIInput = \Mockery::mock(Drahak\Restful\Http\Input::class);
            $mockIInput->shouldReceive('isValid')->andReturn(true);
            //$mockIInput->shouldReceive('validate');

            $mockInputFactory = \Mockery::mock(Drahak\Restful\Http\InputFactory::class);
            $mockInputFactory->shouldReceive('create')->andReturn($mockIInput);

            $mockResourceConverter = \Mockery::mock(ResourceConverter::class);
            $mockResourceConverter->shouldReceive('convertResource')->andReturn([]);
            $mockRequestFilter = \Mockery::mock(\Movisio\RestfulApi\Utils\RequestFilter::class);

            $mockRequest = Mockery::mock(Request::class);
            $mockRequest->shouldReceive('getPresenterName')->andReturn('test');
            $mockRequest->shouldReceive('getParameters')->andReturn([]);
            $mockRequest->shouldReceive('getPost')->andReturn(null);
            $mockRequest->shouldReceive('getMethod')->andReturn(null);

            $presenter->injectPrimary($mockContext, $mockPresenterFactory, $mockRouter, $mockHttpRequest, $mockHttpResponse, $mockSession, $mockUser);
            $presenter->injectDrahakRestful($mockAuthenticationContext, $mockInputFactory, $mockRequestFilter, $mockResourceConverter);
            $presenter->run($mockRequest);

            $presenter = new class extends ResourcePresenter {
                public function validateDefault() : void
                {
                    throw new ValidationException('abc');
                }
            };
            $presenter->injectPrimary($mockContext, $mockPresenterFactory, $mockRouter, $mockHttpRequest, $mockHttpResponse, $mockSession, $mockUser);
            $presenter->injectDrahakRestful($mockAuthenticationContext, $mockInputFactory, $mockRequestFilter, $mockResourceConverter);
            Assert::type(ErrorResponse::class,$presenter->run($mockRequest));

            $presenter = new class extends ResourcePresenter {
                public bool $validated = false;
                public function validateDefault() : void
                {
                    $this->validated = true;
                }
            };
            $presenter->injectPrimary($mockContext, $mockPresenterFactory, $mockRouter, $mockHttpRequest, $mockHttpResponse, $mockSession, $mockUser);
            $presenter->injectDrahakRestful($mockAuthenticationContext, $mockInputFactory, $mockRequestFilter, $mockResourceConverter);
            Assert::type(JsonResponse::class, $presenter->run($mockRequest));
            Assert::true($presenter->validated);

            $presenter = new class extends ResourcePresenter {
                public bool $validated = false;
                public function validateDefault() : void
                {
                    $this->validated = true;
                }
            };
            $mockIInput = \Mockery::mock(Drahak\Restful\Http\Input::class);
            $mockIInput->shouldReceive('isValid')->andReturn(false);
            $mockIInput->shouldReceive('validate')->andReturn(['testError']);
            $mockInputFactory = \Mockery::mock(Drahak\Restful\Http\InputFactory::class);
            $mockInputFactory->shouldReceive('create')->andReturn($mockIInput);
            
            $presenter->injectPrimary($mockContext, $mockPresenterFactory, $mockRouter, $mockHttpRequest, $mockHttpResponse, $mockSession, $mockUser);
            $presenter->injectDrahakRestful($mockAuthenticationContext, $mockInputFactory, $mockRequestFilter, $mockResourceConverter);
            $response = $presenter->run($mockRequest);

            Assert::type(ErrorResponse::class, $response);
            Assert::true($presenter->validated);

        }
    }

    (new ResourcePresenterTest)->run();
}



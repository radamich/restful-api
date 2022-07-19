<?php

namespace {

    use Drahak\Restful\DI\RestfulExtension;
    use Movisio\RestfulApi\Application\Responses\ErrorResponse;
    use Movisio\RestfulApi\Application\Routes\ResourceRoute;
    use Movisio\RestfulApi\Application\UI\ResourcePresenter;
    use Movisio\RestfulApi\BadRequestException;
    use Movisio\RestfulApi\DI\RestfulApiExtension;
    use Movisio\RestfulApi\Http\ApiRequestFactory;
    use Movisio\RestfulApi\Security\AuthenticationException;
    use Movisio\RestfulApi\Security\Process\AuthenticationProcess;
    use Movisio\RestfulApi\Security\Process\NullAuthentication;
    use Movisio\RestfulApi\Security\RequestTimeoutException;
    use Nette\Application\Response;
    use Nette\Http\RequestFactory;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class CompatibilityTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testRelations() : void
        {
            $mockRequestFactory = Mockery::mock(RequestFactory::class);
            $mockResponse = Mockery::mock(Response::class);
            Assert::type(ApiRequestFactory::class, new \Drahak\Restful\Http\ApiRequestFactory($mockRequestFactory));
            Assert::type(RestfulApiExtension::class, new RestfulExtension());
            Assert::type(ResourceRoute::class, new \Drahak\Restful\Application\Routes\ResourceRoute(''));
            Assert::type(ResourcePresenter::class, new \Drahak\Restful\Application\UI\ResourcePresenter());
            Assert::type(BadRequestException::class, new \Drahak\Restful\Application\BadRequestException());
            Assert::type(ErrorResponse::class, new \Drahak\Restful\Application\Responses\ErrorResponse($mockResponse));
            Assert::type(NullAuthentication::class, new \Drahak\Restful\Security\Process\NullAuthentication());
            Assert::type(AuthenticationProcess::class, new \Drahak\Restful\Security\Process\NullAuthentication());
            $reflection = new ReflectionClass(\Drahak\Restful\Security\Process\AuthenticationProcess::class);
            Assert::true($reflection->isSubclassOf(AuthenticationProcess::class));
            Assert::type(AuthenticationException::class, new \Drahak\Restful\Security\AuthenticationException());
            Assert::type(RequestTimeoutException::class, new \Drahak\Restful\Security\RequestTimeoutException());
        }
    }

    (new CompatibilityTest)->run();
}



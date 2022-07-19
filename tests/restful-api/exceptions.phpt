<?php

namespace {

    use Movisio\RestfulApi\BadRequestException;
    use Movisio\RestfulApi\Security\AuthenticationException;
    use Movisio\RestfulApi\Security\ForbiddenRequestException;
    use Movisio\RestfulApi\Security\RequestTimeoutException;
    use Movisio\RestfulApi\Security\SecurityException;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class BadRequestExceptionTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testBadRequestException() : void
        {
            $exception = BadRequestException::unauthorized();
            Assert::type(BadRequestException::class, $exception);
            Assert::equal(401, $exception->getCode());

            $exception = BadRequestException::forbidden();
            Assert::equal(403, $exception->getCode());

            $exception = BadRequestException::notFound();
            Assert::equal(404, $exception->getCode());


            $exception = BadRequestException::methodNotSupported();
            Assert::equal(405, $exception->getCode());

            $exception = BadRequestException::unsupportedMediaType();
            Assert::equal(415, $exception->getCode());

            $exception = BadRequestException::unprocessableEntity(['test_error']);
            Assert::equal(422, $exception->getCode());
            Assert::equal(['test_error'], $exception->errors);

            $exception = BadRequestException::gone();
            Assert::equal(410, $exception->getCode());

            $exception = BadRequestException::tooManyRequests();
            Assert::equal(429, $exception->getCode());
        }

        public function testOther() : void
        {
            Assert::type(SecurityException::class, new ForbiddenRequestException());
            Assert::type(SecurityException::class, new AuthenticationException());
            Assert::type(SecurityException::class, new RequestTimeoutException());
        }
    }

    (new BadRequestExceptionTest)->run();
}



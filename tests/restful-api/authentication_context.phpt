<?php

namespace {;

    use Movisio\RestfulApi\Http\IInput;
    use Movisio\RestfulApi\Security\AuthenticationContext;
    use Movisio\RestfulApi\Security\Process\AuthenticationProcess;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class AuthenticationContextTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactoryReturn() : void
        {
            $auth = new AuthenticationContext();

            $mockInput = Mockery::mock(IInput::class);
            $mockProcess = Mockery::mock(AuthenticationProcess::class);
            $mockProcess->shouldReceive('authenticate')->andReturn(false, true);

            Assert::type(AuthenticationContext::class, $auth->setAuthProcess($mockProcess));
            Assert::false($auth->authenticate($mockInput));
            Assert::true($auth->authenticate($mockInput));
        }
    }

    (new AuthenticationContextTest)->run();
}



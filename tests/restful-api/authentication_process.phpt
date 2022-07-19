<?php

namespace {;

    use Movisio\RestfulApi\Http\IInput;
    use Movisio\RestfulApi\Security\Process\AuthenticationProcess;
    use Movisio\RestfulApi\Security\Process\NullAuthentication;
    use Tester\Assert;

    // load Tester library
    require __DIR__ . '/../../vendor/autoload.php';
//    require __DIR__ . '/../../../../vendor/autoload.php';

    // set up PHP behaviour and some Tester properties
    Tester\Environment::setup();

    /**
     * @testCase
     */
    class AuthenticationProcessTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testFactoryReturn() : void
        {
            $auth = new NullAuthentication();
            Assert::type(AuthenticationProcess::class, $auth);

            $mockInput = Mockery::mock(IInput::class);
            $auth->authenticate($mockInput);
        }
    }

    (new AuthenticationProcessTest)->run();
}



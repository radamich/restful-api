<?php

namespace {;

    use Movisio\RestfulApi\BadRequestException;
    use Movisio\RestfulApi\Http\IInput;
    use Movisio\RestfulApi\Http\Input;
    use Nette\Http\IRequest;
    use Nette\MemberAccessException;
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
    class InputTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testInput() : void
        {
            $input = new Input();
            Assert::type(IInput::class, $input);
            Assert::equal([], $input->getData());
            $input->setData(['key' => 'test']);
            Assert::equal(['key' => 'test'], $input->getData());
            Assert::equal('test', $input->key);
            Assert::exception(function () use($input) {
                $input->nonexistentkey;
            }, MemberAccessException::class);
            Assert::exception(function () use($input) {
                $input->key = 5;
            }, MemberAccessException::class);
            Assert::false(isset($input->nonexistentkey));
            Assert::type(ArrayIterator::class, $input->getIterator());
            Assert::equal(['key' => 'test'], iterator_to_array($input->getIterator()));
        }
    }

    (new InputTest)->run();
}



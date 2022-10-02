<?php

namespace {;

    use Drahak\Restful\Validation\IValidator;
    use Movisio\RestfulApi\BadRequestException;
    use Movisio\RestfulApi\Http\IInput;
    use Movisio\RestfulApi\Http\Input;
    use Nette\Http\IRequest;
    use Nette\InvalidStateException;
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

        public function testValidators() : void
        {
            $input = new Input();
            Assert::same($input, $input->field(''));
            Assert::exception(function () use($input) {
                $input->addRule('x');
            }, InvalidStateException::class);
            $input->field('xyz');
            Assert::same($input, $input->addRule('string')->addRule('required'));
            Assert::false($input->isValid());
            $input->setData(['xyz' => 'test']);
            $input->field('inttest');
            $input->addRule('int');
            $input->field('anytest');
            $input->addRule('required');
            $input->field('email');
            $input
                ->addRule('email')
                ->addRule('min_length', null, 9)
                ->addRule('max_length', null, 12);
            Assert::false($input->isValid());
            $input->setData(['xyz'=> 'a', 'anytest' => 1.0, 'inttest' => 1, 'email' => 'aaaaaa@b.cz']);
            Assert::equal([], $input->validate());
            $input->addRule('requiredd');
            Assert::exception(function () use($input) {
                $input->isValid();
            }, InvalidStateException::class);

            $input = new Input();
            $input->field('abc')->addRule(IValidator::INTEGER)->addRule(IValidator::STRING);
            Assert::exception(function () use($input) {
                $input->isValid();
            }, InvalidStateException::class);

        }
    }

    (new InputTest)->run();
}



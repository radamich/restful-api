<?php

namespace {;

    use Movisio\RestfulApi\InvalidStateException;
    use Movisio\RestfulApi\Utils\RequestFilter;
    use Nette\Http\IRequest;
    use Nette\Utils\Paginator;
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
    class RequestFilterTest extends Tester\TestCase {

        public function tearDown() : void
        {
            // steps to execute after each test iteration
            \Mockery::close();
        }

        public function testRequestFilter() : void
        {
            $mockIRequest = Mockery::mock(IRequest::class);
            $mockIRequest->shouldReceive('getQuery')->with('fields')->andReturn(null, ['a'], 'b', 'c,d,,,');
            $mockIRequest->shouldReceive('getQuery')->with('sort')->andReturn(null, 'a', '-b', '-c,d,,,');
            $mockIRequest->shouldReceive('getQuery')->with('q')->andReturn(null, 'a', '-b', '-c,d,,,');

            $filter = new RequestFilter($mockIRequest);
            Assert::equal([], $filter->getFieldList());
            Assert::equal([], $filter->getSortList());
            Assert::equal(null, $filter->getSearchQuery());
            $filter = new RequestFilter($mockIRequest);
            Assert::equal(['a'], $filter->getFieldList());
            Assert::equal(['a' => 'ASC'], $filter->getSortList());
            Assert::equal('a', $filter->getSearchQuery());
            $filter = new RequestFilter($mockIRequest);
            Assert::equal(['b'], $filter->getFieldList());
            Assert::equal(['b' => 'DESC'], $filter->getSortList());
            Assert::equal('-b', $filter->getSearchQuery());
            $filter = new RequestFilter($mockIRequest);
            Assert::equal(['c', 'd'], $filter->getFieldList());
            Assert::equal(['c' => 'DESC', 'd' => 'ASC'], $filter->getSortList());
            Assert::equal('-c,d,,,', $filter->getSearchQuery());

            $mockIRequest = Mockery::mock(IRequest::class);
            $mockIRequest->shouldReceive('getQuery')->with('offset')->andReturn(null, 112);
            $mockIRequest->shouldReceive('getQuery')->with('limit')->andReturn(null, 0,5);
            $filter = new RequestFilter($mockIRequest);
            Assert::exception(function () use($filter) {
                $filter->getPaginator();
            },InvalidStateException::class);
            Assert::exception(function () use($filter) {
                $filter->getPaginator();
            },InvalidStateException::class);
            $paginator = $filter->getPaginator();
            Assert::type(Paginator::class, $paginator);
            var_dump($paginator);
            Assert::equal(5, $paginator->getItemsPerPage());
            Assert::equal(23, $paginator->getPage());
        }
    }

    (new RequestFilterTest)->run();
}



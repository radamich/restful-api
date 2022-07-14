<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use ArrayIterator;
use IteratorAggregate;

/**
 * Request Input parser
 * @property array $data
 */
class Input implements IteratorAggregate, IInput
{
    /** @var array */
    private $data;
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /******************** IInput ********************/

    /**
     * Get parsed input data
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Set input data
     * @param array $data
     * @return Input
     */
    public function setData(array $data) : Input
    {
        $this->data = $data;
        return $this;
    }

    /******************** Magic methods ********************/

    /**
     * @param string $name
     * @return mixed
     *
     * @throws \Exception|\Nette\MemberAccessException
     */
    public function &__get(string $name)
    {
        $data = $this->getData();
        if (array_key_exists($name, $data)) {
            return $data[$name];
        }
        throw new \Nette\MemberAccessException(
            'Cannot read an undeclared property ' . static::class . '::$' . $name . '.'
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name) : bool
    {
        $data = $this->getData();
        return array_key_exists($name, $data);
    }

    /******************** Iterator aggregate interface ********************/

    /**
     * Get input data iterator
     * @return ArrayIterator
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->getData());
    }
}

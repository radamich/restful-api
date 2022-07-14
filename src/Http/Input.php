<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use ArrayIterator;
use IteratorAggregate;
use Movisio\RestfulApi\Validation\IDataProvider;
use Movisio\RestfulApi\Validation\IField;
use Movisio\RestfulApi\Validation\IValidationScope;
use Movisio\RestfulApi\Validation\IValidationScopeFactory;

/**
 * Request Input parser
 * @property array $data
 */
class Input implements IteratorAggregate, IInput, IDataProvider
{
    /** @var array */
    private $data;

    /** @var IValidationScope */
    private $validationScope;

    /** @var IValidationScopeFactory */
    private $validationScopeFactory;

    /**
     * @param IValidationScopeFactory $validationScopeFactory
     * @param array $data
     */
    public function __construct(IValidationScopeFactory $validationScopeFactory, array $data = [])
    {
        $this->data = $data;
        $this->validationScopeFactory = $validationScopeFactory;
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

    /******************** Validation data provider interface ********************/

    /**
     * Get validation field
     * @param string $name
     * @return IField
     */
    public function field(string $name) : IField
    {
        return $this->getValidationScope()->field($name);
    }

    /**
     * Validate input data
     * @return array
     */
    public function validate() : array
    {
        return $this->getValidationScope()->validate($this->getData());
    }

    /**
     * Is input valid
     * @return bool
     */
    public function isValid() : bool
    {
        return !$this->validate();
    }

    /**
     * Get validation scope
     * @return IValidationScope
     */
    public function getValidationScope() : IValidationScope
    {
        if (!$this->validationScope) {
            $this->validationScope = $this->validationScopeFactory->create();
        }
        return $this->validationScope;
    }
}

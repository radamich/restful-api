<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use ArrayIterator;
use IteratorAggregate;
use Movisio\RestfulApi\Validation\ValidatorAttribute;
use Movisio\RestfulApi\Validation\ValidatorType;
use Nette\InvalidStateException;
use Nette\Schema\Elements\Type;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\ValidationException;
use Nette\SmartObject;

/**
 * Request Input parser
 * @property array $data
 */
class Input implements IteratorAggregate, IInput
{
    use SmartObject;

    /** @var array */
    private array $data;
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
     */
    public function &__get(string $name) : mixed
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

    /*********** Validator integration ******************/

    /** @var string|null context selector for backward compatible adding of rules  */
    private ?string $currentField = null;

    /** @var array sets of rules indexed by fields */
    private array $fieldsRules = [];

    /**
     * context selector to allow addRule() chaining
     * @param string $selectField
     * @return $this
     */
    public function field(string $selectField) : self
    {
        $this->currentField = $selectField;
        return $this;
    }

    /**
     * add validation rule to a field selected by the most recent call to field()
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @param string $rule
     * @param string|null $message
     * @param mixed|null $parameter
     * @return $this
     */
    public function addRule(string $rule, string $message = null, mixed $parameter = null) : self
    {
        if (!$this->currentField) {
            throw new InvalidStateException("addRule() called without field selection");
        }
        if (!isset($this->fieldsRules[$this->currentField])) {
            $this->fieldsRules[$this->currentField] = [];
        }
        $this->fieldsRules[$this->currentField][] = [
            'rule' => $rule,
            //'msg' => $message, // not available with Nette/Schema atm
            'parameter' => $parameter,
        ];
        return $this;
    }

    /**
     * Build the Nette\Schema validation structures
     * @return Schema
     */
    private function getValidationSchema() : Schema
    {
        $fieldSchemas = [];
        foreach ($this->fieldsRules as $field => $rules) {
            $sortedRules = $this->preprocessRules($rules);
            $type = $this->getTypeSchema($sortedRules['type']);
            $schema = $this->applyAtrributes($type, $sortedRules['attributes']);
            $fieldSchemas[$field] = $schema;
        }
        $arraySchema = Expect::structure($fieldSchemas)->otherItems()->castTo('array');
        return $arraySchema;
    }

    /**
     * Create Schema::type instance by type enum
     * @param ValidatorType $type
     * @return Type
     */
    private function getTypeSchema(ValidatorType $type) : Type
    {
        if ($type === ValidatorType::ANY) {
            return Expect::mixed();
        }
        $typeSchema = Expect::type($type->value);
        switch ($type) {
            case ValidatorType::INT:
            case ValidatorType::INTEGER:
                $typeSchema->before(fn($v) => intval($v));
                break;
        }
        return $typeSchema;
    }

    /**
     * Apply set of attributes to given Schema type
     * @param Type $type
     * @param array $attributes
     * @return Schema
     */
    private function applyAtrributes(Type $type, array $attributes) : Schema
    {
        foreach ($attributes as $attribute) {
            switch ($attribute['attribute']) {
                case ValidatorAttribute::REQUIRED:
                    $type->required($attribute['parameter'] ?? true);
                    break;
                case ValidatorAttribute::MAX_LENGTH:
                    $type->max($attribute['parameter']);
                    break;
                case ValidatorAttribute::MIN_LENGTH:
                    $type->min($attribute['parameter']);
                    break;
            }
        }
        return $type;
    }

    /**
     * Sort and filter rules to groups (type, attributes)
     * @param array $rules
     * @return array
     */
    private function preprocessRules(array $rules) : array
    {
        $type = null;
        //$typeMessage = '';
        $typeCount = 0;
        $attributes = [];
        $toProcess = count($rules);
        foreach ($rules as $rule) {
            $typeTest = ValidatorType::tryFrom($rule['rule']);
            if ($typeTest) {
                //$typeMessage = $rule['msg'];
                $type = $typeTest;
                $typeCount++;
                $toProcess--;
                continue;
            }
            $attr = ValidatorAttribute::tryFrom($rule['rule']);
            if ($attr) {
                $attribute = [
                    'attribute' => $attr,
                    //'errorMessage' => $rule['msg'],
                    'parameter' => $rule['parameter'],
                ];
                $attributes[] = $attribute;
                $toProcess--;
                continue;
            }
        }
        if ($toProcess > 0) {
            throw new InvalidStateException('Rules contain some unknown definition');
        }
        if ($typeCount === 0) {
            $type = ValidatorType::ANY;
        }
        if ($typeCount > 1) {
            throw new InvalidStateException('Multiple types were defined');
        }
        return [
            'type' => $type,
            //'typeErrorMessage' => $typeMessage,
            'attributes' => $attributes,
        ];
    }

    /**
     * Validate request data
     * @return array empty on valid or list of error messages
     */
    public function validate() : array
    {
        $errors = [];
        $schema = $this->getValidationSchema();
        $processor = new Processor();
        try {
            $res = $processor->process($schema, $this->getData());
            $this->data = $res;
            $processor->process($schema, $this->getData());
        } catch (ValidationException $e) {
            $errors = $e->getMessages();
        }
        return $errors;
    }

    /**
     * Check request data validity
     * @return bool
     */
    public function isValid() : bool
    {
        return empty($this->validate());
    }
}

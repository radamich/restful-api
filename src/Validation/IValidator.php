<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Validation;

interface IValidator
{
    /* attributes */
    public const REQUIRED = 'required'; //ValidatorAttribute::REQUIRED
    public const MAX_LENGTH = 'max_length'; //ValidatorAttribute::MAX_LENGTH
    public const MIN_LENGTH = 'min_length'; //ValidatorAttribute::MIN_LENGTH

    /* types */
    public const ARRAY = 'array'; //ValidatorType::ARRAY
    public const BOOL = 'bool'; //ValidatorType::BOOL
    public const BOOLEAN = 'boolean'; //ValidatorType::BOOLEAN
    public const FLOAT = 'float'; //ValidatorType::FLOAT
    public const INT = 'int'; //ValidatorType::INT
    public const INTEGER = 'integer'; //ValidatorType::INTEGER
    public const NULL = 'null'; //ValidatorType::NULL
    public const OBJECT = 'object'; //ValidatorType::OBJECT
    public const RESOURCE = 'resource'; //ValidatorType::RESOURCE
    public const SCALAR = 'scalar'; //ValidatorType::SCALAR
    public const STRING = 'string'; //ValidatorType::STRING

    public const CALLABLE = 'callable'; //ValidatorType::CALLABLE
    public const ITERABLE = 'iterable'; //ValidatorType::ITERABLE
    public const LIST = 'list'; //ValidatorType::LIST
    public const MIXED = 'mixed'; //ValidatorType::MIXED
    public const NONE = 'none'; //ValidatorType::NONE
    public const NUMBER = 'number'; //ValidatorType::NUMBER
    public const NUMERIC = 'numeric'; //ValidatorType::NUMERIC
    public const NUMERICINT = 'numericint'; //ValidatorType::NUMERICINT

    public const EMAIL = 'email'; //ValidatorType::EMAIL
    public const IDENTIFIER = 'identifier'; //ValidatorType::IDENTIFIER
    public const URI = 'uri'; //ValidatorType::URI
    public const URL = 'url'; //ValidatorType::URL
    public const PHONE = 'string'; //ValidatorType::STRING
}

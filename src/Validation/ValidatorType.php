<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Validation;

enum ValidatorType : string
{
    case ANY = 'any';

    case ARRAY = 'array';
    case BOOL = 'bool';
    case BOOLEAN = 'boolean';
    case FLOAT = 'float';
    case INT = 'int';
    case INTEGER = 'integer';
    case NULL = 'null';
    case OBJECT = 'object';
    case RESOURCE = 'resource';
    case SCALAR = 'scalar';
    case STRING = 'string';

    case CALLABLE = 'callable';
    case ITERABLE = 'iterable';
    case LIST = 'list';
    case MIXED = 'mixed';
    case NONE = 'none';
    case NUMBER = 'number';
    case NUMERIC = 'numeric';
    case NUMERICINT = 'numericint';

    case EMAIL = 'email';
    case IDENTIFIER = 'identifier';
    case URI = 'uri';
    case URL = 'url';
}

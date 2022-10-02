<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Validation;

enum ValidatorAttribute : string
{
    case REQUIRED = "required";
    case MAX_LENGTH = "max_length";
    case MIN_LENGTH = "min_length";
}

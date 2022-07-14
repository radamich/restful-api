<?php
declare(strict_types = 1);

namespace Drahak\Restful;

/**
 * IResource determines REST service result set
 */
interface IResource
{
    /** Result types */
    public const XML = 'application/xml';
    public const JSON = 'application/json';
    public const JSONP = 'application/javascript';
    public const QUERY = 'application/x-www-form-urlencoded';
    public const DATA_URL = 'application/x-data-url';
    public const FILE = 'application/octet-stream';
    public const FORM = 'multipart/form-data';
    public const NULL = null;
}

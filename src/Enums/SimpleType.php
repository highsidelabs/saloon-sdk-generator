<?php

namespace Crescat\SaloonSdkGenerator\Enums;

enum SimpleType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case ARRAY = 'array';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case NULL = 'null';
}

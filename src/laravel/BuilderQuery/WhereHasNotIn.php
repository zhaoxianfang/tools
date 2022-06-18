<?php

namespace zxf\laravel\BuilderQuery;

use zxf\laravel\BuilderQuery\WhereHasIn;

class WhereHasNotIn extends WhereHasIn
{
    /**
     * @var string
     */
    protected $method = 'whereNotIn';
}

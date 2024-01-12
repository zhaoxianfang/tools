<?php

namespace zxf\Laravel\BuilderQuery;

use zxf\Laravel\BuilderQuery\WhereHasIn;

class WhereHasNotIn extends WhereHasIn
{
    /**
     * @var string
     */
    protected $method = 'whereNotIn';
}

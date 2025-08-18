<?php

namespace zxf\Laravel\BuilderQuery;

class WhereHasNotIn extends WhereHasIn
{
    /**
     * @var string
     */
    protected $method = 'whereNotIn';
}

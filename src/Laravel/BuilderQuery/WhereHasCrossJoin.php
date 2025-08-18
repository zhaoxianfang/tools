<?php

namespace zxf\Laravel\BuilderQuery;

class WhereHasCrossJoin extends WhereHasJoin
{
    /**
     * @var string
     */
    protected $method = 'crossJoin';
}

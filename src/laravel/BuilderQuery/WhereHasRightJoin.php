<?php

namespace zxf\Laravel\BuilderQuery;

class WhereHasRightJoin extends WhereHasJoin
{
    /**
     * @var string
     */
    protected $method = 'rightJoin';
}
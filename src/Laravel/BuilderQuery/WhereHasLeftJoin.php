<?php

namespace zxf\Laravel\BuilderQuery;

class WhereHasLeftJoin extends WhereHasJoin
{
    /**
     * @var string
     */
    protected $method = 'leftJoin';
}
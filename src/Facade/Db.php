<?php

namespace zxf\Facade;

/**
 * 数据库操作类 未整合完成
 * @method int count ()
 * @method mixed ArrayBuilder()
 * @method mixed JsonBuilder()
 * @method mixed ObjectBuilder()
 * @method mixed byId(string $id, mixed $fields)
 * @method mixed get(mixed $limit, mixed $fields)
 * @method mixed getOne(mixed $fields)
 * @method mixed paginate(int $page, array $fields)
 * @method mixed join(string $objectName, string $key, string $joinType, string $primaryKey)
 * @method mixed with(string $objectName)
 */
class Db extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\mysql\Db::class;
    }
}
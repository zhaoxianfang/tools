<?php

namespace zxf\Facade;

/**
 * Mysqli Model 模型类
 *
 * @link http://github.com/zhaoxianfang/tools
 * @link https://gitee.com/zhaoxianfang/tools
 *
 * @method string getTableName()
 * @method mixed query()
 * @method mixed fill()
 * @method mixed save()
 * @method mixed create()
 * @method mixed createOrFail()
 * @method mixed find()
 * @method Model findOrFail()
 * @method Model hasMany()
 * @method Model belongsTo()
 * @method Model hasOne()
 * @method Model hasManyThrough()
 */
class Model extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Database\Model::class;
    }
}
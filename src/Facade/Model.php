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
 * @method mixed refresh()
 * @method mixed save()
 * @method mixed create()
 * @method mixed createOrFail()
 * @method mixed update()
 * @method Model find()
 * @method Model findOrFail()
 * @method Model first()
 * @method Model get()
 * @method Model hasMany()
 * @method Model belongsTo()
 * @method Model hasOne()
 * @method Model hasManyThrough()
 * @method Model setData()
 * @method Model getData()
 */
class Model extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Database\Model::class;
    }
}
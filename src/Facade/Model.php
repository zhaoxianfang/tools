<?php

namespace zxf\Facade;

/**
 * Mysqli Model 模型类
 *
 * @link http://github.com/zhaoxianfang/tools
 *
 * @method string getTableName()
 * @method static query()
 * @method mixed fill(array $data)
 * @method mixed refresh()
 * @method mixed save(array $data = [])
 * @method mixed create(array $data = [])
 * @method mixed createOrFail(array $data = [])
 * @method mixed update(array $data = [])
 * @method Model find($id)
 * @method Model findOrFail($id)
 * @method Model first()
 * @method Model get()
 * @method Model hasMany($table, $foreignKey, $localKey, $field)
 * @method Model belongsTo($table, $foreignKey, $ownerKey, $field)
 * @method Model hasOne($table, $foreignKey, $field = '*')
 * @method Model belongsToMany($table, $throughTable, $ownerForeignKey, $throughForeignKey, $ownerKey = "id",$throughKey = "id", $field = '*')
 * @method Model setData($data = [], $multi = false)
 * @method Model toArray()
 */
class Model extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Database\Model::class;
    }
}

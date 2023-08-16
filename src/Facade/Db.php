<?php

namespace zxf\Facade;

/**
 * 数据库操作类
 * @method static mixed newQuery ()
 * @method mixed connect()
 * @method mixed table()
 * @method mixed select()
 * @method mixed where()
 * @method mixed whereIn()
 * @method mixed whereNotIn()
 * @method mixed orWhere()
 * @method mixed whereColumn()
 * @method mixed orWhereColumn()
 * @method mixed whereNull()
 * @method mixed whereNotNull()
 * @method mixed join()
 * @method mixed leftJoin()
 * @method mixed rightJoin()
 * @method mixed fullJoin()
 * @method mixed get()
 * @method mixed first()
 * @method mixed exists()
 * @method mixed doesntExist()
 * @method mixed each()
 * @method mixed insert()
 * @method mixed insertGetId()
 * @method mixed batchInsert()
 * @method mixed batchInsertAndGetIds()
 * @method mixed update()
 * @method mixed batchUpdate()
 * @method mixed increment()
 * @method mixed decrement()
 * @method mixed delete()
 * @method mixed beginTransaction()
 * @method mixed commit()
 * @method mixed rollBack()
 * @method mixed transaction()
 * @method mixed inTransaction()
 * @method mixed count()
 * @method mixed max()
 * @method mixed min()
 * @method mixed avg()
 * @method mixed sum()
 * @method mixed groupBy()
 * @method mixed having()
 * @method mixed orderBy()
 * @method mixed limit()
 * @method mixed paginate()
 * @method mixed toSql()
 * @method mixed getColumns()
 * @method mixed getLastQuery()
 * @method mixed getPrimaryKey()
 * @method mixed getIndexes()
 * @method mixed getError()
 * @method mixed when()
 * @method mixed exec()
 * @method mixed query()
 * @method mixed quote()
 */
class Db extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Database\Db::class;
    }
}
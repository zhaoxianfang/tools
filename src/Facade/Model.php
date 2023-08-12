<?php

namespace zxf\Facade;

/**
 * Mysqli Model 模型类
 *
 * @link http://github.com/zhaoxianfang/tools
 * @link https://gitee.com/zhaoxianfang/tools
 *
 * @method int table($tableName)
 * @method int count ()
 * @method \zxf\Database\Model ArrayBuilder()
 * @method Model JsonBuilder()
 * @method Model ObjectBuilder()
 * @method mixed byId(string $id, mixed $fields)
 * @method mixed get(mixed $limit, mixed $fields)
 * @method mixed getOne(mixed $fields)
 * @method mixed paginate(int $page, array $fields)
 * @method Model query($query, $numRows = null)
 * @method Model rawQuery($query, $bindParams = null)
 * @method Model join(string $objectName, string $key, string $joinType, string $primaryKey)
 * @method Model with(string $objectName)
 * @method Model groupBy(string $groupByField)
 * @method Model orderBy($orderByField, $orderbyDirection = "DESC", $customFieldsOrRegExp = null)
 * @method Model where($whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
 * @method Model orWhere($whereProp, $whereValue = 'DBNULL', $operator = '=')
 * @method Model having($havingProp, $havingValue = 'DBNULL', $operator = '=', $cond = 'AND')
 * @method Model orHaving($havingProp, $havingValue = null, $operator = null)
 * @method Model setQueryOption($options)
 * @method Model setTrace($enabled, $stripPrefix = null)
 * @method Model withTotalCount()
 * @method Model startTransaction()
 * @method Model commit()
 * @method Model rollback()
 * @method Model ping()
 * @method string getLastError()
 * @method string getLastQuery()
 */
class Model extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\Database\Model::class;
    }
}
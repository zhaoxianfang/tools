<?php


namespace zxf\mysql;

use zxf\mysql\Db;

class Model
{

    /**
     * Working instance of Db created earlier
     *
     * @var Db
     */
    private $db;

    /**
     * Primary key for an object. 'id' is a default value.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name for an object. Class name will be used by default
     *
     * @var string
     */
    protected $dbTable;

    public $connectionName = 'default';

    public function __construct($data = null)
    {
        $this->db = Db::instance();
        $this->db->connect($this->connectionName);
        if (empty ($this->dbTable)) {
            $this->dbTable = $this->underlineConvert(get_class($this));
            $this->db->table($this->dbTable);
        }
        if ($data) {
            $this->data = $data;
        }
    }

    // 获取模型类名(驼峰转下划线)
    public function underlineConvert(string $str): string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }

    public static function query()
    {
        return new static();
    }

    /**
     * Magic setter function
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (property_exists($this, 'hidden') && array_search($name, $this->hidden) !== false) {
            return $this;
        }

        $this->data[$name] = $value;
        return $this;
    }


    public function hasMany($table, $foreignKey = null, $localKey = 'id', $where = '', $field = '*')
    {
        $query = "SELECT $field FROM $table WHERE $foreignKey = ?";
        if (!empty($where)) {
            $query .= " AND $where";
        }
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new \Exception('预处理失败:' . $this->db->error);
        }
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            throw new \Exception('查询失败:' . $this->db->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // 需要放到model中
    public function belongsTo($table, $foreignKey, $ownerKey = 'id', $where = "", $field = '*')
    {
        $query = "SELECT $field FROM $table WHERE $ownerKey = (SELECT $foreignKey FROM " . get_class($this) . " WHERE id = ?)";

        if (!empty($where)) {
            $query .= " AND $where";
        }

        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new \Exception('预处理失败:' . $this->db->error);
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            throw new \Exception('查询失败:' . $this->db->error);
        }

        return $result->fetch_assoc();
    }

    public function hasOne($table, $foreignKey, $where = "", $field = '*')
    {
        $query = "SELECT $field FROM $table WHERE $foreignKey = ?";

        if ($where != "") {
            $query .= " AND $where";
        }
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new \Exception('预处理失败:' . $this->db->error);
        }
        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            throw new \Exception('查询失败:' . $this->db->error);
        }

        return $result->fetch_assoc();
    }

    public function hasManyThrough($table, $through_table, $foreign_key, $through_foreign_key, $where = "")
    {
        $query = "SELECT $table.* FROM $table JOIN $through_table ON $table.id = $through_table.$foreign_key WHERE $through_table.$through_foreign_key = ?";

        if ($where != "") {
            $query .= " AND $where";
        }

        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new \Exception('预处理失败:' . $this->db->error);
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            throw new \Exception('查询失败:' . $this->db->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // 预加载
    public function preload($table, $foreign_key, $where = "")
    {
        $query = "SELECT * FROM $table WHERE $foreign_key IN (?)";

        if ($where != "") {
            $query .= " AND $where";
        }

        $ids = array_column($this->hasMany($table, $foreign_key), 'id');

        if (count($ids) == 0) {
            return [];
        }

        $stmt = $this->db->prepare(str_replace("?", implode(",", array_fill(0, count($ids), "?")), $query));

        if (!$stmt) {
            throw new \Exception('预处理失败:' . $this->db->error);
        }

        $stmt->bind_param(str_repeat("i", count($ids)), ...$ids);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            throw new \Exception('查询失败:' . $this->db->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Catches calls to undefined methods.
     *
     * Provides magic access to private functions of the class and native public Db functions
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     */
    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        return call_user_func_array(array($this->db, $method), $arg);
    }

    /**
     * Catches calls to undefined static methods.
     *
     * Transparently creating Model class to provide smooth API like name::get() name::orderBy()->get()
     *
     * @param string $method
     * @param mixed  $arg
     *
     * @return mixed
     */
    public static function __callStatic($method, $arg)
    {
        $class = self::class;
        if (method_exists($class, $method)) {
            return call_user_func_array(array($class, $method), $arg);
        }

        if (empty($class->db)) {
            $class = self::query();
        }
        return call_user_func_array(array($class->db, $method), $arg);
    }
}
<?php


namespace zxf\mysql;


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

    public function __construct($data = null)
    {
        $this->db = Db::instance();
        if (empty ($this->dbTable)) {
            $this->dbTable = $this->underlineConvert(get_class($this));
            $this->db->setTable($this->dbTable);
        }
        if ($data) {
            $this->data = $data;
        }
    }

    /**
     * Magic setter function
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (property_exists($this, 'hidden') && array_search($name, $this->hidden) !== false) {
            return;
        }

        $this->data[$name] = $value;
    }
}
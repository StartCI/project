<?php

namespace CodeIgniter\Startci;

use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;

/**
 * Class Db
 * @package CodeIgniter\Startci
 * 
 * @implements ConnectionInterface<TConnection, TResult>
 * @mixin ConnectionInterface
 */
class Db
{

    /**
     * @var \CodeIgniter\Database\Connection
     */
    var $con = null;

    function __construct($db = null)
    {
        $this->con = $db;
    }

    function __call($name, $params = [])
    {
        if (method_exists($this->con, $name)){
            $r = $this->con->{$name}(...$params);
            return $r;
        }
            
        return null;
    }

    function whereRaw($cond)
    {
        $this->con->where($cond, null, false);
        return $this->con;
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function rs(string $type = 'object')
    {
        return $this->result($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function result(string $type = 'object')
    {
        return $this->con->get()->getResult($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function first(string $type = 'object')
    {
        return $this->con->get()->getFirstRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function last(string $type = 'object')
    {
        return $this->con->get()->getLastRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $mode Mode to set
     *
     * @return BaseBuilder
     */
    function def($values = [])
    {
        $table = $this->con->tableName;
        $db = $this->con->db();
        $v = [];
        foreach ($db->getFieldNames($table) as $key => $value) {
            $v[$value] = (!isset($values[$value])) ? null : $values[$value];
        }
        return (object) $v;
    }
    
}

<?php

namespace CodeIgniter\Startci;

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
        if (method_exists($this->con, $name))
            return $this->con->{$name}(...$params);
        return null;
    }
    
}

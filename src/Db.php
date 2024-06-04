<?php

namespace CodeIgniter\Startci;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;

/**
 * Class Db
 * @package CodeIgniter\Startci
 *
 * @implements ConnectionInterface<TConnection, TResult>
 * @mixin ConnectionInterface
 * @mixin BaseConnection
 * @method Builder table($name)
 */
class Db
{
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    var $con = null;

    var $builder = null;

    function __construct(\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->con = $db;
    }
    /**
     * Summary of __call
     * @param mixed $name
     * @param mixed $params
     * @return mixed|Builder|self
     */
    function __call($name, $params = [])
    {
        if (method_exists($this->con, $name)) {
            $r = $this->con->{$name}(...$params);
            if (gettype($r) == 'object') {
                if (class_basename($r) == 'Builder') {
                    return new Builder($this->con, $r);
                } else {
                    return $r;
                }
            } else {
                return $r;
            }
        }

        return null;
    }

}

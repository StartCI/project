<?php

namespace CodeIgniter\Startci;


class Record implements \ArrayAccess
{

    private $data = [];
    private $table = '';
    /** @var \CodeIgniter\Database\BaseConnection|Db */
    private $db = null;

    function __construct($table = null, $db = null)
    {
        $this->table = $table;
        if ($db == null) {
            $this->db = db($db);
        } else {
            $this->db = $db;
        }
    }

    function setDatabase($db)
    {
        $this->db = $db;
    }
    function getDatabase()
    {

        return $this->db;
    }
    function setTable($table)
    {

        $this->table = $table;
    }
    function getTable()
    {

        return $this->table;
    }
    function getData()
    {

        return $this->data;
    }

    function save()
    {
        if (isset($this->data['id'])) {
            $this->db->table($this->table)->where('id', $this->data['id'])->update($this->data);
            return $this;
        } else {
            $this->db->table($this->table)->insert($this->data);
            $this->data['id'] = $this->db->insertID();
            $this->data = (array)$this->db->table($this->table)->where('id', $this->data['id'])->first();
            return $this;
        }
    }

    function insert()
    {
        if (isset($this->data['id'])) {
            unset($this->data['id']);
        }
        $this->db->table($this->table)->insert($this->data);
        $this->data['id'] = $this->db->insertID();
        $this->data = (array)$this->db->table($this->table)->where('id', $this->data['id'])->first();
        return $this;
    }
    function update()
    {
        $this->db->table($this->table)->where('id', $this->data['id'])->update($this->data);
        return $this;
    }
    function delete()
    {
        if (!isset($this->data['id'])) {
            return null;
        }
        $this->db->table($this->table)->where('id', $this->data['id'])->delete();
        return null;
    }

    function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    function offsetExists(mixed $offset): bool
    {

        return array_key_exists($offset, $this->data);
    }
    function offsetGet(mixed $offset): mixed
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        } else {
            return null;
        }
    }
    function offsetSet(mixed $offset, mixed $value): void
    {

        $this->data[$offset] = $value;
    }
    function offsetUnset(mixed $offset): void
    {

        unset($this->data[$offset]);
    }
}

<?php

namespace CodeIgniter\Startci;

use \CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Traits\ConditionalTrait;
use stdClass;

class Builder extends BaseBuilder
{
    use ConditionalTrait;

    function whereRaw($cond)
    {
        $this->where($cond, null, false);
        return $this;
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
    function save($data)
    {
        if(isset($data['id'])){
            foreach ($data as $key => $value) {
                if(is_array($value)){
                    $data[$key] = table($key)->save($value);
                }
            }
            $this->where('id',$data['id'])->update($data);
            return $this->where('id',$data['id'])->first();
        }else{
            foreach ($data as $key => $value) {
                if(is_array($value)){
                    $data[$key] = table($key)->save($value);
                }
            }
            $this->insert($data);
            $data['id'] = $this->insertID();
            return $this->where('id',$data['id'])->first();
        }
    }
    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return boolean|stdClass[]|$type[]
     */
    public function result(string $type = 'object')
    {
        $get = $this->get();
        if ($get)
            return $get->getResult($type);
        else {
            return false;
        }
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
        return $this->get()->getFirstRow($type);
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
        return $this->get()->getLastRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $mode Mode to set
     *
     * @return \CodeIgniter\Database\BaseBuilder
     */
    function def($values = [])
    {
        $table = $this->tableName;
        $db = $this->db();
        $v = [];
        foreach ($db->getFieldNames($table) as $key => $value) {
            $v[$value] = (!isset($values[$value])) ? null : $values[$value];
        }
        return (object) $v;
    }

    /**
     * Creates a new record in the database table.
     *
     * @param array $fields An array of fields and their corresponding types.
     * @param bool $pk [optional] Determines whether to include primary key field. Default is true.
     * @throws \Throwable If there is an error connecting to the database.
     * @return $this Returns the current object.
     */
    public function create(array $fields, $pk = true)
    {
        $table = $this->tableName;
        while (true) {
            try {
                $this->db()->connect();
                break;
            } catch (\Throwable $th) {
                //throw $th;
            }
            sleep(1);
        }

        $forge = \Config\Database::forge($this->db);
        $db = $this->db;
        $tables = $db->listTables();
        $f = [];
        foreach ($fields as $k => $field) {
            if (is_numeric($k)) {
                $key_name = $field;
                $f[$key_name] = 'TEXT';
            } else {
                $key_name = $k;
                $f[$key_name] = $field;
            }
        }
        $fields = $f;

        if (in_array($table, $tables)) {
            $field_names = array_unique($db->getFieldNames($table) ?? []);

            foreach ($fields as $name => $type) {
                if (in_array($name, $field_names))
                    continue;
                if (strpos($type, '.') !== false) {
                    if ($pk) {
                        $type = explode('.', $type);
                        $forge->addField([
                            $name => [
                                'type' => 'INT',
                                'null' => true,
                            ]
                        ]);
                        $forge->addKey($name);
                        $forge->addForeignKey($name, $type[0], $type[1], 'SET NULL', 'SET NULL');
                        $forge->addColumn($table, [
                            $name => [
                                'type' => 'INT',
                                'null' => true
                            ]
                        ]);
                    }
                } else {
                    $forge->addColumn($table, [
                        $name => [
                            'type' => $type,
                            'null' => true
                        ]
                    ]);
                }
            }
        } else {
            $forge->addField('id');
            foreach ($fields as $name => $type) {
                if (strpos($type, '.') !== false) {
                    if ($pk) {
                        $forge->addField([
                            $name => [
                                'type' => 'INT',
                                'null' => true
                            ]
                        ]);
                        $type = explode('.', $type);
                        $forge->addKey($name);
                        $forge->addForeignKey($name, $type[0], $type[1], 'SET NULL', 'SET NULL');
                    }
                } else {
                    $forge->addField([
                        $name => [
                            'type' => $type,
                            'null' => true
                        ]
                    ]);
                }
            }
            switch ($db->getPlatform()) {
                case 'Postgre':
                    $forge->addField('created_at timestamp with time zone  NOT NULL  DEFAULT current_timestamp');
                    $forge->addField('updated_at timestamp with time zone  NOT NULL  DEFAULT current_timestamp');
                    break;
                case 'SQLite3':
                    $forge->addField('created_at DATETIME NOT NULL DEFAULT (datetime(\'now\',\'localtime\'))');
                    $forge->addField('updated_at DATETIME NOT NULL DEFAULT (datetime(\'now\',\'localtime\'))');
                    break;
                case 'MySQLi':
                    $forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
                    $forge->addField('updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
                    break;
            }
            $forge->createTable($table, true);
        }
        return $this;
    }
    function __call($name, $arguments)
    {
        return $this->$name(...$arguments);
    }
}

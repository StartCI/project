<?php

namespace CodeIgniter\Startci;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\Database;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

/**
 * @method ORM where($key, $value = null, bool $escape = null) Description
 * @method ORM orWhere($key, $value = null, bool $escape = null) Description
 * @method ORM distinct(bool $val = true)
 * @method ORM ignore(bool $ignore = true)
 * @method ORM select($select = '*', bool $escape = null)
 * @method ORM selectMax(string $select = '', string $alias = '')
 * @method ORM selectMin(string $select = '', string $alias = '')
 * @method ORM selectAvg(string $select = '', string $alias = '')
 * @method ORM selectSum(string $select = '', string $alias = '')
 * @method ORM selectCount(string $select = '', string $alias = '')
 * @method ORM from($from, bool $overwrite = false)
 * @method ORM join(string $table, string $cond, string $type = '', bool $escape = null)
 * @method ORM whereIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM orWhereIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM whereNotIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM orWhereNotIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM havingIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM orHavingIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM havingNotIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM orHavingNotIn(string $key = null, $values = null, bool $escape = null)
 * @method ORM like($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM notLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM orLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM orNotLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM havingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM notHavingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM orHavingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM orNotHavingLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false)
 * @method ORM groupStart()
 * @method ORM orGroupStart()
 * @method ORM notGroupStart()
 * @method ORM orNotGroupStart()
 * @method ORM groupEnd()
 * @method ORM havingGroupStart()
 * @method ORM orHavingGroupStart()
 * @method ORM notHavingGroupStart()
 * @method ORM orNotHavingGroupStart()
 * @method ORM havingGroupEnd()
 * @method ORM groupBy($by, bool $escape = null)
 * @method ORM having($key, $value = null, bool $escape = null)
 * @method ORM orHaving($key, $value = null, bool $escape = null)
 * @method ORM orderBy(string $orderBy, string $direction = '', bool $escape = null)
 * @method ORM limit(?int $value = null, ?int $offset = 0)
 * @method ORM offset(int $offset)
 * @method ORM resetQuery()
 * @method ORM def($values = [])
 *
 */
class ORM extends Record
{

    private $class = null;
    private $autoload = [];

    private $fields = [];
    /**
     * @var Builder
     */
    private $builder = null;
    private $queryHistory = [];

    function getQueryHistory(){

        return $this->queryHistory;
    }

    function onSave()
    {
        return true;
    }

    function onDelete()
    {
        return true;
    }

    function onSet($name, $value)
    {
        return $value;
    }

    function onGet($name)
    {
        return parent::__get($name);
    }

    function __get($name)
    {
        $value = $this->onGet($name);
        if(!$value){
            if($tmp = $this->getData()[$name]){
                return $tmp;
            }
        }
        return $value;
    }

    function __set($name, $value)
    {
        $value = $this->onSet($name, $value);
        // $this->data[$name] = $value;
        parent::__set($name, $value);
    }

    function get_fields(){

        return $this->fields;
    }
    /**
     *
     *
     * @param mixed $data
     * @return static
     */
    function save($data = [])
    {
        if ($this->onSave() === false)
            return false;
        $data = parent::save();
        return $data;
    }

    function delete()
    {
        if ($this->onDelete() === false)
            return false;

        return parent::delete();
    }

    public function __construct($db = null)
    {

        $this->class = get_class($this);
        $c_name = explode('\\', $this->class);
        $c_name = $c_name[count($c_name) - 1];
        if (!$this->getTable())
            $this->setTable(strtolower($c_name));


        $rc = new ReflectionClass($this->class);

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($rc->getDocComment() ?? '');
        $tags = $docblock->getTagsByName('property');
        $autoload = $docblock->getTagsByName('autoload');
        $this->setTable(strval($docblock->getTagsByName('table')[0]));
        if (!$this->getTable())
            $this->setTable(strtolower($c_name));

        if ($autoload) {
            $this->autoload = explode(' ', strval($autoload[0]));
        }
        foreach ($tags as $key => $value) {
            $type = strval($value->getType());
            $name = $value->getVariableName();
            if (!str_contains($type, 'Collection') && !str_contains($type, '[]'))
                $fields[] = [
                    'type' => $type,
                    'name' => $name
                ];
        }
        $this->fields = $fields;
        // xdebug_break();
        parent::__construct($this->getTable(), $db);
        $this->builder = $this->getDatabase()->table($this->getTable());
    }

    function load($prop)
    {
        $this->autoload[] = $prop;
        return $this;
    }
    function create($prefix=null){
        $this->__create($prefix, false);
        $this->__create($prefix, true);
    }
    function __create($prefix = null, $pk = true)
    {
        // xdebug_break();
        $rc = new ReflectionClass($this->class);
        if (!$models_create = cache()->get('startci_models_create'))
            $models_create = [];
        if (in_array($rc->getName(), $models_create))
            return false;
        $models_create[] = $rc->getName();
        cache()->save('startci_models_create', $models_create, 3600);

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($rc->getDocComment() ?? '');
        $tags = $docblock->getTagsByName('property');
        $fields = [];

        foreach ($tags as $key => $t) {
            // $t = new Property();
            $name = $t->getVariableName();
            $type = strval($t->getType());
            $is_relation = class_exists($type) && str_starts_with($type, '\App\Models');
            if ($is_relation && !in_array($type, ['date', 'datetime', 'timestamp'])) {
                $c = new $type($this->getDatabase());
                $c->create();
                $type = $c->table . '.id';
            }
            if ($type[0] == '\\')
                $type = substr($type, 1);
            switch ($type) { //melhorar
                case 'string':
                    $type = 'text';
                    break;
            }
            if (!$name)
                continue;
            if (str_contains($type, 'Collection') || str_contains($type, '[]')) {
                continue;
            }
            if (in_array($name, ['id', 'created_at', 'updated_at']))
                continue;
            $fields[$name] = $type;
        }
        $this->builder->create($fields, $pk);
    }

    function run_seed()
    {
        $rc = new ReflectionClass($this->class);
        $myClass = new $this->class();
        if ($rc->hasMethod('seed')) {
            if ($this->getDatabase()->table($this->getTable())->countAll() == 0) {
                CLI::write("Running seed in {$this->getTable()}");
                if ($seed = $myClass->seed()) {
                    foreach ($seed as $key => $s) {
                        try {
                            if (!$this->builder->insert($s)) {
                                die(CLI::error($this->class . "-" . $this->getDatabase()->error()['message']));
                            }
                        } catch (\Throwable $th) {
                            die(CLI::error($this->class . "-" . $this->getDatabase()->error()['message']));
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @param integer $id
     * @return self|parent|static
     */
    function byId($id): ORM|Record|self|null
    {
        $this->builder->where('id', $id);
        return $this->first();
    }



    /**
     *
     * @return \Tightenco\Collect\Support\Collection|self|parent|static|array|array[static]
     */
    function get(): \Tightenco\Collect\Support\Collection
    {
        try {
            $autoload = $this->get_autoload();
            $r = collect($this->builder->get()->getResult())->map(function ($v, $k) use ($autoload) {
                $class = $this->get_class();
                $r = new $class($this->getDatabase());
                if (!$v)
                    return null;
                foreach ($this->get_fields() as $key => $value) {
                    $name = $value['name'];
                    if (isset($v->{$name}))
                        $r->{$name} = ($v->{$name} != "null") ? $v->{$name} : null;
                    else
                        $r->{$name} = null;
                }
                foreach ($this->get_autoload() as $key => $value) {
                    $content = $r->onGet($value);
                    $r->{$value} = $content;
                }
                return $r;
            });
        } catch (\Throwable $th) {

            throw $th;
        }
        return $r;
    }



    /**
     *
     * @return ORM|Record|self|null
     */
    function first(): ORM|Record|self|null
    {
        $v = $this->_first();
        $class = $this->get_class();
        $r = new $class($this->getDatabase());
        if (!$v)
            return null;
        foreach ($this->get_fields() as $key => $value) {
            $name = $value['name'];
            if (isset($v->{$name}))
                $r->{$name} = ($v->{$name} != "null") ? $v->{$name} : null;
            else
                $r->{$name} = null;
        }
        foreach ($this->get_autoload() as $key => $value) {
            $content = $r->onGet($value);
            $r->{$value} = $content;
        }
        return $r;
    }

    /**
     *
     * @param string $name
     * @param array $params
     * @return Database\BaseBuilder
     */
    public function __call(string $name, array $params)
    {
        $result = null;
        if (method_exists($this->builder, $name))
            $result = $this->builder->{$name}(...$params);
        if (is_object($result) && !$result instanceof ORM)
            $result = $this;
        $this->queryHistory[] = $this->getDatabase()->getLastQuery() . '';
        return $result;
    }

    function toJson()
    {
        return json_encode($this->getData());
    }

    public function __debugInfo()
    {
        return $this->getData();
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    private function _first(string $type = 'object')
    {
        return $this->builder->get()->getFirstRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    private function _last(string $type = 'object')
    {
        return $this->builder->get()->getLastRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $mode Mode to set
     *
     * @return Builder
     */
    function def($values = [])
    {
        $table = $this->tableName;
        $forge = \Config\Database::forge($this->getDatabase());
        $db = $this->db();
        $v = [];

        foreach ($db->getFieldNames($table) as $key => $value) {
            $v[$value] = (!isset($values[$value])) ? null : $values[$value];
        }
        return (object) $v;
    }

    /**
     * Sets a test mode status.
     *
     * @param array $fields Array of fields
     * @param boolean $pk create or not pk
     *
     * @return $this
     */
    private function _create(array $fields, $pk = true)
    {
        $table = $this->getTable();
        while (true) {
            try {
                $this->builder->db()->connect();
                break;
            } catch (\Throwable $th) {
                //throw $th;
            }
            sleep(1);
        }

        $forge = \Config\Database::forge($this->getDatabase());
        $db = $this->getDatabase();
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

    function whereRaw($cond)
    {
        $this->where($cond, null, false);
        return $this;
    }

}

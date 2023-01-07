<?php

namespace CodeIgniter;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Config;
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
 * @mixin Database\BaseBuilder
 */
class ORM
{

    /**
     * 
     * @var Database\BaseConnection
     */
    private $db;

    /**
     * 
     * @var Database\BaseBuilder
     */
    private $builder;
    private $class;
    private $table = '';
    private $autoload = [];
    private $fields = [];
    private $data = [];
    private $queryHistory = [];

    /**
     * @return static
     */
    static function init($db = null)
    {
        return new static($db);
    }
    function get_fields()
    {
        return $this->fields;
    }
    function history()
    {
        return $this->queryHistory;
    }
    function get_class()
    {
        return $this->class;
    }
    function get_table()
    {
        return $this->table;
    }
    function get_autoload()
    {
        return array_unique($this->autoload);
    }
    function fromObject(object $o)
    {
        foreach (get_object_vars($o) as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }
    function fromArray(array $o)
    {
        foreach ($o as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    function onSave()
    {
        return true;
    }

    function onRemove()
    {
        return true;
    }

    function onSet($name, $value)
    {
        return $value;
    }

    function onGet($name)
    {
        return $this->data[$name];
    }

    function __get($name)
    {
        $value = $this->onGet($name);
        return $value;
    }

    function __set($name, $value)
    {
        $value = $this->onSet($name, $value);
        $this->data[$name] = $value;
        $this->{$name} = $value;
    }

    function save($data = [])
    {
        if ($this->onSave() === false)
            return false;
        $d = [];
        $reflection = new ReflectionObject($this);
        $properties = $reflection->getProperties();
        $ps = [];
        foreach ($properties as $property)
            $ps[] = $property->name;
        foreach ($ps as $key => $value) {
            $key = $value;
            $value = $this->{$value};
            if (isset($value->id))
                if ($value->id)
                    $value = $value->id;
            $d[$key] = $value;
        }
        $d = array_merge($d, $data);
        if ($d['id'] ?? false) {
            $this->where('id', $d['id'])->update($d);
            $r = $this->byId($d['id']);
            $this->id = $r->id;
            return $r;
        } else {
            if ($this->insert($d)) {
                $r = $this->byId($this->builder->selectMax('id')->first()->id);
                $this->id = $r->id;
                return $r;
            } else {
                return false;
            }
        }
    }

    function remove()
    {
        if ($this->onRemove() === false)
            return false;
        if ($this->id ?? false) {
            return $this->where('id', $this->id)->delete();
        }
        return false;
    }

    public function __construct($db = null)
    {
        $this->db = db_connect($db);
        $this->class = get_class($this);
        $c_name = explode('\\', $this->class);
        $c_name = $c_name[count($c_name) - 1];
        if (!$this->table)
            $this->table = strtolower($c_name);
        $this->builder = $this->db->table($this->table);
        $rc = new ReflectionClass($this->class);

        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($rc->getDocComment() ?? '');
        $tags = $docblock->getTagsByName('property');
        $autoload = $docblock->getTagsByName('autoload');
        $this->table = strval($docblock->getTagsByName('table')[0]);
        if (!$this->table)
            $this->table = strtolower($c_name);
        $this->builder = $this->db->table($this->table);
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
        $tables = $this->db->listTables();
        if (!in_array($this->table, $tables)) {
            $this->create();
        }
    }
    function load($prop)
    {
        $this->autoload[] = $prop;
        return $this;
    }
    function create($prefix = null, $pk = true)
    {
        $rc = new ReflectionClass($this->class);
        if (!$models_create = cache()->get('startci_models_create'))
            $models_create = [];
        if (in_array($rc->getName(), $models_create))
            return false;
        $models_create[] = $rc->getName();
        cache()->save('startci_models_create', $models_create, 3600);
        $myClass = new $this->class();
        if (!$prefix) {
            $prefix = implode('_', array_map('strtolower', array_slice(explode('\\', $myClass->class), 2, -1)));
            if ($prefix)
                $prefix .= '_';
        }
        if ($prefix)
            $this->builder->setTableName($prefix . $this->table);
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
                $c = new $type();
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
            if ($this->db->table($this->table)->countAll() == 0) {
                CLI::write("Running seed in $this->table");
                if ($seed = $myClass->seed()) {
                    foreach ($seed as $key => $s) {
                        try {
                            if (!$this->builder->insert($s)) {
                                die(CLI::error($this->class . "-" . db_connect()->error()['message']));
                            }
                        } catch (\Throwable $th) {
                            die(CLI::error($this->class . "-" . db_connect()->error()['message']));
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
    function byId($id)
    {
        $this->builder->where('id', $id);
        return $this->first();
    }


    function relation($class, $fk, $id = null, $mode = 'many')
    {
        $c = new $class($this->db);
        if (!$id)
            $id = $this->id;
        return $c->where($fk, $id)->get();
    }

    function relationOne($class, $fk, $id)
    {
        $c = new $class($this->db);
        return $c->where($fk, $id)->first();
    }

    /**
     * 
     * @return \Tightenco\Collect\Support\Collection|self|parent|static|array|array[static]
     */
    function get(): \Tightenco\Collect\Support\Collection
    {
        try {
            //code...

            $autoload = $this->get_autoload();
            $r = collect($this->builder->rs())->map(function ($v, $k) use ($autoload) {
                $class = $this->get_class();
                $r = new $class($this->db);
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
     * @return self|this|null|parent|static
     */
    function first()
    {
        $v = $this->builder->first();
        $class = $this->get_class();
        $r = new $class($this->db);
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
        return  $r;
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
        $this->queryHistory[] = db_connect()->getLastQuery() . '';
        return $result;
    }

    function toJson()
    {
        return json_encode($this->data);
    }

    public function __debugInfo()
    {
        return $this->data;
    }
}

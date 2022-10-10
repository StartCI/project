<?php

namespace CodeIgniter;

use CodeIgniter\CLI\CLI;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

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
    public $builder;
    public $class;
    public $table = '';
    public $autoload = [];
    public $fields = [];
    public $data = [];
    /**
     * @return static
     */
    static function init($db = null)
    {
        return new static($db);
    }
    function fromObject(object $o)
    {
        foreach (get_object_vars($o) as $key => $value) {
            $this->{$key} = $value;
        }
        return $this;
    }
    function fromArray(array $o)
    {
        foreach ($o as $key => $value) {
            $this->{$key} = $value;
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

        foreach ($this->data as $key => $value) {
            if (isset($value->id))
                $value = $value->id;
            $d[$key] = $value;
        }
        $d = array_merge($d, $data);
        if ($d['id'] ?? false) {
            $this->where('id', $d['id'])->update($d);
            return $this;
        } else {
            if ($this->insert($d))
                return $this->byId($this->builder->selectMax('id')->first($this->class)->id);
            else
                return false;
        }
    }

    function remove()
    {
        if ($this->onRemove() === false)
            return false;
        if ($this->data['id'] ?? false) {
            return $this->where('id', $this->data['id'])->delete();
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
    }
    function load($prop)
    {
        $this->autoload[] = $prop;
        return $this;
    }
    function create($prefix = null)
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
        $this->builder->create($fields);

        //TODO: Refatorar para criar 

        if ($rc->hasMethod('seed'))
            if (!$this->db->table($this->table)->first())
                if ($seed = $myClass->seed())
                    $this->builder->insertBatch($seed);
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
        $autoload = $this->autoload;
        $r = collect($this->builder->rs($this->class))->map(function ($v, $k) use ($autoload) {
            $r = (object) [];
            foreach ($this->fields as $key => $value) {
                $name = $value['name'];
                if (!in_array($name, $autoload))
                    $r->{$name} = $v->{$name};
            }
            foreach ($autoload as $key => $value)
                try {
                    $r->{$value} = $v->__get($value);
                } catch (\Throwable $th) {
                }
            $o = new $this->class();
            $r = $o->fromObject($r);
            return $r;
        });
        return $r;
    }

    /**
     * 
     * @return self|this|null|parent|static
     */
    function first()
    {
        $v = $this->builder->first($this->class);
        if (!$v)
            return null;
        $r = (object) [];
        foreach ($this->fields as $key => $value) {
            $name = $value['name'];
            if (!in_array($name, $this->autoload))
                $r->{$name} = $v->{$name};
        }
        foreach ($this->autoload as $key => $value)
            try {
                $r->{$value} = $v->__get($value);
            } catch (\Throwable $th) {
            }
        $o = new $this->class();
        $r = $o->fromObject($r);
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
        return $result;
    }

    function toJson()
    {
        return json_encode($this);
    }
}

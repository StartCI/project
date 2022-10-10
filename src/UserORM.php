<?php

namespace CodeIgniter;

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
class UserORM extends ORM
{

    function login_email($email, $password, $jwt = false)
    {
        if ($user = $this->where([
            'email' => $email,
            'password' => md5($password)
        ])->first()) {
            if($jwt){
                header('Content-Type: application/json');
                die(json_encode([
                    'token' => jwt_encode(['id'=>$user->id])
                ]));
            }
            session()->set('id', $user->id);
            return true;
        }
        return false;
    }

    function login_name($name, $password, $jwt = false)
    {
        if ($user = $this->where([
            'name' => $name,
            'password' => md5($password)
        ])->first()) {
            if($jwt){
                header('Content-Type: application/json');
                die(json_encode([
                    'token' => jwt_encode(['id'=>$user->id])
                ]));
            }
            session()->set('id', $user->id);
            return true;
        }
        return false;
    }

    function logout()
    {
        session()->destroy();
    }

    function validate_email($email, $password)
    {
        if ($this->where([
            'email' => $email,
            'password' => md5($password)
        ])->first())
            return true;
        return false;
    }

    function validate_name($name, $password)
    {
        if ($this->where([
            'name' => $name,
            'password' => md5($password)
        ])->first())
            return true;
        return false;
    }

    function get_type()
    {
        return $this->type ?? null;
    }

    function get_roles()
    {
        return json_decode($this->roles) ?? [];
    }

    function has_role($role)
    {
        return in_array($role, $this->get_roles());
    }
}

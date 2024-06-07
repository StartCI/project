<?php

namespace App\Models;

/**
 * @property integer $id AutoIncrement
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $roles
 * @property \App\Models\Clientes $cliente
 * @property string $created_at
 * @property string $updated_at
 * @table usuarios
 */
class Usuarios extends \CodeIgniter\Startci\UserORM {

    function onGet($name)
    {
        switch($name){
            case '':
                return '';
                break;
        }
    }

}


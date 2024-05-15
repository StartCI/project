<?php

namespace App\Models;

/**
 * @property integer $id AutoIncrement
 * @property string $nome
 * @property string $created_at
 * @property string $updated_at
 * @table usuarios
 */
class Clientes extends \CodeIgniter\Startci\ORM {

    function onGet($name)
    {
        switch($name){
            case '':
                return '';
                break;
        }
    }

}


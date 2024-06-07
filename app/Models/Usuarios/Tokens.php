<?php

namespace App\Models\Usuarios;

/**
 * @property integer $id AutoIncrement
 * @property \App\Models\Usuarios $usuario
 * @property string $valor
 * @property string $created_at
 * @property string $updated_at
 * @table usuarios_tokens
 */
class Tokens extends \CodeIgniter\Startci\ORM {

    function onGet($name)
    {
        switch($name){
            case '':
                return '';
                break;
        }
    }

}


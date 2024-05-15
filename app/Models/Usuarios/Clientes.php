<?php

namespace App\Models\Usuarios;

/**
 * @property integer $id AutoIncrement
 * @property \App\Models\Usuarios $usuario
 * @property \App\Models\Clientes $cliente
 * @property string $created_at
 * @property string $updated_at
 * @table usuarios_clientes
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


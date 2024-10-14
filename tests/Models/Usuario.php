<?php

namespace Tests\Models;
use CodeIgniter\Startci\ORM;
/**
 * @property int $id
 * @property string $nome
 * @property string $email
 * @property Cliente $cliente
 * @property string $created_at
 * @property string $updated_at
 * @table usuario
 */
class Usuario extends ORM
{
    function onGet($name){
        switch($name){
            case 'cliente': 
                return (new Cliente())->where('id', 1)->first();
            break;
        }

    }
}
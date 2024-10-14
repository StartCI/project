<?php

namespace Tests\Models;
use CodeIgniter\Startci\ORM;
/**
 * @property int $id
 * @property string $nome
 * @property string $email
 * @property Usuario[] $usuarios
 * @property string $created_at
 * @property string $updated_at
 * @table cliente
 */
class Cliente extends ORM
{
    function onGet($name){
        switch($name){
            case 'usuarios': return (new Usuario())->where('id', 1)->get()->getResult();
            break;
        }

    }
}
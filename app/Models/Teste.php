<?php

namespace App\Models;

/**
 * @property integer $id AutoIncrement
 * @property string $nome
 * @property string $fit
 * @property string $created_at
 * @property string $updated_at
 * @table teste
 */
class Teste extends \CodeIgniter\Startci\ORM {

    function onGet($name)
    {
        switch($name){
            case '':
                return '';
                break;
        }
    }

}


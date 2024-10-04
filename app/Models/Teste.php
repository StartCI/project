<?php

namespace App\Models;

/**
 * @property integer $id AutoIncrement
 * @property string $nome
 * @property string $telefone
 * @property string $created_at
 * @property string $updated_at
 * @table teste
 */
class Teste extends \CodeIgniter\Startci\ORM
{

    function seed()
    {
        $faker = faker();
        return array_map(function () use ($faker) {
            return [
                'nome' => $faker->name(),
                'telefone' => $faker->phoneNumber()
            ];
        }, range(1, 1000));
    }
    function onGet($name)
    {
        switch ($name) {
            case '':
                return '';
                break;
        }
    }

}


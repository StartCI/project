<?php

use CodeIgniter\Config\Services;
use CodeIgniter\Log\Logger;
use CodeIgniter\Startci\Commands\Orm;

// uses(\CodeIgniter\Test\CIUnitTestCase::class);
// uses(\CodeIgniter\Test\CIDatabaseTestCase::class);

beforeEach(function () { });

test('create', function () {
    $dbs = [
        db([
            'DBDriver' => 'MySQLi',
            'hostname' => 'mysql',
            'database' => 'startci',
            'username' => 'startci',
            'password' => 'startci',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ]),
        db([
            'DBDriver' => 'Postgre',
            'hostname' => 'postgres',
            'database' => 'startci',
            'username' => 'startci',
            'password' => 'startci',
            'charset' => 'utf8',
        ]),
        db([
            'DBDriver' => 'SQLite3',
            'hostname' => 'db.sqlite',
            'database' => 'db.sqlite',
        ])
    ];
    foreach ($dbs as $key => $db) {
        cache()->clean();
        $cliente = new \App\Models\Cliente($db);
        $cliente->create();
        $usuario = new \App\Models\Usuario($db);
        $usuario->create();
        $tables = $db->listTables();
        expect($tables)->toBeArray();
        expect($tables)->toContain('usuario');
        expect($tables)->toContain('cliente');
    }
})->skip();

test('save', function () {

    $dbs = [
        db([
            'DBDriver' => 'MySQLi',
            'hostname' => 'mysql',
            'database' => 'startci',
            'username' => 'startci',
            'password' => 'startci',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ]),
        db([
            'DBDriver' => 'Postgre',
            'hostname' => 'postgres',
            'database' => 'startci',
            'username' => 'startci',
            'password' => 'startci',
            'charset' => 'utf8',
        ]),
        db([
            'DBDriver' => 'SQLite3',
            'hostname' => 'db.sqlite',
            'database' => 'db.sqlite',
        ])
    ];
    foreach ($dbs as $key => $db) {
        cache()->clean();
        $model = new \App\Models\Cliente($db);
        $model->nome = 'NEWBGP';
        $cliente = $model->save();
        $id = $cliente->id;
        expect($id)->toBeNumeric();
    }
})->skip();

test('delete', function () {
    $dbs = [
        db([
            'DBDriver' => 'MySQLi',
            'hostname' => 'mysql',
            'database' => 'startci',
            'username' => 'startci',
            'password' => 'startci',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ]),
        db([
            'DBDriver' => 'Postgre',
            'hostname' => 'postgres',
            'database' => 'startci',
            'username' => 'startci',
            'password' => 'startci',
            'charset' => 'utf8',
        ]),
        db([
            'DBDriver' => 'SQLite3',
            'hostname' => 'db.sqlite',
            'database' => 'db.sqlite',
        ])
    ];
    foreach ($dbs as $key => $db) {
        $model = new \App\Models\Cliente($db);
        $ultimo_id = $model->selectMax('id')->first()->id;
        $tmp = $model->where('id', $ultimo_id);//null
        $model = $tmp->first();
        $model->delete();
        $foi_excluido = $model->where('id', $ultimo_id)->first();
        expect($foi_excluido)->toBeNull();
    }
})->skip();
test('select', function () { 

})->skip();


test('up', function () {
    // Services::commands()->run('startci:orm', ['up']);


    // xdebug_break();
})->skip();

test('seed', function () { })->skip();


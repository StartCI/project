<?php

use CodeIgniter\Config\Services;
use CodeIgniter\Log\Logger;
use CodeIgniter\Startci\Commands\Orm;

// uses(\CodeIgniter\Test\CIUnitTestCase::class);
// uses(\CodeIgniter\Test\CIDatabaseTestCase::class);

beforeEach(function () {});

test('create', function () {
    $dbs = [
        db([
            'DBDriver' => 'MySQLi',
            'hostname' => '127.0.0.1',
            'database' => 'startci',
            'username' => 'root',
            'password' => '123',
            'port' => 3306,
            'charset' => 'utf8',
            'DBCollat' => 'utf8_general_ci',
        ]),
        db([
            'DBDriver' => 'Postgre',
            'hostname' => 'localhost',
            'database' => 'startci',
            'username' => 'startci',
            'password' => '3af8601b46ab39f0',
            'charset'  => 'utf8',
        ]),
        db([
            'DBDriver' => 'SQLite3',
            'hostname' => 'db.sqlite',
            'database' => 'db.sqlite',
        ])
    ];

    foreach ($dbs as $key => $db) {
        cache()->clean();
        $model = new \App\Models\Clientes($db);
        $model->create();
        // $model->nome = 'felipe';
        // $model->save();
        // $model->nome = 'felipe2';
        // $model->save();
        // $json = $model->toJson();
        $tables = $db->listTables();
        expect($tables)->toBeArray();
        expect($tables)->toHaveCount(2);
        expect($tables)->toContain('usuarios');
        expect($tables)->toContain('clientes');
        // xdebug_break();
    }
});

test('save', function () {

    $dbs = [
        db([
            'DBDriver' => 'MySQLi',
            'hostname' => '127.0.0.1',
            'database' => 'startci',
            'username' => 'root',
            'password' => '123',
            'port' => 3306,
            'charset' => 'utf8',
            'DBCollat' => 'utf8_general_ci',
        ]),
        db([
            'DBDriver' => 'Postgre',
            'hostname' => 'localhost',
            'database' => 'startci',
            'username' => 'startci',
            'password' => '3af8601b46ab39f0',
            'charset'  => 'utf8',
        ]),
        db([
            'DBDriver' => 'SQLite3',
            'hostname' => 'db.sqlite',
            'database' => 'db.sqlite',
        ])
    ];
    foreach ($dbs as $key => $db) {
        cache()->clean();
        $model = new \App\Models\Clientes($db);
        $model->nome = 'NEWBGP';
        $cliente = $model->save();
        $id = $cliente->id;
        xdebug_break();
    }
});

test('delete', function () {
    $dbs = [
        // db([
        //     'DBDriver' => 'MySQLi',
        //     'hostname' => '127.0.0.1',
        //     'database' => 'startci',
        //     'username' => 'root',
        //     'password' => '123',
        //     'port' => 3306,
        //     'charset' => 'utf8',
        //     'DBCollat' => 'utf8_general_ci',
        // ]),
        // db([
        //     'DBDriver' => 'Postgre',
        //     'hostname' => 'localhost',
        //     'database' => 'startci',
        //     'username' => 'startci',
        //     'password' => '3af8601b46ab39f0',
        //     'charset'  => 'utf8',
        // ]),
        db([
            'DBDriver' => 'SQLite3',
            'hostname' => 'db.sqlite',
            'database' => 'db.sqlite',
        ])
    ];
    foreach ($dbs as $key => $db) {
        $model = new \App\Models\Clientes($db);
        $model->where('id', '1')->delete();//null
    }
});

test('select', function () {});


test('up', function () {
    // Services::commands()->run('startci:orm', ['up']);


    // xdebug_break();
});

test('seed', function () {});


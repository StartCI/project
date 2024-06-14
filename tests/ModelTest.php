<?php
use CodeIgniter\Config\Services;
use CodeIgniter\Log\Logger;
use CodeIgniter\Startci\Commands\Orm;

// uses(\CodeIgniter\Test\CIUnitTestCase::class);
// uses(\CodeIgniter\Test\CIDatabaseTestCase::class);

beforeEach(function () {
    // $this->sqlite = db([
    //     'DBDriver' => 'SQLite3',
    //     'hostname' => 'db.sqlite',
    //     'database' => 'db.sqlite',
    // ]);
});

test('create', function () {
    $db = [
        'DBDriver' => 'MySQLi',
        'hostname' => '127.0.0.1',
        'database' => 'startci',
        'username' => 'root',
        'password' => '3af8601b46ab39f0',
        'port' => 3306
    ];
    
    $model = new \App\Models\Usuarios($db);
    $model->create();
    $model = new \App\Models\Usuarios\Clientes($db);
    $model->create();
    $tabelas = db_connect()->listTables();
    xdebug_break();
});

test('up', function () {
    // Services::commands()->run('startci:orm', ['up']);


    // xdebug_break();
});

test('seed', function () {
});

test('connection', function () {
});

test('insert', function () {
});

test('update', function () {
});

test('delete', function () {
});

test('select', function () {
});

<?php
use CodeIgniter\Config\Services;
use CodeIgniter\Log\Logger;
use CodeIgniter\Startci\Commands\Orm;

// uses(\CodeIgniter\Test\CIUnitTestCase::class);
// uses(\CodeIgniter\Test\CIDatabaseTestCase::class);

beforeEach(function () {

});

test('create', function () {
    // $db = db([
    //     'DBDriver' => 'MySQLi',
    //     'hostname' => 'localhost',
    //     'database' => 'startci',
    //     'username' => 'root',
    //     'password' => '3af8601b46ab39f0',
    //     'port' => 3306,
    //     'charset' => 'utf8',
    //     'DBCollat' => 'utf8_general_ci',
    // ]);
    $db = db([
        'DBDriver' => 'Postgre',
        'hostname' => 'localhost',
        'database' => 'startci',
        'username' => 'startci',
        'password' => '3af8601b46ab39f0',
        'charset'     => 'utf8',
    ]);
    // $db = db([
    //     'DBDriver' => 'SQLite3',
    //     'hostname' => 'db.sqlite',
    //     'database' => 'db.sqlite',
    // ]);
    // $model = new \App\Models\Usuarios($db);
    // $model->create();
    cache()->clean();
    // xdebug_break();
    $model = new \App\Models\Usuarios\Clientes($db);
    $model->create();
    $teste = new \App\Models\Teste($db);
    $teste->create();

    $teste->save();
    // $model->create();
    // $tabelas = db_connect()->listTables();
    // xdebug_break();

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

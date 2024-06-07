<?php
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
    // command('startci:orm up');
    // $db = db_connect();
    // xdebug_break();
});

test('up', function () {
    
  $orm = new Orm()  ;
  $orm->up();
    // $driver = env('database.default.DBDriver');
    // $db = db_connect();
    
    // xdebug_break();
    // command('startci:orm up');
    
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

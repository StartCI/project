<?php
use CodeIgniter\Database\BaseConnection;
use \CodeIgniter\Database\SQLite3\Connection;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsNumeric;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotFalse;

beforeEach(function () {
    $this->sqlite = db([
        'DBDriver' => 'SQLite3',
        'hostname' => 'db',
    ]);
    $this->mysql = db([
        'DBDriver' => 'MySQLi',
        'hostname' => 'localhost',
        'database' => 'startci',
        'username' => 'root',
        'password' => '3af8601b46ab39f0',
    ]);
    $this->postgres = db([
        'DBDriver' => 'Postgre',
        'hostname' => 'localhost',
        'database' => 'startci',
        'username' => 'startci',
        'password' => '3af8601b46ab39f0',
    ]);
});
afterEach(function () {
});
test('connection', function () {
    expect($this->sqlite)->toBeInstanceOf(CodeIgniter\Database\SQLite3\Connection::class);
    expect($this->mysql)->toBeInstanceOf(CodeIgniter\Database\MySQLi\Connection::class);
    expect($this->postgres)->toBeInstanceOf(CodeIgniter\Database\Postgre\Connection::class);

});
test('create table sqlite', function () {

    $db = $this->sqlite;
    xdebug_break();

    $db->table('users')->create([
        'name' => 'text',
        'age' => 'integer',
        'email' => 'text',
        'password' => 'text',
    ]);
    table('user_cnames', $db)->create([
        'user' => 'users.id',
        'name' => 'text'
    ]);
    $tables = $db->listTables();
    $table_users_fields = $db->getFieldData('users');
    $table_user_cnames_fields = $db->getFieldData('user_cnames');
    assertEquals([
        [
            "name" => "id",
            "type" => "INTEGER",
            "max_length" => null,
            "default" => null,
            "primary_key" => true,
            "nullable" => true
        ],
        [
            "name" => "name",
            "type" => "TEXT",
            "max_length" => null,
            "default" => null,
            "primary_key" => false,
            "nullable" => true
        ],
        [
            "name" => "age",
            "type" => "INTEGER",
            "max_length" => null,
            "default" => null,
            "primary_key" => false,
            "nullable" => true
        ],
        [
            "name" => "email",
            "type" => "TEXT",
            "max_length" => null,
            "default" => null,
            "primary_key" => false,
            "nullable" => true
        ],
        [
            "name" => "password",
            "type" => "TEXT",
            "max_length" => null,
            "default" => null,
            "primary_key" => false,
            "nullable" => true
        ],
        [
            "name" => "created_at",
            "type" => "DATETIME",
            "max_length" => null,
            "default" => "datetime('now','localtime')",
            "primary_key" => false,
            "nullable" => false
        ],
        [
            "name" => "updated_at",
            "type" => "DATETIME",
            "max_length" => null,
            "default" => "datetime('now','localtime')",
            "primary_key" => false,
            "nullable" => false
        ]
    ], json_decode(json_encode($table_users_fields), true));
    assertEquals(
        [
            [
                "name" => "id",
                "type" => "INTEGER",
                "max_length" => null,
                "default" => null,
                "primary_key" => true,
                "nullable" => true
            ],
            [
                "name" => "user",
                "type" => "INT",
                "max_length" => null,
                "default" => null,
                "primary_key" => false,
                "nullable" => true
            ],
            [
                "name" => "name",
                "type" => "TEXT",
                "max_length" => null,
                "default" => null,
                "primary_key" => false,
                "nullable" => true
            ],
            [
                "name" => "created_at",
                "type" => "DATETIME",
                "max_length" => null,
                "default" => "datetime('now','localtime')",
                "primary_key" => false,
                "nullable" => false
            ],
            [
                "name" => "updated_at",
                "type" => "DATETIME",
                "max_length" => null,
                "default" => "datetime('now','localtime')",
                "primary_key" => false,
                "nullable" => false
            ]
        ],
        json_decode(json_encode($table_user_cnames_fields), true)
    );
    $prefix = $db->getPrefix();
    assertContains($prefix . 'users', $tables);
    assertContains($prefix . 'user_cnames', $tables);
})->only();
test('insert', function () {
    table('users', static::$db)->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $id = static::$db->insertID();
    assertNotFalse($id);
    assertIsNumeric($id);
    foreach (range(1, 10) as $key => $value) {
        table('user_cnames', static::$db)->insert([
            'user' => $id,
            'name' => 'test',
        ]);
        $id_user_cnames = static::$db->insertID();
        assertNotFalse($id_user_cnames);
        assertIsNumeric($id_user_cnames);
    }
});
test('select', function () {
    $db = static::$db;
    $resultado1 = $db->table('users')->select('id, name, age, email')->get()->getResult();
    $resultado2 = $db->table('users')->select('id, name, age, email')->get()->getFirstRow();
    assertEquals(table('users', static::$db)->select('id,name,age,email')->rs(), $resultado1);
    assertEquals(table('users', static::$db)->select('id,name,age,email')->first(), $resultado2);
    assertEquals(table('users', static::$db)->select('id,name,age,email')->last(), $resultado2);
});
test('update', function () {
    $resultado1 = table('users', static::$db)->like('name', 'felipe')->select('id,name')->rs();
    table('users', static::$db)->like('name', 'tes')->update([
        'name' => 'felipe',
    ]);
    $resultado2 = table('users', static::$db)->like('name', 'felipe')->select('id,name')->rs();
    assertNotEquals($resultado1, $resultado2);
});
test('delete', function () {
    table('users', static::$db)->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    table('users', static::$db)->where('id', 1)->delete();
    $total = table('users', static::$db)->selectCount('id', 'total')->first()->total;
    assertEquals($total, 1);
});

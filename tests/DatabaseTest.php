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
        'hostname' => 'db.sqlite',
        'database' => 'db.sqlite',
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
    expect($this->sqlite->con)->toBeInstanceOf(CodeIgniter\Database\SQLite3\Connection::class);
    expect($this->mysql->con)->toBeInstanceOf(CodeIgniter\Database\MySQLi\Connection::class);
    expect($this->postgres->con)->toBeInstanceOf(CodeIgniter\Database\Postgre\Connection::class);

})->only();
test('create table sqlite', function () {

    $sqlite = db([
        'DBDriver' => 'SQLite3',
        'hostname' => 'db.sqlite',
        'database' => 'db.sqlite',
    ]);
    $db = $sqlite;
    
    $db->table('users')->create([
        'name' => 'text',
        'age' => 'integer',
        'email' => 'text',
        'password' => 'text',
    ]);
    $db->table('user_cnames')->create([
        'user' => 'users.id',
        'name' => 'text'
    ]);

    $tables = $db->listTables();
    expect($tables)->toContain('users');
    expect($tables)->toContain('user_cnames');
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

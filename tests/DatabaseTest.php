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
    if (file_exists('writable/db.sqlite')) {
        unlink('writable/db.sqlite');
    }
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
test('connection', function () {
    expect($this->sqlite->con)->toBeInstanceOf(CodeIgniter\Database\SQLite3\Connection::class);
    expect($this->mysql->con)->toBeInstanceOf(CodeIgniter\Database\MySQLi\Connection::class);
    expect($this->postgres->con)->toBeInstanceOf(CodeIgniter\Database\Postgre\Connection::class);

});
test('sqlite', function () {
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
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $id = $db->insertID();
    assertNotFalse($id);
    assertIsNumeric($id);
    foreach (range(1, 10) as $key => $value) {
        $db->table('user_cnames')->insert([
            'user' => $id,
            'name' => 'test',
        ]);
        $id_user_cnames = $db->insertID();
        assertNotFalse($id_user_cnames);
        assertIsNumeric($id_user_cnames);
    }
    $resultado1 = $db->table('users')->select('id, name, age, email')->get()->getResult();
    $resultado2 = $db->table('users')->select('id, name, age, email')->get()->getFirstRow();
    assertEquals($db->table('users')->select('id,name,age,email')->rs(), $resultado1);
    assertEquals($db->table('users')->select('id,name,age,email')->first(), $resultado2);
    assertEquals($db->table('users')->select('id,name,age,email')->last(), $resultado2);

    $resultado1 = $db->table('users')->like('name', 'felipe')->select('id,name')->rs();
    $db->table('users')->like('name', 'tes')->update([
        'name' => 'felipe',
    ]);
    $resultado2 = $db->table('users')->like('name', 'felipe')->select('id,name')->rs();
    assertNotEquals($resultado1, $resultado2);
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $db->table('users')->where('id', 1)->delete();
    $total = $db->table('users')->selectCount('id', 'total')->first()->total;
    assertEquals($total, 1);
});

test('mysql', function () {
    $mysql = db([
        'DBDriver' => 'MySQLi',
        'hostname' => '127.0.0.1',
        'database' => 'startci',
        'username' => 'root',
        'password' => '3af8601b46ab39f0',
    ]);
    $db = $mysql;
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
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $id = $db->insertID();
    assertNotFalse($id);
    assertIsNumeric($id);
    foreach (range(1, 10) as $key => $value) {
        $db->table('user_cnames')->insert([
            'user' => $id,
            'name' => 'test',
        ]);
        $id_user_cnames = $db->insertID();
        assertNotFalse($id_user_cnames);
        assertIsNumeric($id_user_cnames);
    }
    $resultado1 = $db->table('users')->select('id, name, age, email')->get()->getResult();
    $resultado2 = $db->table('users')->select('id, name, age, email')->get()->getFirstRow();
    assertEquals($db->table('users')->select('id,name,age,email')->rs(), $resultado1);
    assertEquals($db->table('users')->select('id,name,age,email')->first(), $resultado2);
    assertEquals($db->table('users')->select('id,name,age,email')->last(), $resultado2);
    $resultado1 = $db->table('users')->like('name', 'felipe')->select('id,name')->rs();
    $db->table('users')->like('name', 'tes')->update([
        'name' => 'felipe',
    ]);
    $resultado2 = $db->table('users')->like('name', 'felipe')->select('id,name')->rs();
    assertNotEquals($resultado1, $resultado2);
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $db->table('users')->where('id', 1)->delete();
    $total = $db->table('users')->selectCount('id', 'total')->first()->total;
    assertEquals($total, 1);
    $db->disableForeignKeyChecks();
    $tables = $db->listTables();
    foreach ($tables as $key => $value) {
        $db->query('DROP TABLE IF EXISTS ' . $value);
    }
});

test('postgres', function () {
    $postgres = db([
        'DBDriver' => 'Postgre',
        'hostname' => '127.0.0.1',
        'database' => 'startci',
        'username' => 'postgres',
        'password' => '3af8601b46ab39f0',
    ]);
    $db = $postgres;
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
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $id = $db->insertID();
    assertNotFalse($id);
    assertIsNumeric($id);
    foreach (range(1, 10) as $key => $value) {
        $db->table('user_cnames')->insert([
            'user' => $id,
            'name' => 'test',
        ]);
        $id_user_cnames = $db->insertID();
        assertNotFalse($id_user_cnames);
        assertIsNumeric($id_user_cnames);
    }
    $resultado1 = $db->table('users')->select('id, name, age, email')->get()->getResult();
    $resultado2 = $db->table('users')->select('id, name, age, email')->get()->getFirstRow();
    assertEquals($db->table('users')->select('id,name,age,email')->rs(), $resultado1);
    assertEquals($db->table('users')->select('id,name,age,email')->first(), $resultado2);
    assertEquals($db->table('users')->select('id,name,age,email')->last(), $resultado2);
    $resultado1 = $db->table('users')->like('name', 'felipe')->select('id,name')->rs();
    $db->table('users')->like('name', 'tes')->update([
        'name' => 'felipe',
    ]);
    $resultado2 = $db->table('users')->like('name', 'felipe')->select('id,name')->rs();
    assertNotEquals($resultado1, $resultado2);
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $db->table('users')->where('id', 1)->delete();
    $total = $db->table('users')->selectCount('id', 'total')->first()->total;
    assertEquals($total, 1);
});

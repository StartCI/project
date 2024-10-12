<?php
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Startci\Record;
use \CodeIgniter\Database\SQLite3\Connection;
use Pest\Plugins\Only;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsNumeric;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotFalse;

test('sqlite', function () {
    $uid = uniqid();
    $db_name = 'db_' . $uid. '.sqlite';
    $sqlite = db([
        'DBDriver' => 'SQLite3',
        'hostname' => $db_name,
        'database' => $db_name,
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


    if (file_exists('writable/'.$db_name)) {
        unlink('writable/'.$db_name);
    }
});

test('record', function () {
    $uid = uniqid();
    $db_name = 'db_' . $uid. '.sqlite';
    
    $sqlite = db([
        'DBDriver' => 'SQLite3',
        'hostname' => $db_name,
        'database' => $db_name,
    ]);


    $db = $sqlite;
    $db->table('users')->create([
        'name' => 'text',
        'age' => 'integer',
        'email' => 'text',
        'password' => 'text',
    ]);
    $db->query('delete from users');
    $db->table('users')->insert([
        'name' => 'test',
        'age' => 1,
        'email' => 'test@example.com',
        'password' => 'test',
    ]);
    $db->table('users')->insert([
        'name' => 'test2',
        'age' => 1,
        'email' => 'test2@example.com',
        'password' => 'test2',
    ]);
    $record_result = $db->table('users')->like('name', 'test2')->first_record();

    expect($record_result['id'])->toBeNumeric();
    expect($record_result->id)->toBeNumeric();
    $record_result->name = 'record example';
    $value_return = $record_result->save();
    expect($value_return->name)->toBe('record example');
    if(file_exists('writable/'.$db_name)) {
        unlink('writable/'.$db_name);
    }

});

test('mysql', function () {
    $mysql = db([
        'DBDriver' => 'MySQLi',
        'hostname' => 'mysql',
        'database' => 'startci',
        'username' => 'startci',
        'password' => 'startci',
        'charset' => 'utf8mb4',
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
        'hostname' => 'postgres',
        'database' => 'startci',
        'username' => 'startci',
        'password' => 'startci',
        'charset' => 'utf8'
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

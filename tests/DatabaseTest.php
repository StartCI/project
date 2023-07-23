<?php
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Startci\Builder;
use Config\Database;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsNumeric;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotFalse;
use function PHPUnit\Framework\assertObjectEquals;
use function PHPUnit\Framework\assertTrue;

class DatabaseTest extends TestCase
{
    /**
     * @var BaseConnection
     */
    static $db;
    function setUp(): void
    {

    }
    function tearDown(): void
    {

    }
    function test_connection(): void
    {
        $db = db();
        static::$db = $db;
        assertInstanceOf(CodeIgniter\Database\SQLite3\Connection::class, $db);
    }
    function test_create_table(): void
    {
        table('users', static::$db)->create([
            'name' => 'text',
            'age' => 'integer',
            'email' => 'text',
            'password' => 'text',
        ]);
        table('user_cnames', static::$db)->create([
            'user' => 'users.id',
            'name' => 'text'
        ]);
        $tables = static::$db->listTables();
        $table_users_fields = static::$db->getFieldData('users');
        $table_user_cnames_fields = static::$db->getFieldData('user_cnames');
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
                "type" => "text",
                "max_length" => null,
                "default" => null,
                "primary_key" => false,
                "nullable" => true
            ],
            [
                "name" => "age",
                "type" => "integer",
                "max_length" => null,
                "default" => null,
                "primary_key" => false,
                "nullable" => true
            ],
            [
                "name" => "email",
                "type" => "text",
                "max_length" => null,
                "default" => null,
                "primary_key" => false,
                "nullable" => true
            ],
            [
                "name" => "password",
                "type" => "text",
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
                    "type" => "text",
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
        assertContains('users', $tables);
        assertContains('user_cnames', $tables);

    }
    function test_insert(): void
    {
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
    }
    function test_select(): void
    {
        $db = static::$db;
        $resultado1 = $db->table('users')->select('id, name, age, email')->get()->getResult();
        $resultado2 = $db->table('users')->select('id, name, age, email')->get()->getFirstRow();
        assertEquals(table('users', static::$db)->select('id,name,age,email')->rs(), $resultado1);
        assertEquals(table('users', static::$db)->select('id,name,age,email')->first(), $resultado2);
        assertEquals(table('users', static::$db)->select('id,name,age,email')->last(), $resultado2);

    }
    function test_update(): void
    {
        $resultado1 = table('users', static::$db)->like('name', 'felipe')->select('id,name')->rs();
        table('users', static::$db)->like('name', 'tes')->update([
            'name' => 'felipe',
        ]);
        $resultado2 = table('users', static::$db)->like('name', 'felipe')->select('id,name')->rs();
        assertNotEquals($resultado1, $resultado2);
    }
    function test_delete(): void
    {
        table('users', static::$db)->insert([
            'name' => 'test',
            'age' => 1,
            'email' => 'test@example.com',
            'password' => 'test',
        ]);
        table('users', static::$db)->where('id', 1)->delete();
        $total = table('users', static::$db)->selectCount('id', 'total')->first()->total;
        assertEquals($total, 1);
    }

    function test_save_insert()
    {

    }
    function test_save_update()
    {

    }

}

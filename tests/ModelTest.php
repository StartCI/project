<?php
use PHPUnit\Framework\TestCase;
class ModelTest extends TestCase
{
   /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    static $db;
    function setUp(): void {
        self::$db = db();
    }
    function test_create(): void{
        command('startci:orm up');
        $db = static::$db;
        xdebug_break();
    }
    function test_up(): void{
        command('startci:orm up');
        $db = static::$db;
        xdebug_break();
    }
    function test_seed(): void{

    }

    function test_connection(): void{

    }
    function test_insert(): void{

    }
    function test_update(): void{

    }
    function test_delete(): void{

    }
    function test_select(): void{

    }



}

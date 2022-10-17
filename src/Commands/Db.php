<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CodeIgniter\Startci\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Description of Db
 *
 * @author felipe
 */
class Db extends BaseCommand {

    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Startci';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'startci:db';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Easy migration system up|down|read force';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'db up|down|read force';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Ensures that all migrations have been run.
     *
     * @param array $params
     */
    public function run(array $params) {
        $cmd = $params[0] ?? null;
        $this->$cmd(in_array('force', $params));
    }

    function read() {
        $tables = db_connect()->listTables();
        foreach ($tables as $key => $value) {
            $field_data = db_connect()->getFieldData($value);
            $field_data = array_values(array_filter($field_data, function($v) {
                        return !in_array($v->name, ['id', 'created_at', 'updated_at']);
                    }));
            $file = "";
            $file .= "<?php" . PHP_EOL;
            $file .= "table('clientes')->create([" . PHP_EOL;
            foreach ($field_data as $key1 => $value1) {
                $file .= "    '$value1->name' => '$value1->type'," . PHP_EOL;
            }
            $file .= "]);" . PHP_EOL;
            // $file .= '$faker = faker();' . PHP_EOL;
//            $file .= "db_connect()->query('truncate table $value');" . PHP_EOL;
            foreach (table($value)->limit(100)->get()->getResult() as $key2 => $value2) {
                $file .= "table('$value')->insert([" . PHP_EOL;
                foreach (array_keys((array) $value2) as $key3 => $value3) {
                    if (in_array($value3, ['id', 'created_at', 'updated_at']))
                        continue;
                    $v = $value2->{$value3};
                    $c = array_values(array_filter($field_data, function($v)use($value3) {
                                return $v->name == $value3;
                            }))[0];
                    if (in_array($c->type, ['blob', 'mediumblob', 'longblob'])) {
//                        $v = '0x' . bin2hex($v); //implementar pg ?;
//                        $file .= "    '$value3' => $v," . PHP_EOL;
                    } else {
                        $file .= "    '$value3' => '$v'," . PHP_EOL;
                    }
                }
                $file .= "]);" . PHP_EOL;
            }
            file_put_contents('../app/Database/' . $key . '_' . $value . '.php', $file);
        }
    }

    function up($force = false) {
        foreach (glob('../app/Database/*.php') as $key => $value) {
            $name = basename($value);
            $key = explode('_', $name)[0];
            $table = str_replace('.php', '', explode('_', $name)[1]);

            if (in_array($table, db_connect()->listTables()) && !$force) {
                continue;
            }

            if (include_once '../app/Database/' . $name)
                ;
        }
    }

    function down($force = false) {
        
    }

}
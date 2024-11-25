<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CodeIgniter\Startci\Commands;

use CodeIgniter\ClassHelper;
use CodeIgniter\CLI\BaseCommand;

use CodeIgniter\CLI\CLI;
use Config\Services;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Description of Db
 *
 * @author felipe
 */
class Orm extends BaseCommand
{

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
    protected $name = 'startci:orm';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Manage orm';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'orm up|down|read force';

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
    public function run(array $params)
    {
        chdir(directory: WRITEPATH);
        $cmd = $params[0] ?? null;
        $this->$cmd($params);
    }

    function create($params)
    {
        if (!isset($params[1]))
            $params[1] = CLI::prompt('Class name ', null, 'required');
        $table = strtolower($params[1]);
        $className = ucfirst($params[1]);
        $file = '';
        $file .= "<?php" . PHP_EOL;
        $file .= "" . PHP_EOL;
        $file .= "namespace App\Models;" . PHP_EOL;
        $file .= "" . PHP_EOL;
        $file .= "/**" . PHP_EOL;
        $file .= " * @property integer \$id AutoIncrement" . PHP_EOL;
        $file .= " * @property string \$" . PHP_EOL;
        $file .= " * @property string \$created_at" . PHP_EOL;
        $file .= " * @property string \$updated_at" . PHP_EOL;
        $file .= " * @table $table" . PHP_EOL;
        $file .= " */" . PHP_EOL;
        $file .= "class $className extends \CodeIgniter\Startci\ORM {" . PHP_EOL;
        $file .= "" . PHP_EOL;
        $file .= "    function onGet(\$name)" . PHP_EOL;
        $file .= "    {" . PHP_EOL;
        $file .= "        switch(\$name){" . PHP_EOL;
        $file .= "            case '':" . PHP_EOL;
        $file .= "                return '';" . PHP_EOL;
        $file .= "                break;" . PHP_EOL;
        $file .= "        }" . PHP_EOL;
        $file .= "    }" . PHP_EOL;
        $file .= "" . PHP_EOL;
        $file .= "}" . PHP_EOL;
        $file .= "" . PHP_EOL;
        if (file_exists("../app/Models/$className.php"))
            if (strtoupper(CLI::prompt('Overwrite file ? (y,n)', null, 'required')) != "Y")
                return false;
        file_put_contents("../app/Models/$className.php", $file);
        $file = '';
        $file = file_get_contents('../app/Common.php') . PHP_EOL;;
        // $file .= "/** " . PHP_EOL;
        // $file .= " * @return \App\Models\\" . $className . PHP_EOL;
        // $file .= " */" . PHP_EOL;
        // $file .= "function model_$table(){" . PHP_EOL;
        // $file .= "  return new \App\Models\\" . $className . "();" . PHP_EOL;
        // $file .= "}" . PHP_EOL . PHP_EOL;
        // file_put_contents('../app/Common.php', $file);
    }

    function down()
    {
        $con = db_connect();
        $database = env('database.default.database');
        try {
            $con->simpleQuery("drop database $database");
            $con->simpleQuery("create database $database");
        } catch (\Throwable $th) {
        }
    }

    function read()
    {
        $con = db_connect();
        $tables = $con->listTables();

        foreach ($tables as $key => $table) {
            $className = ucfirst($table);
            $file = '';
            $file .= "<?php" . PHP_EOL;
            $file .= "" . PHP_EOL;
            $file .= "namespace App\Models;" . PHP_EOL;
            $file .= "" . PHP_EOL;
            $file .= "/**" . PHP_EOL;
            $file .= " * @property integer \$id AutoIncrement" . PHP_EOL;
            $fields = $con->getFieldData($table);
            $have_id = count(array_values(array_filter($fields, function ($v) {
                return in_array($v->name, ['id']);
            }))) == 1;
            $fields = array_values(array_filter($fields, function ($v) {
                return !in_array($v->name, ['created_at', 'updated_at', 'id']);
            }));
            $select_fields = [];
            foreach ($fields as $key => $field) {
                $name = $field->name;
                $type = $field->type;
                if (!(str_contains($type, 'blob') || str_contains($type, 'long'))) {
                    $select_fields[] = $name;
                }
                switch ($type) {
                    case 'tinyint':
                    case 'int':
                        $type = 'integer';
                        break;
                    case 'varchar':
                    case 'text':
                        $type = 'string';
                        break;
                }
                $file .= " * @property $type \$" . $name . PHP_EOL;
            }

            $file .= " * @property string \$created_at" . PHP_EOL;
            $file .= " * @property string \$updated_at" . PHP_EOL;
            $file .= " * @table $table" . PHP_EOL;
            $file .= " */" . PHP_EOL;
            $file .= "class $className extends \CodeIgniter\Startci\ORM {" . PHP_EOL;
            $file .= "" . PHP_EOL;
            $file .= "    function seed()" . PHP_EOL;
            $file .= "    {" . PHP_EOL;
            $file .= "        return [" . PHP_EOL;
            try {
                $rs = $con->table($table)->limit(10)->select($select_fields);
                if ($have_id)
                    $rs->orderBy('id', 'ASC');
                foreach ($rs->get()->getResult() as $key => $value) {
                    $file .= "[        " . PHP_EOL;
                    foreach ($select_fields as $key => $f) {
                        $file .= "   '$f' => '" . addslashes($value->{$f}) . "'," . PHP_EOL;
                    }
                    $file .= "],        " . PHP_EOL;
                }
            } catch (\Throwable $th) {
                throw $th;
            }
            $file .= "        " . PHP_EOL;
            $file .= "        ];" . PHP_EOL;

            $file .= "    }" . PHP_EOL;
            $file .= "" . PHP_EOL;
            $file .= "" . PHP_EOL;
            $file .= "    function onGet(\$name)" . PHP_EOL;
            $file .= "    {" . PHP_EOL;
            $file .= "        switch(\$name){" . PHP_EOL;
            $file .= "            case '':" . PHP_EOL;
            $file .= "                return '';" . PHP_EOL;
            $file .= "                break;" . PHP_EOL;
            $file .= "        }" . PHP_EOL;
            $file .= "    }" . PHP_EOL;
            $file .= "" . PHP_EOL;
            $file .= "}" . PHP_EOL;
            file_put_contents("../app/Models/$className.php", $file);
            // file_put_contents("../debug/$className.php", $file);
        }
    }
    function seed($p)
    {

        $path = '../app/Models';
        $fqcns = array();
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
                    $index += 2;
                    if ($p[1] ?? false) {
                        if (strtolower($tokens[$index][1]) == $p[1] ?? '') {
                            $fqcns[] = $namespace . '\\' . $tokens[$index][1];
                        }
                    } else {
                        $fqcns[] = $namespace . '\\' . $tokens[$index][1];
                    }
                    break;
                }
            }
        }
        $count = count($fqcns);
        foreach ($fqcns as $key => $value) {
            $fqn = $value;
            if (!class_exists($fqn))
                CLI::error("The class $fqn not found");
            $c = new $fqn();
            CLI::clearScreen();
            CLI::print("Seed key $fqn");
            CLI::newLine();
            CLI::showProgress($key, $count);
            $c->run_seed();
        }
    }
    function truncate($p)
    {
        $path = '../app/Models';
        $fqcns = array();
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
                    $index += 2;
                    if ($p[1] ?? false) {
                        if (strtolower($tokens[$index][1]) == $p[1] ?? '') {
                            $fqcns[] = $namespace . '\\' . $tokens[$index][1];
                        }
                    } else {
                        $fqcns[] = $namespace . '\\' . $tokens[$index][1];
                    }
                    break;
                }
            }
        }
        $db = db_connect();
        $count = count($fqcns);
        foreach ($fqcns as $key => $value) {
            $fqn = $value;
            if (!class_exists($fqn))
                CLI::error("The class $fqn not found");
            $c = new $fqn();
            CLI::clearScreen();
            CLI::print("Truncate key $fqn");
            CLI::newLine();
            CLI::showProgress($key, $count);
            $table = $c->get_table();
            $db->query("truncate table $table");
        }
    }
    function up()
    {
        xdebug_break();
        cache()->delete('startci_models_create');
        // for ($i = 0; $i < 10; $i++) {

        //     try {
        //         $database = env('database.default.database');
        //         $config = [
        //             'hostname' => env('database.default.hostname'),
        //             'username' => env('database.default.username'),
        //             'password' => env('database.default.password'),
        //             'DBDriver' => env('database.default.DBDriver'),
        //             'DBPrefix' => env('database.default.DBPrefix'),
        //             'port' => env('database.default.port'),
        //         ];
        //         \Config\Database::forge($config)->createDatabase($database, true);
        //         break;
        //     } catch (\Throwable $th) {
        //         //throw $th;
        //     }
        //     sleep(1);
        // }
        // for ($i = 0; $i < 10; $i++) {
        //     try {
        //         $con = @db_connect();
        //         @$con->simpleQuery("SET foreign_key_checks = 0");
        //         break;
        //     } catch (\Throwable $th) {
        //     }
        //     sleep(1);

        // }
        
        $con = db_connect();
        $con->disableForeignKeyChecks();
        $con->transBegin();
        $path = '../app/Models';
        $fqcns = array();
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
                    $index += 2;
                    $fqcns[] = $namespace . '\\' . $tokens[$index][1];
                    break;
                }
            }
        }

        $count = count($fqcns);
        foreach ($fqcns as $key => $value) { //pegar todas as classes e namespaces
            $fqn = $value;
            if (!class_exists($fqn))
                CLI::error("The class $fqn not found");

            $c = new $fqn();


            try {
                CLI::clearScreen();
                CLI::print("Table  $fqn");
                CLI::newLine();
                CLI::showProgress($key, $count);

                $c->create(false);
            } catch (\Throwable $th) {
                (is_cli()) ? eval(\Psy\sh()) : false;

                CLI::error(db_connect()->getLastQuery() . '');
                throw $th;
            } finally {
            }
        }


        foreach ($fqcns as $key => $value) {
            $fqn = $value;
            if (!class_exists($fqn))
                CLI::error("The class $fqn not found");
            $c = new $fqn();
            CLI::clearScreen();
            CLI::print("Foreign key $fqn");
            CLI::newLine();
            CLI::showProgress($key, $count);
            $c->create(true);
        }
        try {
            $con->simpleQuery("SET foreign_key_checks = 1");
        } catch (\Throwable $th) {
            //throw $th;
        }
        $con->transCommit();
    }
}

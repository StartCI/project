<?php
use Config\Database;
include 'vendor/autoload.php';
include 'vendor/codeigniter4/framework/system/Test/bootstrap.php';
if (!file_exists('writable'))
    mkdir('writable');
$db = db_connect();
$sql = <<<SQL
    CREATE TABLE if not exists usuarios (
        id INTEGER PRIMARY KEY,
        nome TEXT NOT NULL,
        email TEXT NOT NULL,
        telefone TEXT,
        created DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated DATETIME DEFAULT CURRENT_TIMESTAMP
    )
SQL;
$db->query($sql);
$db->table('usuarios')->insert([
    'nome' => 'Felipe',
    'email' => 'felipe@newbgp.com.br',
    'telefone' => '999999999',
]);
db_connect()->table('usuarios');
$resultado = table('usuarios')->rs();
var_dump($resultado);
if (!file_exists('writable'))
    unlink('writable');

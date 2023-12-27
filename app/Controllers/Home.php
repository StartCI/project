<?php

namespace App\Controllers;
use CodeIgniter\Controller;



class Home extends Controller
{
    public function index()
    {
        table('teste','tests')->create([
'nome' => 'text'
        ]);
        return 'oi';
    }
}

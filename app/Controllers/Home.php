<?php

namespace App\Controllers;

use CodeIgniter\Controller;



class Home extends Controller
{
    public function index()
    {
        $nome = faker();
        // table('teste')->replace([
        //     'nome' => $nome->name()
        // ]);
        return $this->response->setJSON(model_teste()->get());
    }
}

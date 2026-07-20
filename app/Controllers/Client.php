<?php

namespace App\Controllers;

class Client extends BaseController
{
    public function login(): string
    {
        return view('Client/login');
    }
    
    public function solde()
    {
        return view('Client/Solde');
    }

    public function depot()
    {
        return view('Client/Depot');
    }

    public function retrait()
    {
        return view('Client/Retrait');
    }

    public function transfert()
    {
        return view('Client/Transfert');
    }
}

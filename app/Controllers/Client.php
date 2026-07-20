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

    public function historique()
    {
        $clientId = session()->get('user_id');
        if (! $clientId) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $transactions = $db->table('transactions')
            ->select('transactions.*, transaction_type.label, transaction_type.code')
            ->join('transaction_type', 'transaction_type.id = transactions.transaction_type_id', 'left')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        return view('Client/Historique', ['transactions' => $transactions]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}

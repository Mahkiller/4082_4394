<?php

namespace App\Controllers;

class Transaction extends BaseController
{
    public function faireDepot()
    {
        $montant = $this->request->getPost('montant');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 1;

        // Récupérer le frais correspondant au montant depuis la table 'montant'
        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;
        $nouveauSolde = session()->get('user_solde') + $montant - $fraisTransaction;

        // MAJ dans la session
        session()->set('user_solde', $nouveauSolde);

        // MAJ dans la base de données
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSolde]);
        return redirect()->to('/solde');
    }

    public function faireRetrait()
    {
        $montant = $this->request->getPost('montant');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 2;

        // Récupérer le frais correspondant au montant depuis la table 'montant'
        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;
        if ($montant + $fraisTransaction > session()->get('user_solde')) {
            // Solde insuffisant, vous pouvez gérer cette situation comme vous le souhaitez
            return redirect()->to('/retrait')->with('error', 'Solde insuffisant pour effectuer ce retrait.');
        }else {
        $nouveauSolde = session()->get('user_solde') - $montant - $fraisTransaction;

        // MAJ dans la session
        session()->set('user_solde', $nouveauSolde);

        // MAJ dans la base de données
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSolde]);
        return redirect()->to('/solde');
    }
    }

    public function faireTransfert()
    {
        $montant = $this->request->getPost('montant');
        $destinataire = $this->request->getPost('destinataire');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 3;

        // Récupérer le frais correspondant au montant depuis la table 'montant'
        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;

        // Vérifier le solde suffisant (montant + frais)
        if ($montant + $fraisTransaction > session()->get('user_solde')) {
            return redirect()->to('/transfert')->with('error', 'Solde insuffisant pour effectuer ce transfert.');
        }

        // Récupérer le destinataire
        $destinataireRow = $db->table('clients')
            ->where('numero', $destinataire)
            ->get()
            ->getRow();

        if (!$destinataireRow) {
            return redirect()->to('/transfert')->with('error', 'Le numéro du destinataire est invalide.');
        }

        if ($destinataireRow->id == $clientId) {
            return redirect()->to('/transfert')->with('error', 'Vous ne pouvez pas vous transférer de l\'argent à vous-même.');
        }

        $nouveauSoldeEmetteur = session()->get('user_solde') - $montant - $fraisTransaction;
        $nouveauSoldeDestinataire = $destinataireRow->solde + $montant;

        // Générer une référence unique
        $reference = 'TXN-' . date('Ymd') . '-' . random_int(1000, 9999);

        // Démarrer une transaction
        $db->transStart();

        // MAJ solde émetteur dans la session et la base
        session()->set('user_solde', $nouveauSoldeEmetteur);
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSoldeEmetteur]);

        // MAJ solde destinataire dans la base
        $db->table('clients')
            ->where('id', $destinataireRow->id)
            ->update(['solde' => $nouveauSoldeDestinataire]);

        // Enregistrer la transaction
        $db->table('transactions')->insert([
            'client_id' => $clientId,
            'transaction_type_id' => $typeTransaction,
            'montant' => $montant,
            'frais_applique' => $fraisTransaction,
            'montant_net' => $montant,
            'reference' => $reference,
            'status' => 'Reussi',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/transfert')->with('error', 'Une erreur est survenue lors du transfert.');
        }

        return redirect()->to('/solde')->with('success', 'Transfert effectué avec succès.');
    }
}

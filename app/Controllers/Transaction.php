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

        $nouveauSolde = session()->get('user_solde') + $montant;

        // MAJ dans la session
        session()->set('user_solde', $nouveauSolde);

        // MAJ dans la base de données
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSolde]);

        // Enregistrer la transaction
        $reference = 'TXN-' . date('Ymd') . '-' . random_int(1000, 9999);
        $db->table('transactions')->insert([
            'client_id' => $clientId,
            'transaction_type_id' => $typeTransaction,
            'montant' => $montant,
            'frais_applique' => 0,
            'montant_net' => $montant,
            'reference' => $reference,
            'status' => 'Reussi',
        ]);

        return redirect()->to('/solde')->with('success', 'Dépôt effectué avec succès.');
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

        // Enregistrer la transaction
        $reference = 'TXN-' . date('Ymd') . '-' . random_int(1000, 9999);
        $db->table('transactions')->insert([
            'client_id' => $clientId,
            'transaction_type_id' => $typeTransaction,
            'montant' => $montant,
            'frais_applique' => $fraisTransaction,
            'montant_net' => $montant - $fraisTransaction,
            'reference' => $reference,
            'status' => 'Reussi',
        ]);

        return redirect()->to('/solde')->with('success', 'Retrait effectué avec succès.');
    }
    }

    public function faireTransfert()
    {
        $montant = $this->request->getPost('montant');
        $destinataire = $this->request->getPost('destinataire');
        $fraisMode = $this->request->getPost('frais_mode') ?? 'deductible';
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

        // Vérifier que le mode "frais inclus" n'est utilisé que pour le même opérateur
        if ($fraisMode === 'inclus') {
            $expediteur = $db->table('clients')->where('id', $clientId)->get()->getRow();
            $prefixeExpediteur = substr($expediteur->numero, 0, 3);
            $operateurExpediteur = $db->table('operateur_prefixe')
                ->where('prefixe', $prefixeExpediteur)
                ->get()
                ->getRow();

            $prefixeDest = substr($destinataireRow->numero, 0, 3);
            $operateurDest = $db->table('operateur_prefixe')
                ->where('prefixe', $prefixeDest)
                ->get()
                ->getRow();

            if (!$operateurDest || $operateurDest->operateur_id != $operateurExpediteur->operateur_id) {
                $fraisMode = 'deductible';
            }
        }

        if ($fraisMode === 'inclus') {
            // Frais inclus dans l'envoi : le montant inclut les frais
            $montantEnvoye = $montant - $fraisTransaction;
            $coutTotal = $montant;
        } else {
            // Frais déductibles de l'expéditeur (par défaut)
            $montantEnvoye = $montant;
            $coutTotal = $montant + $fraisTransaction;
        }

        if ($montantEnvoye < 100) {
            return redirect()->to('/transfert')->with('error', 'Le montant net par destinataire doit être d\'au moins 100 Ar.');
        }

        // Vérifier le solde suffisant
        if ($coutTotal > session()->get('user_solde')) {
            return redirect()->to('/transfert')->with('error', 'Solde insuffisant pour effectuer ce transfert.');
        }

        $nouveauSoldeEmetteur = session()->get('user_solde') - $coutTotal;
        $nouveauSoldeDestinataire = $destinataireRow->solde + $montantEnvoye;

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
            'montant' => $montantEnvoye,
            'frais_applique' => $fraisTransaction,
            'montant_net' => $montantEnvoye,
            'reference' => $reference,
            'status' => 'Reussi',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/transfert')->with('error', 'Une erreur est survenue lors du transfert.');
        }

        $fraisInfo = $fraisMode === 'inclus'
            ? "Frais de {$fraisTransaction} Ar inclus dans l'envoi."
            : "Frais de {$fraisTransaction} Ar déductibles de votre solde.";
        return redirect()->to('/solde')->with('success', "Transfert effectué avec succès. {$montantEnvoye} Ar envoyé au destinataire. {$fraisInfo}");
    }

    public function faireTransfertMultiple()
    {
        $montantTotal = $this->request->getPost('montant_total');
        $destinatairesRaw = $this->request->getPost('destinataires');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 3;

        // Parse les numéros de destinataires
        $numeros = array_filter(array_map('trim', explode(',', $destinatairesRaw)), fn($n) => $n !== '');
        $nombreDestinataires = count($numeros);

        if ($nombreDestinataires === 0) {
            return redirect()->to('/transfert')->with('error', 'Veuillez indiquer au moins un numéro de destinataire.');
        }

        $montantParDestinataire = floor($montantTotal / $nombreDestinataires);

        if ($montantParDestinataire < 100) {
            return redirect()->to('/transfert')->with('error', 'Le montant par destinataire doit être d\'au moins 100 Ar.');
        }

        // Récupérer le frais correspondant au montant total depuis la table 'montant'
        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montantTotal)
            ->where('max_montant >=', $montantTotal)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;

        // Vérifier le solde suffisant (montant total + frais)
        if ($montantTotal + $fraisTransaction > session()->get('user_solde')) {
            return redirect()->to('/transfert')->with('error', 'Solde insuffisant pour effectuer ce transfert multiple.');
        }

        // Récupérer l'opérateur de l'expéditeur
        $expediteur = $db->table('clients')->where('id', $clientId)->get()->getRow();
        $prefixeExpediteur = substr($expediteur->numero, 0, 3);
        $operateurExpediteur = $db->table('operateur_prefixe')
            ->where('prefixe', $prefixeExpediteur)
            ->get()
            ->getRow();

        if (!$operateurExpediteur) {
            return redirect()->to('/transfert')->with('error', 'Opérateur de l\'expéditeur introuvable.');
        }

        // Récupérer tous les préfixes du même opérateur que l'expéditeur
        $prefixesOperateur = $db->table('operateur_prefixe')
            ->where('operateur_id', $operateurExpediteur->operateur_id)
            ->get()
            ->getResult();

        $prefixesAutorises = array_column($prefixesOperateur, 'prefixe');

        // Récupérer tous les destinataires et vérifier l'opérateur
        $destinataires = [];
        foreach ($numeros as $numero) {
            $dest = $db->table('clients')->where('numero', $numero)->get()->getRow();
            if (!$dest) {
                return redirect()->to('/transfert')->with('error', "Le numéro {$numero} est invalide.");
            }
            if ($dest->id == $clientId) {
                return redirect()->to('/transfert')->with('error', "Vous ne pouvez pas vous transférer de l'argent à vous-même ({$numero}).");
            }

            $prefixeDest = substr($dest->numero, 0, 3);

            if (!in_array($prefixeDest, $prefixesAutorises)) {
                return redirect()->to('/transfert')->with('error', "Le numéro {$numero} n'appartient pas au même opérateur que vous. Le transfert multiple n'est disponible que pour le même opérateur.");
            }

            $destinataires[] = $dest;
        }

        $nouveauSoldeEmetteur = session()->get('user_solde') - $montantTotal - $fraisTransaction;

        // Démarrer une transaction
        $db->transStart();

        // MAJ solde émetteur dans la session et la base
        session()->set('user_solde', $nouveauSoldeEmetteur);
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSoldeEmetteur]);

        // Pour chaque destinataire
        foreach ($destinataires as $dest) {
            $nouveauSoldeDestinataire = $dest->solde + $montantParDestinataire;

            // MAJ solde destinataire
            $db->table('clients')
                ->where('id', $dest->id)
                ->update(['solde' => $nouveauSoldeDestinataire]);

            // Enregistrer la transaction
            $reference = 'TXN-' . date('Ymd') . '-' . random_int(1000, 9999);
            $db->table('transactions')->insert([
                'client_id' => $clientId,
                'transaction_type_id' => $typeTransaction,
                'montant' => $montantParDestinataire,
                'frais_applique' => 0,
                'montant_net' => $montantParDestinataire,
                'reference' => $reference,
                'status' => 'Reussi',
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/transfert')->with('error', 'Une erreur est survenue lors du transfert multiple.');
        }

        return redirect()->to('/solde')->with('success', "Transfert multiple effectué avec succès. {$montantParDestinataire} Ar envoyé à {$nombreDestinataires} destinataire(s).");
    }
}

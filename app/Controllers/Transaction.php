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

        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;
        $nouveauSolde = session()->get('user_solde') + $montant - $fraisTransaction;

        session()->set('user_solde', $nouveauSolde);

        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSolde]);

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

        return redirect()->to('/solde')->with('success', 'Dépôt effectué avec succès.');
    }

    public function faireRetrait()
    {
        $montant = $this->request->getPost('montant');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 2;

        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;
        if ($montant + $fraisTransaction > session()->get('user_solde')) {
            return redirect()->to('/retrait')->with('error', 'Solde insuffisant pour effectuer ce retrait.');
        }

        $nouveauSolde = session()->get('user_solde') - $montant - $fraisTransaction;

        session()->set('user_solde', $nouveauSolde);

        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSolde]);

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

    public function faireTransfert()
    {
        $montant = (float) $this->request->getPost('montant');
        $destinataire = trim($this->request->getPost('destinataire') ?? '');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 3;

        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? (float) $row->frais_montant : 0;
        $fraisMode = $this->request->getPost('frais_mode') ?? 'deductible';

        if ($montant + $fraisTransaction > session()->get('user_solde')) {
            return redirect()->to('/transfert')->with('error', 'Solde insuffisant pour effectuer ce transfert.');
        }

        $emetteur = $db->table('clients')->where('id', $clientId)->get()->getRow();
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

        $sourcePrefixe = substr($emetteur->numero, 0, 3);
        $sourceOpRow = $db->table('operateur_prefixe')
            ->where('prefixe', $sourcePrefixe)
            ->get()
            ->getRow();
        $sourceOpId = $sourceOpRow ? (int) $sourceOpRow->operateur_id : null;

        $destPrefixe = substr($destinataireRow->numero, 0, 3);
        $destOpRow = $db->table('operateur_prefixe')
            ->where('prefixe', $destPrefixe)
            ->get()
            ->getRow();
        $destOpId = $destOpRow ? (int) $destOpRow->operateur_id : null;

        $commission = 0.0;
        if ($sourceOpId && $destOpId && $sourceOpId != $destOpId) {
            $com = $db->table('commission')
                ->where('operateur_source_id', $sourceOpId)
                ->where('operateur_destinataire_id', $destOpId)
                ->where('est_actif', 1)
                ->get()
                ->getRow();
            if ($com) {
                $commission = (float) $com->pourcentage / 100 * $montant;
            }
        }

        if ($fraisMode === 'inclus') {
            $montantRecu = max(0, $montant - $fraisTransaction);
            $totalDebit = $montant + $commission;
        } else {
            $montantRecu = $montant;
            $totalDebit = $montant + $fraisTransaction + $commission;
        }

        if ($totalDebit > session()->get('user_solde')) {
            return redirect()->to('/transfert')->with('error', 'Solde insuffisant pour effectuer ce transfert.');
        }

        $nouveauSoldeEmetteur = session()->get('user_solde') - $totalDebit;
        $nouveauSoldeDestinataire = $destinataireRow->solde + $montantRecu;

        $reference = 'TXN-' . date('Ymd') . '-' . random_int(1000, 9999);

        $db->transStart();

        session()->set('user_solde', $nouveauSoldeEmetteur);
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSoldeEmetteur]);

        $db->table('clients')
            ->where('id', $destinataireRow->id)
            ->update(['solde' => $nouveauSoldeDestinataire]);

        $db->table('transactions')->insert([
            'client_id' => $clientId,
            'transaction_type_id' => $typeTransaction,
            'montant' => $montant,
            'frais_applique' => $fraisTransaction,
            'montant_net' => $montantRecu,
            'commission' => $commission,
            'destinataire_numero' => $destinataire,
            'reference' => $reference,
            'status' => 'Reussi',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/transfert')->with('error', 'Une erreur est survenue lors du transfert.');
        }

        return redirect()->to('/solde')->with('success', 'Transfert effectué avec succès.');
    }

    public function calculFrais()
    {
        $type = (int) $this->request->getPost('type');
        $montant = (float) $this->request->getPost('montant');
        $destinataire = trim($this->request->getPost('destinataire') ?? '');
        $fraisMode = $this->request->getPost('frais_mode') ?? 'deductible';
        $db = \Config\Database::connect();

        $row = $db->table('montant')
            ->where('transaction_type_id', $type)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? (float) $row->frais_montant : 0;
        $commission = 0.0;
        $montantRecu = $montant;
        $totalDebit = $montant + $fraisTransaction;

        if ($type == 3 && !empty($destinataire)) {
            $emetteur = $db->table('clients')->where('id', session()->get('user_id'))->get()->getRow();
            if ($emetteur) {
                $sourcePrefixe = substr($emetteur->numero, 0, 3);
                $sourceOpRow = $db->table('operateur_prefixe')->where('prefixe', $sourcePrefixe)->get()->getRow();
                $destRow = $db->table('clients')->where('numero', $destinataire)->get()->getRow();
                if ($destRow) {
                    $destPrefixe = substr($destRow->numero, 0, 3);
                    $destOpRow = $db->table('operateur_prefixe')->where('prefixe', $destPrefixe)->get()->getRow();
                    $sourceOpId = $sourceOpRow ? (int) $sourceOpRow->operateur_id : null;
                    $destOpId = $destOpRow ? (int) $destOpRow->operateur_id : null;
                    if ($sourceOpId && $destOpId && $sourceOpId != $destOpId) {
                        $com = $db->table('commission')
                            ->where('operateur_source_id', $sourceOpId)
                            ->where('operateur_destinataire_id', $destOpId)
                            ->where('est_actif', 1)
                            ->get()
                            ->getRow();
                        if ($com) {
                            $commission = (float) $com->pourcentage / 100 * $montant;
                        }
                    }
                }
            }

            if ($fraisMode === 'inclus') {
                $montantRecu = max(0, $montant - $fraisTransaction);
                $totalDebit = $montant + $commission;
            } else {
                $totalDebit = $montant + $fraisTransaction + $commission;
            }
        }

        return $this->response->setJSON([
            'frais' => $fraisTransaction,
            'commission' => $commission,
            'montant_recu' => $montantRecu,
            'total_debit' => $totalDebit,
        ]);
    }

    public function faireTransfertMultiple()
    {
        $montantTotal = $this->request->getPost('montant_total');
        $destinatairesRaw = $this->request->getPost('destinataires');
        $db = \Config\Database::connect();
        $clientId = session()->get('user_id');
        $typeTransaction = 3;

        $numeros = array_filter(array_map('trim', explode(',', $destinatairesRaw)), fn($n) => $n !== '');
        $nombreDestinataires = count($numeros);

        if ($nombreDestinataires === 0) {
            return redirect()->to('/transfert')->with('error', 'Veuillez indiquer au moins un numéro de destinataire.');
        }

        $montantParDestinataire = floor($montantTotal / $nombreDestinataires);

        if ($montantParDestinataire < 100) {
            return redirect()->to('/transfert')->with('error', 'Le montant par destinataire doit être d\'au moins 100 Ar.');
        }

        $row = $db->table('montant')
            ->where('transaction_type_id', $typeTransaction)
            ->where('min_montant <=', $montantTotal)
            ->where('max_montant >=', $montantTotal)
            ->get()
            ->getRow();

        $fraisTransaction = $row ? $row->frais_montant : 0;
        $fraisMode = $this->request->getPost('frais_mode') ?? 'deductible';

        if ($montantTotal + $fraisTransaction > session()->get('user_solde')) {
            return redirect()->to('/transfert')->with('error', 'Solde insuffisant pour effectuer ce transfert multiple.');
        }

        $expediteur = $db->table('clients')->where('id', $clientId)->get()->getRow();
        $prefixeExpediteur = substr($expediteur->numero, 0, 3);
        $operateurExpediteur = $db->table('operateur_prefixe')
            ->where('prefixe', $prefixeExpediteur)
            ->get()
            ->getRow();

        if (!$operateurExpediteur) {
            return redirect()->to('/transfert')->with('error', 'Opérateur de l\'expéditeur introuvable.');
        }

        $prefixesOperateur = $db->table('operateur_prefixe')
            ->where('operateur_id', $operateurExpediteur->operateur_id)
            ->get()
            ->getResult();

        $prefixesAutorises = array_column($prefixesOperateur, 'prefixe');

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

        $db->transStart();

        session()->set('user_solde', $nouveauSoldeEmetteur);
        $db->table('clients')
            ->where('id', $clientId)
            ->update(['solde' => $nouveauSoldeEmetteur]);

        foreach ($destinataires as $dest) {
            if ($fraisMode === 'inclus') {
                $montantRecuParDest = max(0, $montantParDestinataire - $fraisTransaction);
            } else {
                $montantRecuParDest = $montantParDestinataire;
            }

            $nouveauSoldeDestinataire = $dest->solde + $montantRecuParDest;

            $db->table('clients')
                ->where('id', $dest->id)
                ->update(['solde' => $nouveauSoldeDestinataire]);

            $reference = 'TXN-' . date('Ymd') . '-' . random_int(1000, 9999);
            $db->table('transactions')->insert([
                'client_id' => $clientId,
                'transaction_type_id' => $typeTransaction,
                'montant' => $montantParDestinataire,
                'frais_applique' => $fraisTransaction,
                'montant_net' => $montantRecuParDest,
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

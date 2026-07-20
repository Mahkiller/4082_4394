<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\CommissionModel;
use App\Models\MontantModel;
use App\Models\OperateurModel;
use App\Models\OperateurPrefixeModel;
use App\Models\TransactionModel;
use App\Models\TransactionTypeModel;

class OperateurController extends BaseController
{
    protected OperateurModel $operateurModel;
    protected OperateurPrefixeModel $prefixeModel;
    protected TransactionTypeModel $typeModel;
    protected MontantModel $montantModel;
    protected ClientModel $clientModel;
    protected TransactionModel $transactionModel;
    protected CommissionModel $commissionModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->operateurModel    = new OperateurModel();
        $this->prefixeModel      = new OperateurPrefixeModel();
        $this->typeModel         = new TransactionTypeModel();
        $this->montantModel      = new MontantModel();
        $this->clientModel       = new ClientModel();
        $this->transactionModel  = new TransactionModel();
        $this->commissionModel   = new CommissionModel();

        helper(['form', 'url']);
    }

    public function index()
    {
        $dernieres = $this->transactionModel->builder()
            ->select('transactions.*, transaction_type.label AS type_label, clients.numero AS client_numero')
            ->join('transaction_type', 'transaction_type.id = transactions.transaction_type_id', 'left')
            ->join('clients', 'clients.id = transactions.client_id', 'left')
            ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
            ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
            ->where('operateur.nom', 'YAS')
            ->orderBy('transactions.created_at', 'DESC')
            ->limit(5)
            ->get()->getResult();

        $types = [];
        foreach ($this->typeModel->findAll() as $t) {
            $types[$t->id] = $t;
        }

        $nbClients = $this->clientModel->builder()
            ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
            ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
            ->where('operateur.nom', 'YAS')
            ->countAllResults();

        $nbTransac = $this->transactionModel->builder()
            ->join('clients', 'clients.id = transactions.client_id', 'left')
            ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
            ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
            ->where('operateur.nom', 'YAS')
            ->countAllResults();

        $data = [
            'nbPrefixes' => $this->prefixeModel->countAllResults(), // Pourrait être filtré sur YAS si besoin
            'nbTypes'    => $this->typeModel->countAllResults(),
            'nbClients'  => $nbClients,
            'nbTransac'  => $nbTransac,
            'gains'      => $this->totalGains(),
            'dernieres'  => $dernieres,
            'types'      => $types,
            'operateurs' => $this->operateurModel->findAll(),
        ];

        return view('Opérateur/dashboard', $data);
    }

    private function totalGains(): float
    {
        $builder = $this->transactionModel->builder()
            ->join('clients', 'clients.id = transactions.client_id', 'left')
            ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
            ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
            ->selectSum('frais_applique', 'total')
            ->where('status', 'Reussi')
            ->where('operateur.nom', 'YAS')
            ->whereIn('transactions.transaction_type_id', [2, 3]);
        $row = $builder->get()->getRow();
        return (float) ($row->total ?? 0);
    }

    public function prefixe()
    {
        $yas = $this->operateurModel->where('nom', 'YAS')->first();
        $prefixes  = $yas ? $this->operateurModel->getPrefixes($yas->id) : [];

        return view('Opérateur/prefixe', [
            // 'operateur' n'est plus nécessaire ici
            'prefixes'   => $prefixes,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function prefixeAjouter()
    {
        $operateur = $this->operateurModel->where('nom', 'YAS')->first();
        if (!$operateur) {
            return redirect()->back()->with('error', 'Opérateur YAS introuvable.');
        }

        $prefixe   = trim($this->request->getPost('prefixe') ?? '');

        if ($prefixe === '') {
            return redirect()->back()->with('error', 'Le préfixe est obligatoire.');
        }

        if ($this->prefixeModel->where('prefixe', $prefixe)->first()) {
            return redirect()->back()->with('error', "Le préfixe $prefixe existe déjà.");
        }

        $this->prefixeModel->insert([
            'operateur_id' => $operateur->id,
            'prefixe'      => $prefixe,
        ]);

        return redirect()->to('/operateur/prefixe')->with('success', "Préfixe $prefixe ajouté.");
    }

    public function prefixeSupprimer($id = null)
    {
        $this->prefixeModel->delete($id);
        return redirect()->to('/operateur/prefixe')->with('success', 'Préfixe supprimé.');
    }

    public function types()
    {
        $types = $this->typeModel->findAll();

        foreach ($types as $type) {
            $type->tranches = $this->montantModel->getByType($type->id);
        }

        return view('Opérateur/types', [
            'types'     => $types,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function typeAjouter()
    {
        $code  = strtoupper(trim($this->request->getPost('code') ?? ''));
        $label = trim($this->request->getPost('label') ?? '');

        if ($code === '' || $label === '') {
            return redirect()->back()->with('error', 'Code et libellé obligatoires.');
        }

        if ($this->typeModel->where('code', $code)->first()) {
            return redirect()->back()->with('error', "Le code $code existe déjà.");
        }

        $this->typeModel->insert(['code' => $code, 'label' => $label]);

        return redirect()->to('/operateur/types')->with('success', "Type $label ajouté.");
    }

    public function typeSupprimer($id = null)
    {
        $this->montantModel->where('transaction_type_id', $id)->delete();
        $this->typeModel->delete($id);
        return redirect()->to('/operateur/types')->with('success', 'Type supprimé.');
    }

    public function montants($typeId = null)
    {
        $type = $this->typeModel->find($typeId);
        if (! $type) {
            return redirect()->to('/operateur/types');
        }

        $tranches = $this->montantModel->getByType($type->id);

        return view('Opérateur/montants', [
            'type'      => $type,
            'tranches'  => $tranches,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function montantAjouter()
    {
        $typeId       = (int) $this->request->getPost('transaction_type_id');
        $minMontant   = (float) $this->request->getPost('min_montant');
        $maxMontant   = (float) $this->request->getPost('max_montant');
        $fraisMontant = (float) $this->request->getPost('frais_montant');

        if ($minMontant > $maxMontant) {
            return redirect()->back()->with('error', 'Le montant min doit être <= au montant max.');
        }

        $this->montantModel->insert([
            'transaction_type_id' => $typeId,
            'min_montant'         => $minMontant,
            'max_montant'         => $maxMontant,
            'frais_montant'       => $fraisMontant,
        ]);

        return redirect()->to("/operateur/montants/$typeId")->with('success', 'Tranche ajoutée.');
    }

    public function montantModifier($id = null)
    {
        $tranche = $this->montantModel->find($id);
        if (! $tranche) {
            return redirect()->to('/operateur/types');
        }

        $minMontant   = (float) $this->request->getPost('min_montant');
        $maxMontant   = (float) $this->request->getPost('max_montant');
        $fraisMontant = (float) $this->request->getPost('frais_montant');

        if ($minMontant > $maxMontant) {
            return redirect()->back()->with('error', 'Le montant min doit être <= au montant max.');
        }

        $this->montantModel->update($id, [
            'min_montant'   => $minMontant,
            'max_montant'   => $maxMontant,
            'frais_montant' => $fraisMontant,
        ]);

        return redirect()->to("/operateur/montants/{$tranche->transaction_type_id}")
            ->with('success', 'Tranche mise à jour.');
    }

    public function montantSupprimer($id = null)
    {
        $tranche = $this->montantModel->find($id);
        if (! $tranche) {
            return redirect()->to('/operateur/types');
        }
        $typeId = $tranche->transaction_type_id;
        $this->montantModel->delete($id);
        return redirect()->to("/operateur/montants/$typeId")->with('success', 'Tranche supprimée.');
    }

    public function gains()
    {
        $types = $this->typeModel->findAll();
        $detailsYas = [];
        $detailsAutres = [];
        $totalYas = 0;
        $totalAutres = 0;
        $totalCommissions = 0;

        foreach ($types as $type) {
            // Gains YAS
            $builderYas = $this->transactionModel->builder()
                ->join('clients', 'clients.id = transactions.client_id', 'left')
                ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
                ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left');
            $builderYas->selectSum('frais_applique', 'total')
                ->selectSum('montant', 'volume')
                ->where('transactions.transaction_type_id', $type->id)
                ->where('operateur.nom', 'YAS')
                ->where('status', 'Reussi');
            $rowYas = $builderYas->get()->getRow();
            $gainYas = (float) ($rowYas->total ?? 0);
            $volumeYas = (float) ($rowYas->volume ?? 0);
            $totalYas += $gainYas;

            // Gains autres opérateurs (frais)
            $builderAutres = $this->transactionModel->builder()
                ->join('clients', 'clients.id = transactions.client_id', 'left')
                ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
                ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left');
            $builderAutres->selectSum('frais_applique', 'total')
                ->selectSum('montant', 'volume')
                ->where('transactions.transaction_type_id', $type->id)
                ->where('operateur.nom !=', 'YAS')
                ->where('status', 'Reussi');
            $rowAutres = $builderAutres->get()->getRow();
            $gainAutres = (float) ($rowAutres->total ?? 0);
            $volumeAutres = (float) ($rowAutres->volume ?? 0);

            // Commissions vers autres opérateurs (uniquement pour transferts)
            $commissionAutres = 0;
            if ($type->id == 3) {
                $builderCom = $this->transactionModel->builder()
                    ->join('clients', 'clients.id = transactions.client_id', 'left')
                    ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
                    ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left');
                $builderCom->selectSum('commission', 'total_com')
                    ->where('transactions.transaction_type_id', $type->id)
                    ->where('operateur.nom !=', 'YAS')
                    ->where('status', 'Reussi');
                $rowCom = $builderCom->get()->getRow();
                $commissionAutres = (float) ($rowCom->total_com ?? 0);
            }

            $gainAutresTotal = $gainAutres + $commissionAutres;
            $totalAutres += $gainAutresTotal;
            $totalCommissions += $commissionAutres;

            $detailsYas[] = [
                'type' => $type,
                'gain' => $gainYas,
                'volume' => $volumeYas,
            ];
            $detailsAutres[] = [
                'type' => $type,
                'gain' => $gainAutres,
                'volume' => $volumeAutres,
                'commission' => $commissionAutres,
                'gain_total' => $gainAutresTotal,
            ];
        }

        return view('Opérateur/gains', [
            'detailsYas'     => $detailsYas,
            'detailsAutres'  => $detailsAutres,
            'totalYas'       => $totalYas,
            'totalAutres'    => $totalAutres,
            'totalCommissions' => $totalCommissions,
            'operateurs'     => $this->operateurModel->findAll(),
        ]);
    }

    public function montantsEnvoyes()
    {
        $db = \Config\Database::connect();

        $operateurs = $db->table('operateur')
            ->where('nom !=', 'YAS')
            ->get()
            ->getResult();

        $resultats = [];
        foreach ($operateurs as $op) {
            // Montant total envoye PAR YAS VERS cet operateur (destinataire commence par un prefixe de cet operateur)
            $destPrefixes = $db->table('operateur_prefixe')
                ->where('operateur_id', $op->id)
                ->get()
                ->getResult();
            $destPrefixesList = array_column($destPrefixes, 'prefixe');

            $envoye = 0;
            if (!empty($destPrefixesList)) {
                $builderEnvoye = $db->table('transactions')
                    ->join('clients', 'clients.id = transactions.client_id', 'left')
                    ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
                    ->where('operateur_prefixe.operateur_id', 1)
                    ->where('transactions.transaction_type_id', 3)
                    ->where('status', 'Reussi')
                    ->groupStart();
                foreach ($destPrefixesList as $p) {
                    $builderEnvoye->orLike('transactions.destinataire_numero', $p, 'after');
                }
                $builderEnvoye->groupEnd();
                $builderEnvoye->selectSum('montant', 'total_envoye');
                $rowEnvoye = $builderEnvoye->get()->getRow();
                $envoye = (float) ($rowEnvoye->total_envoye ?? 0);
            }

            // Montant total recu PAR YAS DEPUIS cet operateur (emetteur appartient a cet operateur)
            $recu = $db->table('transactions')
                ->join('clients', 'clients.id = transactions.client_id', 'left')
                ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
                ->where('operateur_prefixe.operateur_id', $op->id)
                ->where('transactions.transaction_type_id', 3)
                ->where('status', 'Reussi')
                ->selectSum('montant', 'total_recu')
                ->get()
                ->getRow();
            $totalRecu = (float) ($recu->total_recu ?? 0);

            $resultats[] = [
                'operateur' => $op,
                'total_envoye' => $envoye,
                'total_recu' => $totalRecu,
                'net' => $envoye - $totalRecu,
            ];
        }

        return view('Opérateur/montants_envoyes', [
            'resultats' => $resultats,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function comptes()
    {
        $clients = $this->clientModel
            ->select('clients.*, operateur.nom as operateurNom, COUNT(transactions.id) as nbTransac')
            ->join('operateur_prefixe', 'SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe', 'left')
            ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
            ->join('transactions', 'transactions.client_id = clients.id', 'left')
            ->where('operateur.nom', 'YAS')
            ->groupBy('clients.id, operateur.nom')
            ->orderBy('clients.numero', 'ASC')
            ->findAll();

        $totalSolde = array_sum(array_column($clients, 'solde'));

        return view('Opérateur/comptes', [
            'clients'    => $clients,
            'totalSolde' => $totalSolde,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    private function operateurDuClient(string $numero): string
    {
        $prefixe = substr($numero, 0, 3);
        $op = $this->prefixeModel->getOperateurFromPrefixe($prefixe);
        return $op->nom ?? 'Inconnu';
    }

    public function clientAjouter()
    {
        $yas = $this->operateurModel->where('nom', 'YAS')->first();
        $prefixes  = $yas ? $this->operateurModel->getPrefixes($yas->id) : [];

        return view('Opérateur/client_ajouter', [
            'prefixes'  => $prefixes,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function clientStore()
    {
        $numero = trim($this->request->getPost('numero') ?? '');
        $solde  = (float) ($this->request->getPost('solde') ?? 0);

        if ($numero === '') {
            return redirect()->back()->withInput()->with('error', 'Le numéro de téléphone est obligatoire.');
        }

        if (! preg_match('/^[0-9]{9,10}$/', $numero)) {
            return redirect()->back()->withInput()->with('error', 'Le numéro doit contenir 9 à 10 chiffres.');
        }

        if ($this->clientModel->where('numero', $numero)->first()) {
            return redirect()->back()->withInput()->with('error', "Le numéro $numero existe déjà.");
        }

        $this->clientModel->insert([
            'numero' => $numero,
            'solde'  => $solde,
        ]);

        return redirect()->to('/operateur/comptes')->with('success', "Client $numero ajouté.");
    }

    public function clientModifier($id = null)
    {
        $client = $this->clientModel->find($id);
        if (! $client) {
            return redirect()->to('/operateur/comptes')->with('error', 'Client introuvable.');
        }

        return view('Opérateur/client_modifier', [
            'client'    => $client,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function clientUpdate($id = null)
    {
        $client = $this->clientModel->find($id);
        if (! $client) {
            return redirect()->to('/operateur/comptes')->with('error', 'Client introuvable.');
        }

        $numero = trim($this->request->getPost('numero') ?? '');
        $solde  = (float) ($this->request->getPost('solde') ?? 0);

        if ($numero === '') {
            return redirect()->back()->withInput()->with('error', 'Le numéro de téléphone est obligatoire.');
        }

        if (! preg_match('/^[0-9]{9,10}$/', $numero)) {
            return redirect()->back()->withInput()->with('error', 'Le numéro doit contenir 9 à 10 chiffres.');
        }

        $doublon = $this->clientModel->where('numero', $numero)->where('id !=', $id)->first();
        if ($doublon) {
            return redirect()->back()->withInput()->with('error', "Le numéro $numero existe déjà.");
        }

        $this->clientModel->update($id, [
            'numero' => $numero,
            'solde'  => $solde,
        ]);

        return redirect()->to('/operateur/comptes')->with('success', "Client $numero mis à jour.");
    }

    public function clientSupprimer($id = null)
    {
        $client = $this->clientModel->find($id);
        if (! $client) {
            return redirect()->to('/operateur/comptes')->with('error', 'Client introuvable.');
        }

        $this->clientModel->delete($id, true);

        return redirect()->to('/operateur/comptes')->with('success', "Client {$client->numero} supprimé.");
    }

    public function clientDetail($id = null)
    {
        $client = $this->clientModel->find($id);
        if (! $client) {
            return redirect()->to('/operateur/comptes')->with('error', 'Client introuvable.');
        }

        $transactions = $this->transactionModel->builder()
            ->select('transactions.*, transaction_type.label, transaction_type.code')
            ->join('transaction_type', 'transaction_type.id = transactions.transaction_type_id', 'left')
            ->where('transactions.client_id', $id)
            ->orderBy('transactions.created_at', 'DESC')
            ->get()
            ->getResult();

        return view('Opérateur/client_detail', [
            'client' => $client,
            'transactions' => $transactions,
        ]);
    }

    public function commissions()
    {
        $commissions = $this->commissionModel->findAll();
        $operateurs = $this->operateurModel->findAll();

        foreach ($commissions as $com) {
            $com->sourceNom = $this->operateurModel->find($com->operateur_source_id)->nom ?? '?';
            $com->destNom = $this->operateurModel->find($com->operateur_destinataire_id)->nom ?? '?';
        }

        return view('Opérateur/commissions', [
            'commissions' => $commissions,
            'operateurs' => $operateurs,
        ]);
    }

    public function commissionAjouter()
    {
        $source = 1; // YAS uniquement
        $dest = (int) $this->request->getPost('operateur_destinataire_id');
        $pct = (float) $this->request->getPost('pourcentage');
        $desc = trim($this->request->getPost('description') ?? '');

        if ($source === $dest) {
            return redirect()->back()->with('error', 'Les opérateurs source et destinataire doivent être différents.');
        }

        $this->commissionModel->insert([
            'operateur_source_id' => $source,
            'operateur_destinataire_id' => $dest,
            'pourcentage' => $pct,
            'description' => $desc,
            'est_actif' => 1,
        ]);

        return redirect()->to('/operateur/commissions')->with('success', 'Commission ajoutée.');
    }

    public function commissionModifier($id = null)
    {
        $com = $this->commissionModel->find($id);
        if (! $com) {
            return redirect()->to('/operateur/commissions')->with('error', 'Commission introuvable.');
        }

        $pct = (float) $this->request->getPost('pourcentage');
        $desc = trim($this->request->getPost('description') ?? '');

        $this->commissionModel->update($id, [
            'pourcentage' => $pct,
            'description' => $desc,
        ]);

        return redirect()->to('/operateur/commissions')->with('success', 'Commission modifiée.');
    }

    public function commissionSupprimer($id = null)
    {
        $this->commissionModel->delete($id, true);
        return redirect()->to('/operateur/commissions')->with('success', 'Commission supprimée.');
    }
}

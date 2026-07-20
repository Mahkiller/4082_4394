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
        $types   = $this->typeModel->findAll();
        $details = [];
        $total   = 0;

        foreach ($types as $type) {
            $builder = $this->transactionModel->builder()
                ->join('clients', 'clients.id = transactions.client_id', 'left')
                ->join('operateur_prefixe', "SUBSTR(clients.numero, 1, 3) = operateur_prefixe.prefixe", 'left')
                ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left');
            $builder->selectSum('frais_applique', 'total')
                ->selectSum('montant', 'volume')
                ->where('transactions.transaction_type_id', $type->id)
                ->where('operateur.nom', 'YAS')
                ->where('status', 'Reussi');
            $row = $builder->get()->getRow();
            $gain = (float) ($row->total ?? 0);
            $volume = (float) ($row->volume ?? 0);
            $total += $gain;
            $details[] = [
                'type'   => $type,
                'gain'   => $gain,
                'volume' => $volume,
            ];
        }

        return view('Opérateur/gains', [
            'details'   => $details,
            'total'     => $total,
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

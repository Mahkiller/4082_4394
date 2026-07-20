<?php

namespace App\Controllers;

use App\Models\ClientModel;
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

        helper(['form', 'url']);
    }

    private function selectedOperateurId(): ?int
    {
        $id = session('operateur_id');
        if ($id === null || $id === 'all' || $id === '') {
            return null;
        }
        return (int) $id;
    }

    private function currentOperateur()
    {
        $id = $this->selectedOperateurId();
        if ($id === null) {
            return (object) ['id' => null, 'nom' => 'Tous les opérateurs'];
        }

        $operateur = $this->operateurModel->find($id);
        if (! $operateur) {
            return (object) ['id' => null, 'nom' => 'Tous les opérateurs'];
        }
        return $operateur;
    }

    private function concretOperateur(): object
    {
        $id = $this->selectedOperateurId();
        if ($id !== null) {
            $op = $this->operateurModel->find($id);
            if ($op) {
                return $op;
            }
        }
        $op = $this->operateurModel->first();
        if (! $op) {
            $newId = $this->operateurModel->insert(['nom' => 'YAS']);
            $op = $this->operateurModel->find($newId);
        }
        return $op;
    }

    private function prefixesFiltre(): array
    {
        $id = $this->selectedOperateurId();
        if ($id === null) {
            return [];
        }
        $rows = $this->operateurModel->getPrefixes($id);
        return array_map(fn($p) => $p->prefixe, $rows);
    }

    public function selectionner()
    {
        $id = $this->request->getPost('operateur_id') ?? $this->request->getGet('operateur_id');
        if ($id === 'all' || $id === '' || $id === null) {
            session()->remove('operateur_id');
        } else {
            session()->set('operateur_id', (int) $id);
        }

        $redirect = $this->request->getPost('redirect') ?? $this->request->getGet('redirect') ?? '/operateur';
        return redirect()->to($redirect);
    }

    private function applyOperateurFiltre($builder)
    {
        $prefixes = $this->prefixesFiltre();
        if (! empty($prefixes)) {
            $builder->groupStart();
            foreach ($prefixes as $p) {
                $builder->orLike('clients.numero', $p, 'after');
            }
            $builder->groupEnd();
        }
        return $builder;
    }

    public function index()
    {
        $operateur = $this->currentOperateur();
        $filtre = $this->prefixesFiltre();

        $builder = $this->transactionModel->builder()
            ->select('transactions.*, transaction_type.label AS type_label, clients.numero AS client_numero')
            ->join('transaction_type', 'transaction_type.id = transactions.transaction_type_id', 'left')
            ->join('clients', 'clients.id = transactions.client_id', 'left')
            ->orderBy('transactions.created_at', 'DESC')
            ->limit(5);
        $this->applyOperateurFiltre($builder);
        $dernieres = $builder->get()->getResult();

        $types = [];
        foreach ($this->typeModel->findAll() as $t) {
            $types[$t->id] = $t;
        }

        $nbClients = $this->clientModel->builder();
        $this->applyOperateurFiltre($nbClients);
        $nbClients = $nbClients->countAllResults();

        $nbTransac = $this->transactionModel->builder()
            ->join('clients', 'clients.id = transactions.client_id', 'left');
        $this->applyOperateurFiltre($nbTransac);
        $nbTransac = $nbTransac->countAllResults();

        $data = [
            'operateur'  => $operateur,
            'nbPrefixes' => $operateur->id ? $this->prefixeModel->where('operateur_id', $operateur->id)->countAllResults() : $this->prefixeModel->countAllResults(),
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
            ->join('clients', 'clients.id = transactions.client_id', 'left');
        $this->applyOperateurFiltre($builder);
        $builder->selectSum('frais_applique', 'total')
            ->where('status', 'Reussi')
            ->whereIn('transactions.transaction_type_id', [2, 3]);
        $row = $builder->get()->getRow();
        return (float) ($row->total ?? 0);
    }

    public function prefixe()
    {
        $operateur = $this->currentOperateur();
        $prefixes  = $this->operateurModel->getPrefixes($operateur->id);

        return view('Opérateur/prefixe', [
            'operateur'  => $operateur,
            'prefixes'   => $prefixes,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function prefixeAjouter()
    {
        $operateur = $this->concretOperateur();
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
            'operateur' => $this->currentOperateur(),
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
            'operateur' => $this->currentOperateur(),
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
                ->join('clients', 'clients.id = transactions.client_id', 'left');
            $this->applyOperateurFiltre($builder);
            $builder->selectSum('frais_applique', 'total')
                ->selectSum('montant', 'volume')
                ->where('transactions.transaction_type_id', $type->id)
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
            'operateur' => $this->currentOperateur(),
            'details'   => $details,
            'total'     => $total,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function comptes()
    {
        $builder = $this->clientModel->builder();
        $this->applyOperateurFiltre($builder);
        $clients = $builder->orderBy('numero', 'ASC')->get()->getResult();

        $totalSolde = 0;
        foreach ($clients as $client) {
            $nb = $this->transactionModel->builder()
                ->join('clients', 'clients.id = transactions.client_id', 'left')
                ->where('client_id', $client->id);
            $this->applyOperateurFiltre($nb);
            $client->nbTransac = $nb->countAllResults();
            $totalSolde += (float) $client->solde;
        }

        return view('Opérateur/comptes', [
            'operateur'  => $this->currentOperateur(),
            'clients'    => $clients,
            'totalSolde' => $totalSolde,
            'operateurs' => $this->operateurModel->findAll(),
        ]);
    }

    public function clientAjouter()
    {
        $operateur = $this->currentOperateur();
        $prefixes  = $this->operateurModel->getPrefixes($operateur->id);

        return view('Opérateur/client_ajouter', [
            'operateur' => $operateur,
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
            'operateur' => $this->currentOperateur(),
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
}

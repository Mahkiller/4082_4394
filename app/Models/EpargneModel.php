<?php

namespace App\Models;

use CodeIgniter\Model;

class EpargneModel extends Model
{
    protected $table            = 'epargnes';
    protected $idClient       = 'client_id';
    protected $pourcentageEpargne = 'pourcentage_epargne';
    protected $soldeEpargne = 'solde_epargne';
}


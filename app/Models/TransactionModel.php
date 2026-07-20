<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'client_id',
        'transaction_type_id',
        'montant',
        'frais_applique',
        'montant_net',
        'reference',
        'status',
    ];
    protected $useTimestamps    = false;
}

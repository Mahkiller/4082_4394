<?php

namespace App\Model;

use CodeIgniter\Model;

class promotions extends Model{
    protected $tables           = 'promotions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields= [
        'transaction_id',
        'operateur_id',
        'pourcentage',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    //public function getpromotions(int $transactionid): {}}

}
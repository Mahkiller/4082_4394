<?php

namespace App\Models;

use CodeIgniter\Model;

class MontantModel extends Model
{
    protected $table            = 'montant';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['transaction_type_id', 'min_montant', 'max_montant', 'frais_montant'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getByType(int $typeId)
    {
        return $this->where('transaction_type_id', $typeId)
            ->orderBy('min_montant', 'ASC')
            ->findAll();
    }

    public function getFrais(int $typeId, float $montant)
    {
        return $this->where('transaction_type_id', $typeId)
            ->where('min_montant <=', $montant)
            ->where('max_montant >=', $montant)
            ->first();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurPrefixeModel extends Model
{
    protected $table            = 'operateur_prefixe';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['operateur_id', 'prefixe'];
    protected $useTimestamps    = false;

    public function getOperateurFromPrefixe(string $prefixe)
    {
        return $this->builder()
            ->select('operateur.nom')
            ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
            ->where('operateur_prefixe.prefixe', $prefixe)
            ->get()->getRow();
    }
}

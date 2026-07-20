<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table            = 'operateur';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['nom'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    public function getPrefixes(?int $operateurId)
    {
        $builder = $this->db->table('operateur_prefixe');
        if ($operateurId !== null) {
            $builder->where('operateur_id', $operateurId);
        }
        return $builder->orderBy('prefixe', 'ASC')->get()->getResult();
    }
}

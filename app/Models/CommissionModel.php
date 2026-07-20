<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionModel extends Model
{
    protected $table            = 'commission';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'operateur_source_id',
        'operateur_destinataire_id',
        'pourcentage',
        'description',
        'est_actif',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getPourcentage(int $sourceId, int $destId): float
    {
        $row = $this->where('operateur_source_id', $sourceId)
            ->where('operateur_destinataire_id', $destId)
            ->where('est_actif', 1)
            ->first();

        return $row ? (float) $row->pourcentage : 0.0;
    }
}

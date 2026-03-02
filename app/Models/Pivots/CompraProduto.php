<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompraProduto extends Pivot
{
    public function getPrecoUnitarioAttribute($value): float
    {
        return $value / 100;
    }

    public function getSubtotalAttribute($value): float
    {
        return $value / 100;
    }
}

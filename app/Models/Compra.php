<?php

namespace App\Models;

use App\Models\Pivots\CompraProduto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Compra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fornecedor',
        'total',
        'status',
        'data_compra',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'data_compra' => 'date',
        ];
    }

    public function getTotalAttribute($value): float
    {
        return $value / 100;
    }

    public function setTotalAttribute($value): void
    {
        $this->attributes['total'] = (int) round($value * 100);
    }

    public function produtos(): BelongsToMany
    {
        return $this->belongsToMany(Produto::class, 'compra_produto')
            ->using(CompraProduto::class)
            ->withPivot('quantidade', 'preco_unitario', 'subtotal')
            ->withTimestamps();
    }
}

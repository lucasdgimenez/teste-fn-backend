<?php

namespace App\Models;

use App\Models\Pivots\VendaProduto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Venda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente',
        'total',
        'lucro',
        'status',
        'data_venda',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'data_venda' => 'date',
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

    public function getLucroAttribute($value): float
    {
        return $value / 100;
    }

    public function setLucroAttribute($value): void
    {
        $this->attributes['lucro'] = (int) round($value * 100);
    }

    public function produtos(): BelongsToMany
    {
        return $this->belongsToMany(Produto::class, 'venda_produto')
            ->using(VendaProduto::class)
            ->withPivot('quantidade', 'preco_unitario', 'subtotal')
            ->withTimestamps();
    }
}

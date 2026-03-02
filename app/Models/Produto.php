<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Produto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'preco_venda',
        'estoque_inicial',
    ];

    // Retorna o preço em reais (divide por 100 ao ler)
    public function getPrecoVendaAttribute(int $value): float
    {
        return $value / 100;
    }

    // Salva o preço em centavos (multiplica por 100 ao escrever)
    public function setPrecoVendaAttribute(float|int $value): void
    {
        $this->attributes['preco_venda'] = (int) round($value * 100);
    }

    public function compras(): BelongsToMany
    {
        return $this->belongsToMany(Compra::class, 'compra_produto')
            ->withPivot('quantidade', 'preco_unitario', 'subtotal')
            ->withTimestamps();
    }

    public function vendas(): BelongsToMany
    {
        return $this->belongsToMany(Venda::class, 'venda_produto')
            ->withPivot('quantidade', 'preco_unitario', 'subtotal')
            ->withTimestamps();
    }
}

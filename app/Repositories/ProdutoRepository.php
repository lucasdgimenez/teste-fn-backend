<?php

namespace App\Repositories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;

class ProdutoRepository
{
    private const LIST_COLUMNS = ['id', 'nome', 'preco_venda', 'estoque_inicial'];

    public function list(): Collection
    {
        return Produto::select(['id', 'nome', 'preco_venda', 'estoque_inicial'])->get();
    }

    public function find(int $id): ?Produto
    {
        return Produto::select(['id', 'nome', 'preco_venda', 'estoque_inicial'])->find($id);
    }

    public function create(array $data): Produto
    {
        return Produto::create($data);
    }

    public function update(Produto $produto, array $data): Produto
    {
        $produto->update($data);

        return $produto->refresh();
    }

    public function delete(Produto $produto): void
    {
        $produto->delete();
    }
}

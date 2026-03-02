<?php

namespace App\Repositories;

use App\Models\Compra;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompraRepository
{
    private const LIST_COLUMNS = ['id', 'fornecedor', 'total', 'status', 'data_compra'];
    private const SHOW_COLUMNS = ['id', 'fornecedor', 'total', 'status', 'data_compra', 'observacao'];

    public function list(): Collection
    {
        return Compra::select(self::LIST_COLUMNS)
            ->with([
                'produtos' => fn($q) => $q->select('produtos.id', 'produtos.nome'),
            ])
            ->get();
    }

    public function find(int $id): ?Compra
    {
        return Compra::select(self::SHOW_COLUMNS)
            ->with([
                'produtos' => fn($q) => $q->select('produtos.id', 'produtos.nome'),
            ])
            ->find($id);
    }

    public function create(array $data, array $produtos): Compra
    {
        return DB::transaction(function () use ($data, $produtos) {
            $compra = Compra::create(array_merge($data, ['total' => 0, 'status' => 'recebida']));

            $this->syncProdutos($compra, $produtos);
            $this->incrementarEstoque($produtos);

            return $this->find($compra->id);
        });
    }

    public function update(Compra $compra, array $data, ?array $produtos): Compra
    {
        return DB::transaction(function () use ($compra, $data, $produtos) {
            $compra->update($data);

            if ($produtos !== null) {
                $compra->loadMissing('produtos');
                $this->reverterEstoque($compra);

                $this->syncProdutos($compra, $produtos);
                $this->incrementarEstoque($produtos);
            }

            return $this->find($compra->id);
        });
    }

    public function delete(Compra $compra): void
    {
        $compra->loadMissing('produtos');
        $this->reverterEstoque($compra);
        $compra->delete();
    }

    private function syncProdutos(Compra $compra, array $produtos): void
    {
        $pivot = collect($produtos)->mapWithKeys(fn($item) => [
            $item['produto_id'] => [
                'quantidade'     => $item['quantidade'],
                'preco_unitario' => (int) round($item['preco_unitario'] * 100),
            ],
        ]);

        $compra->produtos()->sync($pivot);

        $total = collect($produtos)->sum(fn($item) => $item['quantidade'] * $item['preco_unitario']);

        $compra->update(['total' => $total]);
    }

    // Incrementa estoque ao registrar/atualizar uma compra (entrada)
    private function incrementarEstoque(array $produtos): void
    {
        foreach ($produtos as $item) {
            Produto::where('id', $item['produto_id'])
                ->increment('estoque_inicial', $item['quantidade']);
        }
    }

    // Reverte estoque ao atualizar/excluir uma compra (desfaz entrada anterior)
    private function reverterEstoque(Compra $compra): void
    {
        foreach ($compra->produtos as $produto) {
            $produto->decrement('estoque_inicial', $produto->pivot->quantidade);
        }
    }
}

<?php

namespace App\Repositories;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VendaRepository
{
    private const LIST_COLUMNS = ['id', 'cliente', 'total', 'lucro', 'status', 'data_venda'];
    private const SHOW_COLUMNS = ['id', 'cliente', 'total', 'lucro', 'status', 'data_venda', 'observacao'];

    public function list(): Collection
    {
        return Venda::select(self::LIST_COLUMNS)
            ->with([
                'produtos' => fn($q) => $q->select('produtos.id', 'produtos.nome'),
            ])
            ->get();
    }

    public function find(int $id): ?Venda
    {
        return Venda::select(self::SHOW_COLUMNS)
            ->with([
                'produtos' => fn($q) => $q->select('produtos.id', 'produtos.nome'),
            ])
            ->find($id);
    }

    public function create(array $data, array $produtos): Venda
    {
        return DB::transaction(function () use ($data, $produtos) {
            $this->validarEDeduzirEstoque($produtos);

            [$receita, $lucro] = $this->calcularReceitaLucro($produtos);

            $venda = Venda::create(array_merge($data, [
                'data_venda' => now()->format('Y-m-d'),
                'total'      => $receita,
                'lucro'      => $lucro,
                'status'     => 'concluida',
            ]));

            $this->syncProdutos($venda, $produtos);

            return $this->find($venda->id);
        });
    }

    public function update(Venda $venda, array $data, ?array $produtos): Venda
    {
        return DB::transaction(function () use ($venda, $data, $produtos) {
            if ($produtos !== null) {
                $this->restaurarEstoque($venda);

                $this->validarEDeduzirEstoque($produtos);

                [$receita, $lucro] = $this->calcularReceitaLucro($produtos);

                $data = array_merge($data, ['total' => $receita, 'lucro' => $lucro]);
            }

            $venda->update($data);

            if ($produtos !== null) {
                $this->syncProdutos($venda, $produtos);
            }

            return $this->find($venda->id);
        });
    }

    public function cancelar(Venda $venda): Venda
    {
        if ($venda->status === 'cancelada') {
            throw new \InvalidArgumentException('Esta venda já está cancelada.');
        }

        return DB::transaction(function () use ($venda) {
            $venda->loadMissing('produtos');

            foreach ($venda->produtos as $produto) {
                $produto->increment('estoque_inicial', $produto->pivot->quantidade);
            }

            $venda->update(['status' => 'cancelada']);

            return $this->find($venda->id);
        });
    }

    public function delete(Venda $venda): void
    {
        $venda->delete();
    }

    private function validarEDeduzirEstoque(array $produtos): void
    {
        foreach ($produtos as $item) {
            $produto = Produto::select('id', 'nome', 'estoque_inicial')
                ->where('id', $item['produto_id'])
                ->first();

            if (!$produto) {
                throw new \InvalidArgumentException("Produto ID {$item['produto_id']} não encontrado.");
            }

            if ($produto->estoque_inicial < $item['quantidade']) {
                throw new \InvalidArgumentException(
                    "Estoque insuficiente para '{$produto->nome}'. Disponível: {$produto->estoque_inicial}, solicitado: {$item['quantidade']}."
                );
            }

            $produto->decrement('estoque_inicial', $item['quantidade']);
        }
    }

    private function restaurarEstoque(Venda $venda): void
    {
        foreach ($venda->produtos as $produto) {
            $produto->increment('estoque_inicial', $produto->pivot->quantidade);
        }
    }

    private function calcularReceitaLucro(array $produtos): array
    {
        $receita = 0;
        $custo   = 0;

        foreach ($produtos as $item) {
            $receita += $item['quantidade'] * $item['preco_unitario'];

            $ultimoPrecoCompra = DB::table('compra_produto')
                ->join('compras', 'compras.id', '=', 'compra_produto.compra_id')
                ->where('compra_produto.produto_id', $item['produto_id'])
                ->whereNull('compras.deleted_at')
                ->orderByDesc('compras.data_compra')
                ->orderByDesc('compras.id')
                ->value('compra_produto.preco_unitario') ?? 0;

            $custo += $item['quantidade'] * ($ultimoPrecoCompra / 100);
        }

        return [$receita, $receita - $custo];
    }

    private function syncProdutos(Venda $venda, array $produtos): void
    {
        $pivot = collect($produtos)->mapWithKeys(fn($item) => [
            $item['produto_id'] => [
                'quantidade'     => $item['quantidade'],
                'preco_unitario' => (int) round($item['preco_unitario'] * 100),
            ],
        ]);

        $venda->produtos()->sync($pivot);
    }
}

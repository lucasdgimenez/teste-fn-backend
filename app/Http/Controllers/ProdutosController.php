<?php

namespace App\Http\Controllers;

use App\Repositories\ProdutoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutosController extends Controller
{
    public function __construct(
        private readonly ProdutoRepository $repository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Produtos listados com sucesso.',
            'data'    => $this->repository->list(),
            'code'    => 200,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $produto = $this->repository->find($id);

        if (!$produto) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produto encontrado.',
            'data'    => $produto,
            'code'    => 200,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome'            => 'required|string|min:3',
            'preco_venda'     => 'required|numeric|min:0.01',
            'estoque_inicial' => 'integer|min:0',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produto criado com sucesso.',
            'data'    => $this->repository->create($validated),
            'code'    => 201,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $produto = $this->repository->find($id);

        if (!$produto) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        $validated = $request->validate([
            'nome'            => 'sometimes|string|min:3',
            'preco_venda'     => 'sometimes|numeric|min:0.01',
            'estoque_inicial' => 'sometimes|integer|min:0',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produto atualizado com sucesso.',
            'data'    => $this->repository->update($produto, $validated),
            'code'    => 200,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $produto = $this->repository->find($id);

        if (!$produto) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        $this->repository->delete($produto);

        return response()->json([
            'success' => true,
            'message' => 'Produto removido com sucesso.',
            'data'    => null,
            'code'    => 200,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Repositories\VendaRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendasController extends Controller
{
    public function __construct(
        private readonly VendaRepository $repository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Vendas listadas com sucesso.',
            'data'    => $this->repository->list(),
            'code'    => 200,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $venda = $this->repository->find($id);

        if (!$venda) {
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venda encontrada.',
            'data'    => $venda,
            'code'    => 200,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente'                   => 'required|string',
            'produtos'                  => 'required|array|min:1',
            'produtos.*.produto_id'     => 'required|exists:produtos,id',
            'produtos.*.quantidade'     => 'required|integer|min:1',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
        ]);

        try {
            $venda = $this->repository->create(
                ['cliente' => $validated['cliente']],
                $validated['produtos']
            );

            return response()->json([
                'success' => true,
                'message' => 'Venda registrada com sucesso.',
                'data'    => $venda,
                'code'    => 201,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
                'code'    => 422,
            ], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $venda = $this->repository->find($id);

        if (!$venda) {
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        $validated = $request->validate([
            'cliente'                   => 'sometimes|string',
            'observacao'                => 'nullable|string',
            'produtos'                  => 'sometimes|array|min:1',
            'produtos.*.produto_id'     => 'required_with:produtos|exists:produtos,id',
            'produtos.*.quantidade'     => 'required_with:produtos|integer|min:1',
            'produtos.*.preco_unitario' => 'required_with:produtos|numeric|min:0.01',
        ]);

        try {//teste
            $venda = $this->repository->update(
                $venda,
                array_intersect_key($validated, array_flip(['cliente', 'observacao'])),
                $validated['produtos'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Venda atualizada com sucesso.',
                'data'    => $venda,
                'code'    => 200,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
                'code'    => 422,
            ], 422);
        }
    }

    public function cancelar(int $id): JsonResponse
    {
        $venda = $this->repository->find($id);

        if (!$venda) {
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        try {
            $venda = $this->repository->cancelar($venda);

            return response()->json([
                'success' => true,
                'message' => 'Venda cancelada e estoque revertido com sucesso.',
                'data'    => $venda,
                'code'    => 200,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
                'code'    => 422,
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $venda = $this->repository->find($id);

        if (!$venda) {
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        $this->repository->delete($venda);

        return response()->json([
            'success' => true,
            'message' => 'Venda removida com sucesso.',
            'data'    => null,
            'code'    => 200,
        ]);
    }
}

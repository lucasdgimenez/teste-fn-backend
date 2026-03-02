<?php

namespace App\Http\Controllers;

use App\Repositories\CompraRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComprasController extends Controller
{
    public function __construct(
        private readonly CompraRepository $repository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Compras listadas com sucesso.',
            'data'    => $this->repository->list(),
            'code'    => 200,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $compra = $this->repository->find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Compra encontrada.',
            'data'    => $compra,
            'code'    => 200,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fornecedor'                => 'required|string',
            'produtos'                  => 'required|array|min:1',
            'produtos.*.produto_id'     => 'required|exists:produtos,id',
            'produtos.*.quantidade'     => 'required|integer|min:1',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
        ]);

        $compra = $this->repository->create(
            [//teste
                'fornecedor'  => $validated['fornecedor'],
                'data_compra' => now()->format('Y-m-d'),
            ],
            $validated['produtos']
        );

        return response()->json([
            'success' => true,
            'message' => 'Compra registrada com sucesso.',
            'data'    => $compra,
            'code'    => 201,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $compra = $this->repository->find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        $validated = $request->validate([
            'fornecedor'                => 'sometimes|string',
            'data_compra'               => 'sometimes|date',
            'status'                    => 'sometimes|in:recebida,cancelada',
            'observacao'                => 'nullable|string',
            'produtos'                  => 'sometimes|array|min:1',
            'produtos.*.produto_id'     => 'required_with:produtos|exists:produtos,id',
            'produtos.*.quantidade'     => 'required_with:produtos|integer|min:1',
            'produtos.*.preco_unitario' => 'required_with:produtos|numeric|min:0.01',
        ]);

        $compra = $this->repository->update(
            $compra,
            array_intersect_key($validated, array_flip(['fornecedor', 'data_compra', 'status', 'observacao'])),
            $validated['produtos'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Compra atualizada com sucesso.',
            'data'    => $compra,
            'code'    => 200,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $compra = $this->repository->find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra não encontrada.',
                'data'    => null,
                'code'    => 404,
            ], 404);
        }

        $this->repository->delete($compra);

        return response()->json([
            'success' => true,
            'message' => 'Compra removida com sucesso.',
            'data'    => null,
            'code'    => 200,
        ]);
    }
}

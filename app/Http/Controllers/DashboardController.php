<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Venda;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $faturamentoTotal = Venda::where('status', 'concluida')->sum('total') / 100;
        $lucroTotal       = Venda::where('status', 'concluida')->sum('lucro') / 100;
        $custoTotal       = Compra::where('status', 'recebida')->sum('total') / 100;
        $totalVendas      = Venda::where('status', 'concluida')->count();
        $ticketMedio      = $totalVendas > 0 ? round($faturamentoTotal / $totalVendas, 2) : 0;

        $cards = [
            'faturamento_total' => round($faturamentoTotal, 2),
            'lucro_total'       => round($lucroTotal, 2),
            'custo_total'       => round($custoTotal, 2),
            'total_vendas'      => $totalVendas,
            'ticket_medio'      => $ticketMedio,
        ];

        $inicio = Carbon::now()->subMonths(11)->startOfMonth();

        $graficoMensal = Venda::where('status', 'concluida')
            ->where('data_venda', '>=', $inicio)
            ->selectRaw("DATE_FORMAT(data_venda, '%Y-%m') as mes, SUM(total) / 100 as faturamento, SUM(lucro) / 100 as lucro")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $comprasMensais = Compra::where('status', 'recebida')
            ->where('data_compra', '>=', $inicio)
            ->selectRaw("DATE_FORMAT(data_compra, '%Y-%m') as mes, SUM(total) / 100 as custo")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard carregado com sucesso.',
            'data'    => [
                'cards'             => $cards,
                'grafico_mensal'    => $graficoMensal,
                'compras_mensais'   => $comprasMensais
            ]
        ], 200);
    }
}

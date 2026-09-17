<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório Financeiro</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        h1 { font-size: 20px; margin: 0 0 4px; color: #111827; }
        h2 { font-size: 14px; margin: 0 0 10px; color: #374151; }
        .muted { color: #6b7280; font-size: 11px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .totais {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .cartao {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .cartao .rotulo { font-size: 11px; color: #6b7280; margin-bottom: 4px; }
        .cartao .valor { font-size: 18px; font-weight: bold; }
        .verde { color: #047857; }
        .vermelho { color: #b91c1c; }
        .secao { margin-bottom: 24px; }
        .secao-titulo { font-size: 13px; font-weight: bold; margin-bottom: 8px; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f9fafb;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        tr.ultima td { border-bottom: none; }
        .texto-direita { text-align: right; }
        .valor-receita { font-weight: bold; color: #047857; }
        .valor-despesa { font-weight: bold; color: #b91c1c; }
        .cancelado td { color: #9ca3af; }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }
        .status-pago { background: #d1fae5; color: #065f46; }
        .status-pendente { background: #fef3c7; color: #92400e; }
        .status-cancelado { background: #f3f4f6; color: #4b5563; }
        .rodape {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Relatório Financeiro</h1>
            <div class="muted">{{ $empresaNome }}</div>
        </div>
        <div class="muted" style="text-align: right;">{{ strtoupper($periodoLabel) }}</div>
    </div>

    <div class="totais">
        <div class="cartao">
            <div class="rotulo">Total de receitas</div>
            <div class="valor verde">R$ {{ $dados['totais']['total_receitas'] }}</div>
        </div>
        <div class="cartao">
            <div class="rotulo">Total de despesas</div>
            <div class="valor vermelho">R$ {{ $dados['totais']['total_despesas'] }}</div>
        </div>
        @php
            $resultadoPositivo = ((float) str_replace(',', '.', str_replace('.', '', $dados['totais']['resultado']))) >= 0;
        @endphp
        <div class="cartao">
            <div class="rotulo">Resultado financeiro</div>
            <div class="valor {{ $resultadoPositivo ? 'verde' : 'vermelho' }}">R$ {{ $dados['totais']['resultado'] }}</div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">
            Receitas ({{ $dados['totais']['qtd_receitas'] }})
        </div>
        @if (count($dados['receitas']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Cliente</th>
                        <th>Categoria</th>
                        <th class="texto-direita">Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dados['receitas'] as $receita)
                        <tr class="{{ $receita['cancelado'] ? 'cancelado' : '' }}">
                            <td>{{ \Carbon\CarbonImmutable::parse($receita['data'])->format('d/m/Y') }}</td>
                            <td>{{ $receita['descricao'] }}</td>
                            <td>{{ $receita['contraparte'] ?? '—' }}</td>
                            <td>{{ $receita['categoria'] ?? '—' }}</td>
                            <td class="texto-direita valor-receita">R$ {{ $receita['valor_formatado'] }}</td>
                            <td><span class="status status-{{ $receita['status'] }}">{{ strtoupper($receita['status']) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Nenhuma receita no período.</p>
        @endif
    </div>

    <div class="secao">
        <div class="secao-titulo">
            Despesas ({{ $dados['totais']['qtd_despesas'] }})
        </div>
        @if (count($dados['despesas']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Fornecedor</th>
                        <th>Categoria</th>
                        <th class="texto-direita">Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dados['despesas'] as $despesa)
                        <tr class="{{ $despesa['cancelado'] ? 'cancelado' : '' }}">
                            <td>{{ \Carbon\CarbonImmutable::parse($despesa['data'])->format('d/m/Y') }}</td>
                            <td>{{ $despesa['descricao'] }}</td>
                            <td>{{ $despesa['contraparte'] ?? '—' }}</td>
                            <td>{{ $despesa['categoria'] ?? '—' }}</td>
                            <td class="texto-direita valor-despesa">R$ {{ $despesa['valor_formatado'] }}</td>
                            <td><span class="status status-{{ $despesa['status'] }}">{{ strtoupper($despesa['status']) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Nenhuma despesa no período.</p>
        @endif
    </div>

    <div class="rodape">Gerado em {{ now()->format('d/m/Y H:i') }} • ContaFácil</div>
</body>
</html>
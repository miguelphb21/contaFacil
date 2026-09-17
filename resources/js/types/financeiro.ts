export type LancamentoTipo = 'receita' | 'despesa'

export type LancamentoStatus = 'pendente' | 'pago' | 'cancelado'

export interface LancamentoItem {
    id: number
    tipo: LancamentoTipo
    descricao: string
    valor: string
    data: string
    status: LancamentoStatus
    forma_pagamento: string | null
    categoria: { id: number; nome: string } | null
    contraparte: { id: number; nome: string } | null
    recorrencia_id: number | null
}

export interface Opcao {
    id: number
    nome: string
}

export interface OpcaoTipoLancamento extends Opcao {
    tipo: LancamentoTipo
}

export interface OpcaoTipoContraparte extends Opcao {
    tipo: 'fornecedor' | 'cliente'
}

export interface Periodo {
    chave: string
    ano: number
    mes: number
    label: string
    anterior: string
    proximo: string
}

export interface ResumoMensal {
    total_receitas: string
    total_despesas: string
    saldo: string
    pagas: string
    pendentes: string
}

export interface RecorrenciaItem {
    id: number
    tipo: LancamentoTipo
    descricao: string
    valor: string
    dia: number
    data_inicio: string
    data_fim: string | null
    forma_pagamento: string | null
    ativa: boolean
    categoria: { id: number; nome: string } | null
    contraparte: { id: number; nome: string } | null
}

export interface ContraparteItem {
    id: number
    nome: string
    tipo: 'fornecedor' | 'cliente'
    cpf_cnpj: string | null
    telefone: string | null
    email: string | null
    endereco: string | null
}

export interface CategoriaItem {
    id: number
    nome: string
    tipo: LancamentoTipo
    descricao: string | null
}

export interface ProximaLancamento extends LancamentoItem {
    data: string
}

export interface Flash {
    success?: string | null
    error?: string | null
}

export type PeriodoRelatorioTipo = 'mes' | 'periodo' | 'ano' | 'tudo'

export interface PeriodoRelatorio {
    tipo: PeriodoRelatorioTipo
    label: string
    inicio: string
    fim: string | null
}

export interface RelatorioMovimentacao {
    id: number
    data: string
    descricao: string
    valor: string
    valor_formatado: string
    status: LancamentoStatus
    categoria: string | null
    contraparte: string | null
    cancelado: boolean
}

export interface RelatorioTotais {
    total_receitas: string
    total_despesas: string
    resultado: string
    qtd_receitas: number
    qtd_despesas: number
}

export interface RelatorioDados {
    receitas: RelatorioMovimentacao[]
    despesas: RelatorioMovimentacao[]
    totais: RelatorioTotais
}
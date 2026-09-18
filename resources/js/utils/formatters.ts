export function converterParaNumero(valor: string): number {
    return Number(valor.replace(/\./g, '').replace(',', '.'))
}

export function formatarMoeda(valor: number | string): string {
    const numero = typeof valor === 'string' ? converterParaNumero(valor) : valor

    if (Number.isNaN(numero)) {
        return typeof valor === 'string' ? valor : 'R$ 0,00'
    }

    return numero.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    })
}

export function formatarData(data: string): string {
    const instante = new Date(data)
    if (Number.isNaN(instante.getTime())) {
        return data
    }

    return instante.toLocaleDateString('pt-BR')
}

export function formatarDataCurta(data: string): string {
    const instante = new Date(data)
    if (Number.isNaN(instante.getTime())) {
        return data
    }

    return instante
        .toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
        .replace('.', '')
}

export const periodoLabelCurto = (chave: string): string => {
    const [ano, mes] = chave.split('-').map(Number)
    const instante = new Date(ano, mes - 1, 1)

    return instante
        .toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' })
        .replace('.', '')
        .replace(/^(\w)/, (letra) => letra.toUpperCase())
}
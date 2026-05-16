@extends('layouts.app')

@section('title', 'Minha carteira')

@section('content')
    <div class="grid grid-2" style="margin-bottom:1.25rem;">
        <div class="card">
            <p class="card-title">Saldo disponível</p>
            <p class="balance-display {{ $balanceCents < 0 ? 'negative' : '' }}">
                R$ {{ number_format($balance, 2, ',', '.') }}
            </p>
            @if ($balanceCents < 0)
                <p style="color:var(--warning);font-size:0.85rem;margin-top:0.5rem;">
                    Saldo negativo — depósitos serão somados ao valor atual.
                </p>
            @endif
        </div>

        <div class="card">
            <p class="card-title">Resumo rápido</p>
            <p style="font-size:0.9rem;color:var(--muted);">
                Use os formulários ao lado para depositar ou transferir.
                Transações podem ser estornadas enquanto estiverem ativas.
            </p>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <p class="card-title">Depositar</p>
            <form method="POST" action="{{ route('wallet.deposit') }}">
                @csrf
                <label for="deposit_amount">Valor (R$)</label>
                <input type="number" id="deposit_amount" name="amount" step="0.01" min="0.01"
                       value="{{ old('amount') }}" placeholder="100,00" required>

                <button type="submit" class="btn btn-primary btn-block">Depositar</button>
            </form>
        </div>

        <div class="card">
            <p class="card-title">Transferir</p>
            <form method="POST" action="{{ route('wallet.transfer') }}">
                @csrf
                <label for="to_user_id">Destinatário</label>
                <select id="to_user_id" name="to_user_id" required>
                    <option value="">Selecione...</option>
                    @foreach ($recipients as $recipient)
                        <option value="{{ $recipient->id }}" @selected(old('to_user_id') == $recipient->id)>
                            {{ $recipient->name }} ({{ $recipient->email }})
                        </option>
                    @endforeach
                </select>

                <label for="transfer_amount">Valor (R$)</label>
                <input type="number" id="transfer_amount" name="amount" step="0.01" min="0.01"
                       value="{{ old('amount') }}" placeholder="50,00" required>

                <button type="submit" class="btn btn-primary btn-block">Transferir</button>
            </form>
        </div>
    </div>

    <div class="card">
        <p class="card-title">Histórico de transações</p>

        @if (count($transactions) === 0)
            <p class="empty-state">Nenhuma transação ainda. Faça um depósito para começar.</p>
        @else
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $tx)
                            <tr>
                                <td>{{ $tx->id }}</td>
                                <td>
                                    <span class="badge badge-{{ $tx->type }}">
                                        {{ str_replace('_', ' ', $tx->type) }}
                                    </span>
                                </td>
                                <td>R$ {{ number_format($tx->amount->amount(), 2, ',', '.') }}</td>
                                <td>
                                    @if ($tx->status === 'reversed')
                                        <span class="badge badge-reversed">estornada</span>
                                    @else
                                        <span style="color:var(--success);">concluída</span>
                                    @endif
                                </td>
                                <td>{{ $tx->createdAt ? \Carbon\Carbon::parse($tx->createdAt)->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    @if ($tx->status !== 'reversed' && in_array($tx->type, ['deposit', 'transfer_out']))
                                        <form method="POST" action="{{ route('transactions.reverse', $tx->id) }}"
                                              onsubmit="return confirm('Confirmar estorno desta transação?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Estornar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

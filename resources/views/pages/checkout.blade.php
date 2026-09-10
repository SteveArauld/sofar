@extends('layouts.app')

@section('title', 'Finalizar encomenda | DFP Interiores')

@section('content')
<div class="CheckoutPage">
    <h1>Finalizar encomenda</h1>

    @if ($errors->any())
        <div class="co-errors">
            <strong>Não foi possível concluir. Corrija os campos assinalados:</strong>
            <ul style="margin:6px 0 0 18px;padding:0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.place') }}" id="checkoutForm">
        @csrf
        <div class="co-grid">
            <div>
                <div class="co-card">
                    <h2>Contacto</h2>
                    <div class="grid2">
                        <div class="field">
                            <label>Nome completo</label>
                            <input type="text" name="name" value="{{ $prefill['name'] }}" class="@error('name') is-invalid @enderror" required>
                            @error('name') <span class="err">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label>Telemóvel</label>
                            <input type="tel" name="phone" value="{{ $prefill['phone'] }}" class="@error('phone') is-invalid @enderror" required>
                            @error('phone') <span class="err">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ $prefill['email'] }}" class="@error('email') is-invalid @enderror" required>
                            @error('email') <span class="err">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label>NIF (opcional)</label>
                            <input type="text" name="nif" value="{{ old('nif') }}" class="@error('nif') is-invalid @enderror">
                            @error('nif') <span class="err">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="co-card">
                    <h2>Morada de entrega</h2>
                    <div class="field">
                        <label>Morada</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="@error('address') is-invalid @enderror" required>
                        @error('address') <span class="err">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid2">
                        <div class="field">
                            <label>Código postal <span style="text-transform:none;font-weight:400;color:#999;">(opcional)</span></label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="0000-000" class="@error('postal_code') is-invalid @enderror">
                            @error('postal_code') <span class="err">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label>Localidade</label>
                            <input type="text" name="city" value="{{ old('city') }}" class="@error('city') is-invalid @enderror" required>
                            @error('city') <span class="err">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="field">
                        <label>Notas (opcional)</label>
                        <textarea name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <span class="err">{{ $message }}</span> @enderror
                    </div>

                    <label class="check-row" style="margin-top:10px;">
                        <input type="checkbox" name="billing_different" value="1" id="billingDiff"
                               {{ old('billing_different') ? 'checked' : '' }}>
                        <span>A morada de faturação é diferente da morada de entrega</span>
                    </label>
                </div>

                <div class="co-card" id="billingCard" @if(! old('billing_different')) hidden @endif>
                    <h2>Morada de faturação</h2>
                    <div class="field">
                        <label>Nome / Empresa</label>
                        <input type="text" name="billing_name" value="{{ old('billing_name') }}" class="@error('billing_name') is-invalid @enderror">
                        @error('billing_name') <span class="err">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label>Morada</label>
                        <input type="text" name="billing_address" value="{{ old('billing_address') }}" class="@error('billing_address') is-invalid @enderror">
                        @error('billing_address') <span class="err">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid2">
                        <div class="field">
                            <label>Código postal <span style="text-transform:none;font-weight:400;color:#999;">(opcional)</span></label>
                            <input type="text" name="billing_postal_code" value="{{ old('billing_postal_code') }}" placeholder="0000-000" class="@error('billing_postal_code') is-invalid @enderror">
                            @error('billing_postal_code') <span class="err">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label>Localidade</label>
                            <input type="text" name="billing_city" value="{{ old('billing_city') }}" class="@error('billing_city') is-invalid @enderror">
                            @error('billing_city') <span class="err">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <aside class="co-summary">
                <h2>A sua encomenda</h2>
                @foreach ($cart['items'] as $it)
                    <div class="li">
                        <img src="/images/120-120/{{ $it['images'][0] }}" alt="{{ $it['nome'] }}">
                        <div>
                            <div class="nm">{{ $it['nome'] }}</div>
                            <div class="q">Qtd: {{ $it['qtd'] }}</div>
                        </div>
                        <div class="pr">{{ number_format((float) $it['total'], 2, ',', ' ') }}€</div>
                    </div>
                @endforeach

                <div class="row"><span>Subtotal</span><span>{{ number_format((float) $cart['subtotal'], 2, ',', ' ') }}€</span></div>
                <div class="row"><span>Portes</span><span>Grátis</span></div>
                <div class="row total"><span>Total</span><span>{{ number_format((float) $cart['subtotal'], 2, ',', ' ') }}€</span></div>

                <label class="check-row">
                    <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                    <span>Li e aceito os <a href="/ajuda/termos-e-condicoes" target="_blank">termos e condições</a>.</span>
                </label>
                @error('terms') <span class="err">{{ $message }}</span> @enderror

                <button type="submit" class="co-submit">Confirmar encomenda</button>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var billingDiff = document.getElementById('billingDiff');
    var billingCard = document.getElementById('billingCard');
    if (billingDiff && billingCard) {
        billingDiff.addEventListener('change', function () {
            billingCard.hidden = !billingDiff.checked;
        });
    }
})();
</script>
@endpush

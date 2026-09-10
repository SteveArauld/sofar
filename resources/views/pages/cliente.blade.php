@extends('layouts.app')

@section('title', 'A minha conta | DFP Interiores')

@push('mods-css')
<link href="/assets/css/pt/mods/cliente.css" rel="stylesheet">
@endpush

@section('content')
<div class="container breadcrumb">
    <div>
        <a href="/">Início</a>
        <h1>A minha conta</h1>
    </div>
</div>

<div class="container" style="max-width:960px;padding:40px 15px 80px;">
@auth
    <div class="card border-0 shadow-sm p-4 p-md-5" style="max-width:560px;margin:0 auto;">
        <h3 class="fw-bold mb-4">Olá, {{ $authUser->name }}</h3>
        <ul class="list-unstyled mb-4">
            <li class="mb-2"><strong>Nome:</strong> {{ $authUser->name }}</li>
            <li class="mb-2"><strong>Email:</strong> {{ $authUser->email }}</li>
            <li class="mb-2"><strong>Telemóvel:</strong> {{ $authUser->phone ?: '—' }}</li>
        </ul>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/wishlist" class="btn btn-outline-secondary">Os meus favoritos</a>
            <a href="/carrinho" class="btn btn-outline-secondary">O meu carrinho</a>
            <form action="/cliente/logout" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">Terminar sessão</button>
            </form>
        </div>
    </div>
@else
    <div class="row g-4 justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 p-md-5 h-100">
                <h3 class="fw-bold mb-4">Entrar</h3>
                <form id="loginForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold text-muted">Email ou telemóvel</label>
                        <input type="text" name="username" class="form-control form-control-lg bg-light" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">Senha</label>
                        <input type="password" name="password" class="form-control form-control-lg bg-light" required>
                    </div>
                    <p class="text-danger small d-none" data-error></p>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Entrar</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 p-md-5 h-100">
                <h3 class="fw-bold mb-4">Criar conta</h3>
                <form id="registoForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold text-muted">Nome</label>
                        <input type="text" name="nome" class="form-control bg-light" required>
                        <small class="text-danger" data-err="nome"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold text-muted">Email</label>
                        <input type="email" name="email" class="form-control bg-light" required>
                        <small class="text-danger" data-err="email"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold text-muted">Telemóvel</label>
                        <input type="tel" name="telemovel" class="form-control bg-light">
                        <small class="text-danger" data-err="telemovel"></small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-uppercase fw-bold text-muted">Senha</label>
                        <input type="password" name="password" class="form-control bg-light" required>
                        <small class="text-danger" data-err="password"></small>
                    </div>
                    <p class="text-danger small d-none" data-error></p>
                    <button type="submit" class="btn btn-success btn-lg w-100">Criar conta</button>
                </form>
            </div>
        </div>
    </div>
@endauth
</div>
@endsection

@push('scripts')
<script>
(function () {
    function send(form, url, onErr) {
        var data = {};
        new FormData(form).forEach(function (v, k) { data[k] = v; });
        var box = form.querySelector('[data-error]');
        if (box) { box.classList.add('d-none'); box.textContent = ''; }
        form.querySelectorAll('[data-err]').forEach(function (el) { el.textContent = ''; });
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        }).then(function (r) {
            return r.json().then(function (body) { return { ok: r.ok, body: body }; });
        }).then(function (res) {
            if (res.ok && (res.body.success || res.body === true)) {
                window.location.href = '/cliente';
                return;
            }
            onErr(res.body || {});
        }).catch(function () {
            if (box) { box.classList.remove('d-none'); box.textContent = 'Ocorreu um erro. Tente novamente.'; }
        }).finally(function () { btn.disabled = false; });
    }

    var lf = document.getElementById('loginForm');
    if (lf) lf.addEventListener('submit', function (e) {
        e.preventDefault();
        send(lf, '/cliente/login', function (body) {
            var box = lf.querySelector('[data-error]');
            box.classList.remove('d-none');
            box.textContent = body.error || (body.errors && Object.values(body.errors).flat()[0]) || 'Credenciais inválidas.';
        });
    });

    var rf = document.getElementById('registoForm');
    if (rf) rf.addEventListener('submit', function (e) {
        e.preventDefault();
        send(rf, '/cliente/registo', function (body) {
            if (body.errors) {
                Object.keys(body.errors).forEach(function (k) {
                    var el = rf.querySelector('[data-err="' + k + '"]');
                    if (el) el.textContent = body.errors[k][0];
                });
            } else {
                var box = rf.querySelector('[data-error]');
                box.classList.remove('d-none');
                box.textContent = body.error || 'Não foi possível criar a conta.';
            }
        });
    });
})();
</script>
@endpush

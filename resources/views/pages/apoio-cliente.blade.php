<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apoio ao Cliente | DFP Interiores</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon-32.png?v=3">
    <style>
        :root { --pink: #e5007d; --ink: #1a1a1a; --line: #e6e6e6; }
        * { box-sizing: border-box; }
        body { margin: 0; font: 15px/1.55 -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: var(--ink); background: #fff; }
        .wrap { max-width: 620px; margin: 0 auto; padding: 24px 20px 40px; }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
        .brand img { height: 34px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .lead { color: #666; margin: 0 0 22px; }
        .card { border: 1px solid var(--line); border-radius: 12px; padding: 16px 18px; margin-bottom: 16px; }
        .card h2 { font-size: 13px; text-transform: uppercase; letter-spacing: .05em; color: var(--pink); margin: 0 0 10px; }
        .contact-row { display: flex; gap: 10px; margin: 6px 0; }
        .contact-row b { min-width: 92px; color: #555; font-weight: 600; }
        a { color: var(--pink); }
        details { border-top: 1px solid var(--line); padding: 10px 0; }
        details:first-of-type { border-top: 0; }
        summary { cursor: pointer; font-weight: 600; }
        details p { margin: 8px 0 0; color: #555; }
        label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #777; margin: 12px 0 4px; }
        input, textarea, select { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 10px 11px; font: inherit; background: #fafafa; }
        input:focus, textarea:focus, select:focus { outline: 2px solid #f6c9e2; background: #fff; }
        button { margin-top: 16px; width: 100%; border: 0; background: var(--pink); color: #fff; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; }
        button:hover { opacity: .92; }
        .ok { background: #e7f6ec; border: 1px solid #b3e0c4; color: #1e6b3a; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        .err { color: #c62828; font-size: 12px; margin-top: 3px; display: block; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <img src="/assets/logos/logo.png" alt="DFP Interiores">
        <strong>Apoio ao Cliente</strong>
    </div>

    <h1>Como podemos ajudar?</h1>
    <p class="lead">Envie-nos uma mensagem ou consulte as perguntas frequentes.</p>

    @if (session('sent'))
        <div class="ok">Mensagem enviada. A nossa equipa responde normalmente em 24-48h úteis.</div>
    @endif

    <div class="card">
        <h2>Contactos</h2>
        <div class="contact-row"><b>Empresa</b><span>DFP Interiores, Unipessoal Lda · NIF 504074571</span></div>
        <div class="contact-row"><b>Email</b><span><a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a></span></div>
        <div class="contact-row"><b>Telefone</b><span><a href="tel:+351912026453">+351 912 026 453</a></span></div>
        <div class="contact-row"><b>Horário</b><span>Segunda a Sexta 9h-22h · Sábado 9h-18h</span></div>
        <div class="contact-row"><b>Morada</b><span>Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas</span></div>
    </div>

    <div class="card">
        <h2>Perguntas frequentes</h2>
        <details>
            <summary>Quais os prazos de entrega?</summary>
            <p>Artigos em stock: 3 a 8 dias úteis. Artigos por encomenda: 20 a 25 dias úteis. O prazo exato é indicado na ficha de cada produto.</p>
        </details>
        <details>
            <summary>Como acompanho a minha encomenda?</summary>
            <p>Recebe um email com o número da encomenda (ex. DFP-000000-XXXX) e é notificado em cada mudança de estado. Pode também consultar a área <a href="/cliente" target="_blank">A minha conta</a>.</p>
        </details>
        <details>
            <summary>Quais os métodos de pagamento?</summary>
            <p>Referência Multibanco e MB WAY. O pagamento deve ser efetuado nas 72h seguintes à encomenda.</p>
        </details>
        <details>
            <summary>Posso trocar ou devolver um artigo?</summary>
            <p>Sim, dispõe de 14 dias após a receção para solicitar a devolução, desde que o artigo esteja em bom estado e na embalagem original.</p>
        </details>
    </div>

    <div class="card">
        <h2>Enviar mensagem</h2>
        <form method="POST" action="/apoioaocliente">
            @csrf
            <label>Nome</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            @error('name') <span class="err">{{ $message }}</span> @enderror

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <span class="err">{{ $message }}</span> @enderror

            <label>Assunto</label>
            <select name="subject" required>
                @foreach (['Encomenda / entrega', 'Produto disponível', 'Pagamento', 'Devolução', 'Outro'] as $s)
                    <option value="{{ $s }}" @selected(old('subject') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            @error('subject') <span class="err">{{ $message }}</span> @enderror

            <label>Mensagem</label>
            <textarea name="message" rows="5" required>{{ old('message') }}</textarea>
            @error('message') <span class="err">{{ $message }}</span> @enderror

            <button type="submit">Enviar mensagem</button>
        </form>
    </div>
</div>
</body>
</html>

<footer class="footer">
    <div class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h3>{{ __('Subscreva a nossa Newsletter') }}</h3>
                <p>{{ __('Receba promoções exclusivas e novidades no seu e-mail.') }}</p>
            </div>
            <div v-if="!newsletter.success" class="newsletter-form">
                <input v-model="newsletter.email" type="text" placeholder="{{ __('O seu e-mail') }}">
                <button v-on:click="addNewsletter">{{ __('Subscrever') }}</button>
                <div v-if="newsletter.error" class="error">${newsletter.error[0]}</div>
            </div>
            <div v-else class="newsletter-success alert alert-success">
                {{ __('Registo concluído com sucesso!') }}
            </div>
        </div>
    </div>

    <div class="destaques">
        <div class="container grid-4">
            <div class="item">
                <svg class="sprite-icon"><use href="/img/footer_icons.svg#apoio_cliente"></use></svg>
                <div class="text">
                    <h4>{{ __('Apoio ao Cliente') }}</h4>
                    <p>{{ __('Telefone, e-mail ou chat.') }}</p>
                </div>
            </div>
            <div class="item">
                <svg class="sprite-icon"><use href="/img/footer_icons.svg#entrega"></use></svg>
                <div class="text">
                    <h4>{{ __('Entrega Rápida') }}</h4>
                    <p>{{ __('Em todo o país.') }}</p>
                </div>
            </div>
            <div class="item">
                <svg class="sprite-icon"><use href="/img/footer_icons.svg#satisfacao"></use></svg>
                <div class="text">
                    <h4>{{ __('Satisfação garantida') }}</h4>
                    <p>{{ __('Feedback dos nossos clientes.') }}</p>
                </div>
            </div>
            <div class="item">
                <svg class="sprite-icon"><use href="/img/footer_icons.svg#qualidade"></use></svg>
                <div class="text">
                    <h4>{{ __('Pagamento Seguro') }}</h4>
                    <p>{{ __('Vários métodos disponíveis.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="links">
        <div class="container grid-4">
            <div class="link-group">
                <h3>{{ __('Links Úteis') }}</h3>
                <ul>
                    <li><a href="{{ locale_url('lojas') }}">{{ __('As nossas lojas') }}</a></li>
                    <li><a href="{{ locale_url('contactos') }}">{{ __('Contactos') }}</a></li>
                    <li><a href="{{ locale_url('ajuda/termos-e-condicoes') }}">{{ __('Termos e Condições') }}</a></li>
                    <li><a href="{{ locale_url('ajuda/politica-privacidade') }}">{{ __('Política de Privacidade') }}</a></li>
                    <li><a href="{{ locale_url('ajuda/politica-de-cookies') }}">{{ __('Política de Cookies') }}</a></li>
                    <li><a href="{{ locale_url('ajuda/politica-de-envios') }}">{{ __('Política de Envios e Entregas') }}</a></li>
                    <li><a href="{{ locale_url('ajuda/politica-de-devolucoes') }}">{{ __('Política de Devoluções e Reembolsos') }}</a></li>
                    <li><a href="{{ locale_url('ajuda/resolucao-alternativa-litigios') }}">{{ __('Resolução Alternativa de Litígios') }}</a></li>
                    <li><a href="https://www.livroreclamacoes.pt/inicio/" target="_blank" rel="noopener">{{ __('Livro de Reclamações') }}</a></li>
                    <li><a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">{{ __('Resolução de Litígios em Linha (UE)') }}</a></li>
                </ul>
            </div>

            <div class="link-group">
                <h3>{{ __('Horário') }}</h3>
                <p>{{ __('Segunda a Sexta: 09h - 22h') }}</p>
                <p>{{ __('Sábado: 9h - 18h') }}<br><small>{{ __('Exclusivo loja online') }}</small></p>
            </div>

            <div class="link-group">
                <h3>{{ __('Contactos') }}</h3>
                <p>DFP Interiores, Unipessoal Lda</p>
                <p>Rua José Francisco Fragoso, n.º 45<br>7080-035 Vendas Novas</p>
                <p><a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a></p>
                <p><a href="tel:+351912026453">+351 912 026 453</a></p>
                <p><small>NIF 504074571</small></p>
            </div>

            <div class="link-group">
                <h3>{{ __('Métodos de Pagamento') }}</h3>
                <div class="payment-methods">
                    <img src="/img/payments/visa.svg" alt="Visa">
                    <img src="/img/payments/paypal.svg" alt="PayPal">
                    <img src="/img/payments/mastercard.svg" alt="Mastercard">
                    <img src="/img/payments/amex.svg" alt="American Express">
                    <img src="/img/mbway.png" alt="MB Way">
                    <img src="/img/mb.png" alt="Multibanco">
                    <img src="/img/payments/klarna.svg" alt="Klarna">
                    <img src="/img/payments/revolut.svg" alt="Revolut Pay">
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div>
                <p>
                    {{ __('Preços válidos salvo erro de publicação e limitados ao stock existente.') }}
                    <br>
                    {{ __('Os preços riscados são os Preços de Venda recomendados pelos fabricantes. Válido apenas na loja Online.') }}
                    <br>
                    {{ __('Copyright 2026 - DFP Interiores todos os direitos reservados.') }}
                </p>
            </div>
            <div>
                <small>{{ __('Cofinanciado por') }}</small>
                <div class="cofinanciado-logos">
                    <img src="/img/compete.png" alt="Portugal 2020">
                </div>
            </div>
        </div>
    </div>
</footer>

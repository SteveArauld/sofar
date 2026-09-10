@extends('layouts.app')

@section('title', 'Política de Privacidade | DFP Interiores')

@push('scripts')
<script src="/assets/vue/cookies.js"></script>
<script src="/assets/modulos/ajuda.js"></script>
@endpush

@section('content')
@verbatim
<div class="container breadcrumb">
    <div>
        <a href="/">Início</a> 
                                    <a href="/">Ajuda</a>
                                                <h1>Política de Privacidade</h1>
                        </div>
    <div class='actions'>
        
    </div>
</div>
	<div class="container">
		<section class="privacy-policy">

			<h3>1. Princípios Gerais</h3>
			<p>A proteção da privacidade e dos dados pessoais dos Clientes e Utilizadores constitui uma prioridade para a DFP Interiores.</p>
			<p>Comprometemo-nos a tratar os dados pessoais de forma lícita, leal e transparente, assegurando a sua confidencialidade e segurança, em conformidade com a legislação aplicável.</p>
			<p>Recomenda-se a leitura desta Política de Privacidade, bem como dos Termos e Condições.</p>

			<h3>2. Responsável pelo Tratamento</h3>
			<p>A DFP Interiores é a entidade responsável pelo tratamento dos dados pessoais.</p>

			<h3>3. Dados Pessoais Recolhidos</h3>
			<ul>
				<li>Nome e identificação fiscal</li>
				<li>Email, telefone e morada</li>
				<li>Dados de compras e histórico de encomendas</li>
				<li>Dados de navegação (cookies)</li>
			</ul>

			<h3>4. Finalidades e Base Legal</h3>
			<p>Os dados são tratados para:</p>
			<ul>
				<li>Execução de contrato (encomendas e entregas)</li>
				<li>Cumprimento de obrigações legais</li>
				<li>Interesses legítimos (melhoria do serviço)</li>
				<li>Marketing (com consentimento)</li>
			</ul>
			<p>O consentimento pode ser retirado a qualquer momento.</p>

			<h3>5. Partilha de Dados</h3>
			<p>Os dados podem ser partilhados com:</p>
			<ul>
				<li>Prestadores de serviços (transportadoras, pagamentos, IT)</li>
				<li>Autoridades legais, quando exigido</li>
			</ul>
			<p>Todos os terceiros estão contratualmente obrigados a proteger os dados.</p>

			<h3>6. Transferências Internacionais</h3>
			<p>Caso ocorram transferências para fora do Espaço Económico Europeu, garantimos a aplicação de mecanismos legais adequados.</p>

			<h3>7. Conservação dos Dados</h3>
			<p>Os dados são conservados apenas pelo tempo necessário para cumprir as finalidades legais e contratuais, sendo posteriormente eliminados ou anonimizados.</p>

			<h3>8. Direitos dos Utilizadores</h3>
			<p>O utilizador tem direito a:</p>
			<ul>
				<li>Acesso aos seus dados</li>
				<li>Retificação</li>
				<li>Apagamento</li>
				<li>Limitação do tratamento</li>
				<li>Portabilidade</li>
				<li>Oposição</li>
				<li>Retirada do consentimento</li>
			</ul>
			<p>Pode exercer os seus direitos através de:
				<strong><a href="mailto:geral@dfpinteriores.pt">geral@dfpinteriores.pt</a></strong>
			</p>
			<p>Tem também o direito de apresentar reclamação junto da Comissão Nacional de Proteção de Dados (CNPD).</p>

			<h3>9. Segurança</h3>
			<p>Adotamos medidas técnicas e organizativas adequadas, incluindo:</p>
			<ul>
				<li>Controlo de acessos</li>
				<li>Sistemas de segurança e monitorização</li>
				<li>Encriptação de dados</li>
			</ul>

			<h3>10. Responsabilidade do Utilizador</h3>
			<p>O utilizador deve manter a confidencialidade dos seus dados de acesso e garantir a segurança dos seus dispositivos.</p>

			<h3>11. Cookies</h3>
			<p>Utilizamos cookies para:</p>
			<ul>
				<li>Funcionamento do website</li>
				<li>Análise de utilização</li>
				<li>Personalização de conteúdos e marketing</li>
			</ul>
			<p>Pode gerir os cookies nas definições do seu navegador.</p>

			<h3>12. Links de Terceiros</h3>
			<p>O website pode conter links para terceiros, não sendo a DFP Interiores responsável pelas suas políticas de privacidade.</p>

			<h3>13. Marketing</h3>
			<p>O envio de comunicações comerciais depende do consentimento do utilizador, podendo este ser retirado a qualquer momento.</p>

			<h3>14. Contactos</h3>
			<p>Email: <a href="mailto:geral@dfpinteriores.pt">geral@dfpinteriores.pt</a></p>
			<p>Morada: Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas – Portugal</p>

			<h3>15. Atualizações</h3>
			<p>A presente Política pode ser atualizada a qualquer momento, sendo as alterações publicadas no website.</p>

			<h3>16. Aceitação</h3>
			<p>Ao utilizar este website, declara que leu e aceita esta Política de Privacidade.</p>
			<p>A utilização por menores deve ser acompanhada por um adulto responsável.</p>

		</section>
	</div>
@endverbatim
@endsection

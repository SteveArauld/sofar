window.pageLogic = function(ob) {

	const Cart = ref(ob.Cart || {});
	const Cliente = ref(ob.Cliente || {});
	const selecionadas = computed(() => {
		return {
			"entrega": Cliente.value?.moradas?.find(m => m.uid == Cliente.value?.morada_entrega_hash) ?? null,
			"faturacao": Cliente.value?.moradas?.find(m => m.uid == Cliente.value?.morada_faturacao_hash) ?? null,
		}
	});
	
	/* const criarGestor = (tipo) => ({
		modo: 'resumo', // resumo, lista, editar, novo
		selecionada: ref(selecionadas.value[tipo]) || null,
		temp: { nome: '', morada: '', cpostal: '', cidade: '' },
		tipo: tipo,
		erros: {}
	}); */

	const criarGestor = (tipo) => ({
		modo: 'resumo', // resumo, lista, editar, novo
		selecionada: selecionadas.value[tipo] || null,
		temp: { nome: '', morada: '', cpostal: '', cidade: '' },
		tipo: tipo,
		erros: {}
	});


	// Instancia das moradas de entrega e faturação
	const entrega = ref(criarGestor('entrega'));
	const faturacao = ref(criarGestor('faturacao'));
	const mesmaMorada = ref(true);

	// Depois do login atualizar as moradas
	watch(() => selecionadas, (novoCliente) => {
		if (novoCliente && novoCliente.moradas) {
			entrega.value.selecionada = selecionadas.value.entrega;
			faturacao.value.selecionada = selecionadas.value.faturacao;
		}
	}, { immediate: true, deep: true });

	// Funções de Ação
	const abrirFormulario = (gestor, dados = null) => {
		if (dados) {
			gestor.temp = { ...dados };
			gestor.modo = 'editar';
		} else {
			gestor.temp = { nome: '', morada: '', cpostal: '', cidade: '' };
			gestor.modo = 'novo';
		}
		console.log("Gestor", gestor);
	};


	const getValorEntrega = (tipoEntrega, codigoPostal) => {
		const valorDefault = tipoEntrega.valor

		const cp = Number(
			String(codigoPostal || '')
			.replace(/\D/g, '')
			.slice(0, 4)
		)

		if (!cp || !tipoEntrega.range_cp) {
			return valorDefault
		}

		for (const [range, valor] of Object.entries(tipoEntrega.range_cp)) {
			const [inicio, fim] = range.split('-').map(Number)

			if (cp >= inicio && cp <= fim) {
			return valor
			}
		}

		return valorDefault;
	}

	const tiposEntregaComValor = computed(() => {
		let codigoPostal = null;
		
		if(entrega.value?.selecionada?.cpostal) 
			codigoPostal = entrega.value.selecionada.cpostal;
		else
			codigoPostal = mesmaMorada.value ? form.value.fatura.cpostal : form.value.entrega.cpostal;
		return Object.entries(Cart.value.tipos_entrega).map(([key, tipo]) => {
			return {
				key,
				...tipo,
				valor_aplicado: getValorEntrega(tipo, codigoPostal)
			}
		})
	})


	const selecionar = (gestor, morada) => {
		gestor.selecionada = morada;
		gestor.modo = 'resumo';
		
		if (gestor.tipo === 'entrega') {
			// Atualizar os portes consoante o codigo postal
			if (typeof atualizarPortes === 'function') atualizarPortes(morada.cpostal);
			
			// Sincronizar faturação se a checkbox estiver ativa
			if (mesmaMorada.value) {
				faturacao.selecionada = morada;
			}
		}
	};

	const guardarMorada = async (gestor) => {
		const { uid } = gestor.temp;
		const url = `/cliente/moradas_api${uid ? `/${uid}` : ''}`;
		try {
			// Post (nova morada) put (editar morada)
			const { data: moradaFinal } = await axios[uid ? 'put' : 'post'](url, gestor.temp);

			// Atulizar lista de moradas
			if (uid) {
				const index = Cliente.value.moradas.findIndex(m => m.uid == uid);
				if (index !== -1) Cliente.value.moradas[index] = { ...moradaFinal };
			} else {
				Cliente.value.moradas.push(moradaFinal);
			}
			gestor.erros = {}; 
			selecionar(gestor, moradaFinal);
			gestor.modo = 'resumo'; 
			return true;
		} catch (error) {
			// Gestão de erros 
			gestor.erros = error.response?.data?.errors || { message: "Erro inesperado" };
			return false;
		}
	};

	const aplicarMascaraCP = (valor) => {
		if (!valor) return '';
		valor = valor.replace(/\D/g, ''); // Remove letras
		valor = valor.substring(0, 7);    // Limita a 7 dígitos
		if (valor.length > 4) {
			return valor.substring(0, 4) + '-' + valor.substring(4);
		}
		return valor;
	};

	const aplicarMascaraTelefone = (valor) => {
		if (!valor) return '';
		valor = valor.replace(/\D/g, ''); // Remove tudo o que não é número
		valor = valor.substring(0, 9);    // Limita a 9 dígitos
		// Formata como 912 345 678 para melhor leitura
		const match = valor.match(/^(\d{3})(\d{3})(\d{3})$/);
		if (match) {
			return `${match[1]} ${match[2]} ${match[3]}`;
		}
		return valor;
	};


	/* 
		Checkout Login 
	*/
	const checkoutLogin = ref({
		username: '',
		password: '',
		errors: {},
		error: ''
	});

	 
	const doCheckoutLogin = async () => {
		try {
			const response = await axios.post('/cliente/login', {
				username: checkoutLogin.value.username,
				password: checkoutLogin.value.password,
				returnData: true
			});
			Cliente.value = response.data;
			entrega.value = criarGestor('entrega');
			faturacao.value = criarGestor('faturacao');
		} catch(error) {
			checkoutLogin.value.errors = error.response?.data?.errors;
			checkoutLogin.value.error = error.response?.data?.error;
		}
		return false; 
	};

	const doRecoverPassword = async () => {
		try {
			await axios.post('/cliente/enviar_token_recuperacao', {
				username: checkoutRecover.value.username
			});
		} catch(error) {
			checkoutRecover.value.errors = error.response.data.errors;
			checkoutRecover.value.error = error.response.data.error;
		}
		return false; 
	};

	/* Checkout */
	const entrega_id = ref(null);
	const pagamento_id = ref(null);
	const usa_credito = ref(false);



	const totais = computed(() => {
		let total = Cart.value?.total || 0;
		let subtotal = Cart.value?.subtotal || 0;
		const entrega_valor = Cart.value.tipos_entrega[entrega_id.value]?.valor || 0;
		total += entrega_valor;
		if(usa_credito.value)
			total -= Cliente.value?.credito_disponivel || 0;
		
		/* Scalapay */
		const metodos_pagamento = JSON.parse(JSON.stringify(Cart.value.tipos_pagamento));
		if(metodos_pagamento.SCALAPAY){
			metodos_pagamento.SCALAPAY.descricao = metodos_pagamento.SCALAPAY.descricao.replace("PRESTACAO3", (total / 3).toFixed(2));
			metodos_pagamento.SCALAPAY.descricao = metodos_pagamento.SCALAPAY.descricao.replace("PRESTACAO4", (total / 4).toFixed(2));
		}
		
		return {
			subtotal: subtotal,
			total: total,
			entrega: entrega_valor,
			credito: Cliente.value?.credito_disponivel || 0,
			pagamento: 0,
			metodos_pagamento: metodos_pagamento
		};	
	});

	const form = ref({ 
			fatura: {
			nome: '',
			telefone: '',
			email: '',
			nif: ''
		}, entrega: {
			nome: '',
			morada: '',
			cpostal: '',
			cidade: ''	
		}, 
		notas: '', 
		pagamento_mbway: '',
		pagamento_id: null,
		entrega_id: null
	 });

	const extraData = reactive({});
	const validaCamposExtraPagamento = (e) => {
		const { name, value } = e.target;
		if(name) 
			extraData[name] = value;
	};

	const startValidate = ref(false);
	const aceito_termos = ref(false);
	const criar_conta = ref(false);
	const requiredNif = computed(() => {
		if(pagamento_id.value == 'SEQURA' || pagamento_id.value == 'EUPAGO_COFIDIS')
			return true;
		return false; 
	});

	function focarNoPrimeiroErro() {
		const primeiroErro = document.querySelector('.error:not(:empty), .is-invalid');

		if (primeiroErro) {
			primeiroErro.scrollIntoView({ 
				behavior: 'smooth',
				block: 'center' 
			});

			if (primeiroErro.tagName === 'INPUT' || primeiroErro.tagName === 'SELECT') {
				primeiroErro.focus({ preventScroll: true });
			}
		}
	}


	const entrega_pudo = ref(null);
	const formErrors = computed(() => {
		errors = {};

		if (!startValidate.value) 
			return { errors: errors };

		/* Regras de validação dos campos de extra de pagamento */
		

		if(selecionadas.value.entrega) {
			if(!entrega_id.value)
				errors["entrega_id"] = "Selecione uma opção de entrega.";
		
			if(!pagamento_id.value)
				errors["pagamento_id"] = "Selecione uma opção de pagamento.";

			if(requiredNif.value && Validator.nifpt("Insira um NIF válido.")(form.value.nif) !== true)
				errors["nif"] = "Insira um NIF válido.";

			if(!aceito_termos.value)
				errors["aceito_termos"] = "Tem que aceitar os termos e condições para avançar.";
			
			const metodo_pagamento = Cart.value.tipos_pagamento?.[pagamento_id.value]; 
			if (metodo_pagamento?.extra?.validation) {
				const { field, rule, message } = metodo_pagamento.extra.validation;
				const valorInserido = extraData[field];
				const validacao = Validator[rule](message)(valorInserido);
			
				if (validacao !== true) {
					errors[pagamento_id.value] = validacao;
				}
			} 

			return { errors: errors };
		}

		const regras = {
			'fatura.nome': Validator.minWords(2, "Insira o nome e apelido."),
			'fatura.morada': Validator.required("Insira a morada."),
			'fatura.cpostal': Validator.codPostal("Insira o código postal."),
			'fatura.cidade': Validator.required("Insira a cidade."),
			'fatura.telefone': Validator.telemovel("Insira um telemovel."),
			'email': Validator.email("Insira um email válido."),
			'nif': requiredNif.value ? Validator.nifpt("Insira um NIF válido.") : () => true
		};
	
		if(!mesmaMorada.value) {
			regras['entrega.nome'] = Validator.minWords(2, "Insira o nome e apelido.");
			regras['entrega.morada'] = Validator.required("Insira a morada.");
			regras['entrega.cpostal'] = Validator.codPostal("Insira o código postal.");
			regras['entrega.cidade'] = Validator.required("Insira a cidade.");
		}

		if(criar_conta.value){
			regras['password'] = Validator.required("Insira uma password.");
			regras['password_confirmar'] = Validator.required("Insira uma password.");
		}
	
		const result = validateForm(form.value, regras);

		if(!entrega_id.value)
			result.errors["entrega_id"] = "Selecione uma opção de entrega.";
		
		if(!pagamento_id.value)
			result.errors["pagamento_id"] = "Selecione uma opção de pagamento.";

		if(!aceito_termos.value)
			result.errors["aceito_termos"] = "Tem que aceitar os termos e condições para avançar.";

		if(criar_conta.value  && form.value.password != form.value.password_confirmar)
			result.errors["password_confirmar"] = "As passwords nao correspondem.";

		//console.log("Pudo", entrega_pudo.value, entrega_id.value == 'LEVANTAMENTO');
		if((entrega_pudo.value === null || entrega_pudo.value === undefined) && entrega_id.value == 'LEVANTAMENTO')
			result.errors["entrega_pudo"] = "Selecione uma opção de entrega.";

		
		return result;
	});



	const isFormValid = computed(() => Object.keys(formErrors.value.errors).length === 0);
	const bloqueiaBtnCheckout = ref(false); 
	const confirmaEncomenda = async () => {
		startValidate.value = true;
		setTimeout(focarNoPrimeiroErro, 100);
		console.log("valida", extraData);
		const pagamentos_extra = { ...extraData };
		if (isFormValid.value) {
			bloqueiaBtnCheckout.value = true;
			
			try {
				
				const response = await axios.post('/carrinho/confirma_encomenda', {
					cliente: {
						fatura: form.value.fatura,
						entrega: form.value.entrega,
						email: form.value.email,
						nif: form.value.nif,
					},
					extra: extraData,
					criar_conta: criar_conta.value,
					password: form.value.password,
					mesma_morada: mesmaMorada.value,
					morada_entrega: selecionadas?.value?.entrega?.uid ?? null,
					morada_fatura: selecionadas?.value?.faturacao?.uid ?? null,
					entrega: entrega_id.value,
					pagamento: pagamento_id.value,
					usa_credito: usa_credito.value ?? false,
					notas: form.value.notas
				});
				if(response.data.result === "success"){
					window.location.href = response.data.redirect;
					//alert("Encomenda confirmada!"); // Lógica de envio
				}else if(response.data.result === "error"){
					console.error("Erro ncomenda:", response.data);	
					formErrors.value.errors = [ response.data.error ] || {};
					bloqueiaBtnCheckout.value = false;
				}
			} catch(error) {
				console.error("Erro ao confirmar encomenda:", error);
				const erros = error?.response?.data?.errors || null; 
				alert(Object.values(erros).flat().join('\n') || "Ocorreu um erro, tente novamente.");
				bloqueiaBtnCheckout.value = false;
			}
		}
	}
		
	const checkout_caixaLogin = ref("login");

	// Máscara de input para formato 9999-999
	class MascaraNumericaComposta {
		constructor(seletor) {
			this.inputs = document.querySelectorAll(seletor);
			
			if (this.inputs.length > 0) {
				this.inputs.forEach(input => {
					input.addEventListener('input', (e) => this.aplicarMascara(e));
					input.addEventListener('keydown', (e) => this.validarTecla(e));
				});
			}
		}

		aplicarMascara(e) {
			let valor = e.target.value.replace(/\D/g, '');
			
			if (valor.length > 7) {
				valor = valor.slice(0, 7);
			}

			// Aplica o formato 9999-999
			if (valor.length <= 4) {
				e.target.value = valor;
			} else {
				e.target.value = valor.slice(0, 4) + '-' + valor.slice(4);
			}
		}

		validarTecla(e) {
			const teclasControlo = [
				'Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 
				'ArrowLeft', 'ArrowRight', 'Home', 'End'
			];

			const asAtalhoCtrl = e.ctrlKey || e.metaKey;
			const ehNumero = /^[0-9]$/.test(e.key);

			if (!teclasControlo.includes(e.key) && !asAtalhoCtrl && !ehNumero) {
				e.preventDefault();
			}
		}
	}


	// Countdown
	const iniciarCountdown = () => {
		const divCountdown = document.querySelector('[data-validade]');
		if (!divCountdown) return;

		const atualizarContagem = () => {
			const dataValidade = divCountdown.getAttribute('data-validade');
			const dataValidadeMs = new Date(dataValidade).getTime();
			const agora = new Date().getTime();
			const diferenca = dataValidadeMs - agora;

			if (diferenca <= 0) {
				divCountdown.textContent = '0';
				return;
			}

			const dias = Math.floor(diferenca / (1000 * 60 * 60 * 24));
			const horas = Math.floor((diferenca % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutos = Math.floor((diferenca % (1000 * 60 * 60)) / (1000 * 60));
			const segundos = Math.floor((diferenca % (1000 * 60)) / 1000);

			divCountdown.textContent = `${minutos}:${segundos < 10 ? '0' : ''}${segundos}`; 
		};

		atualizarContagem();
		setInterval(atualizarContagem, 1000);
	};



	// Chamar countdown quando o componente monta
	onMounted(() => {
		iniciarCountdown();
		new MascaraNumericaComposta('.codigoPostal');
	});

    return {

		criarGestor,
		abrirFormulario,
		selecionar,
		guardarMorada,
		aplicarMascaraCP,
		aplicarMascaraTelefone,
		confirmaEncomenda,
		validaCamposExtraPagamento,
		doCheckoutLogin, 
		watch,

		checkoutLogin, 
		requiredNif,

		criar_conta,
		entrega,
		faturacao,
		mesmaMorada,
		
		usa_credito,
		entrega_id,
		entrega_pudo,
		pagamento_id,
		aceito_termos,
		usa_credito,
		totais,
		form,
		formErrors,
		bloqueiaBtnCheckout,
		checkout_caixaLogin,


		tiposEntregaComValor
    };
};
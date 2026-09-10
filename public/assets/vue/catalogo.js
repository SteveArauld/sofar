window.pageLogic = function(ob) {
    const pv = ref({ variations: []});
    const artigo = ref(window.artigo || {});
	const images = ref(artigo.value.images || []);


	const Imperline = ref();
	const selectedImper = ref();
	const selected = ref({});
	const segurosSelecionados = ref([]);

    const loader = ref(false);
    
    const modalFinalizar = ref({
        visible: false,
    });

	const StockLojas = ref({
		vid : null, 
		lojas: [
			{nome: "Maia", stock: "Disponivel"},
			{nome: "Porto", stock: "Disponivel"},
			{nome: "Gaia", stock: "Indisponível"},
		], 
		visible: false
	});

	

	const validaSeguro = (selected) => {
		let seguros = selectedOption.value.seguros;
		if (segurosSelecionados.value.includes(selected.erpid)) {
			segurosSelecionados.value = segurosSelecionados.value.filter(val => {
				let opt = seguros.find(o => o.erpid == val);
				return opt.tipo !== selected.tipo || opt.erpid === selected.erpid
			})
		}
	}

	const consultaStockLojas = () => {
		StockLojas.value.loading = true;
		axios.post('/catalogo/getstock', { id : selectedOption.value?.pid })
		.then(function (data) {
			StockLojas.value.vid = selectedOption.value?.pid;
			StockLojas.value.lojas = data.data;
			StockLojas.value.visible = !StockLojas.value.visible;
			StockLojas.value.loading = false;
		})
		.catch(function (error) {
			console.log(error);
		});
		
	}
	
	const availableOptions = computed(() => { 
		
		const options = {};
		if(typeof ProdVariations.variations !== "undefined")
			pv.value.variations = ProdVariations.variations;
		pv.value.variations.forEach((variation) => {
			
			
			Object.keys(variation).forEach((key) => {
				if (
					key !== "prazo_entrega" &&
					key !== "lojascomstock" &&
					key !== "stocklojas" &&
					key !== "stock" && 
					key !== "preco" && 
					key !== "precopromo" && 
					key !== "pid" && 
					key !== "precoantes" && 
					key !== "seguros" &&
					key !== "imperline" &&
					key !== "image" && 
					key !== "images"
					) {
				if (!options[key]) {
					options[key] = [];
				}
				const value = variation[key];
				if (typeof value === "object") {
					if (!options[key].some((v) => v.name === value.name)) {
					options[key].push(value);
					}
				} else {
					if (!options[key].includes(value)) {
					options[key].push(value);
					}
				}
				}
			});
		
		});
		
		return options;
	});

	const minPrice = computed(() => {
		if(Object.entries(pv.value.variations).length == 0)
			return artigo.value.preco;
		return Math.min(...pv.value.variations.map((v) => Number(v.preco)));
	});
	

	const selectedPrice = computed(() => {
		if(Object.entries(pv.value.variations).length == 0){
			return 0;
		}
		//let extra = Number(selectedImper.value ? Imperline.value.find(s => s.id == selectedImper.value)?.preco || 0 : 0);
		let extra = Number(selectedImper.value?.preco ?? 0); 

		variation = pv.value.variations.find(
			(v) => Object.keys(selected.value).every( 
				(key) => v[key]?.name === selected.value[key] 
			)
		);
		
		let extraseguro = segurosSelecionados.value.reduce((acumula, valor) => {
			let extra = selectedOption.value.seguros.find(o => o.erpid == valor);
			return acumula + Number(extra.preco) ?? 0;
		}, 0);
		
		extra = extra + extraseguro;
		 
		
		return !variation ? minPrice.value + extra : Number(variation.preco) + extra;
	});

	const precos = computed(() => {
		
		let precoAntes = selectedOption.value?.precoantes ?? variacaoMaisBarata?.value.precoantes ?? 0;
		/* Correção das Protecs quando não tem filhos */
		
		let precoAtual = selectedPrice.value/*selectedOption.value?.preco*/ ?? variacaoMaisBarata?.value.preco;
		if(!selectedOption.value)
			precoAtual = minPrice.value/*selectedOption.value?.preco*/ ?? variacaoMaisBarata?.value.preco;
		//if(precoAntes == 0 || precoAntes == precoAtual)
		//	precoAntes = Math.trunc(precoAntes * 1.25);
		let percentDesconto = precoAntes && precoAtual && precoAntes > precoAtual ? `-${(((precoAntes - precoAtual) / precoAntes) * 100).toFixed(0)}%` : '';
	 
		return {
			antes: precoAntes > precoAtual ? precoAntes : null,
			atual: precoAtual,
			desconto: percentDesconto
		};
	});

	const variacaoMaisBarata = computed(() => {
		if (Object.entries(pv.value.variations).length === 0) {
			return artigo.value.variations.variations[0];
		}
		let maisbarato = pv.value.variations.reduce((prev, curr) => {
			return (Number(curr.preco) < Number(prev.preco)) ? curr : prev;
		});
		return maisbarato;
	});


	/* Slider de produto */
	const activeIndex = ref(0);
	let timer = null;

	const next = () => {
		activeIndex.value = (activeIndex.value + 1) % images.value.length;
	};

	const prev = () => {
		activeIndex.value = (activeIndex.value - 1 + images.value.length) % images.value.length;
	};

	const setActive = (index) => {
		activeIndex.value = index;
		stopAutoplay();
		startAutoplay();
	};
	
	const startAutoplay = () => {
		// Só inicia se houver mais de uma imagem
		if (images.value.length > 1) {
			timer = setInterval(next, 5000);
		}
	};

	const stopAutoplay = () => {
		if (timer) clearInterval(timer);
	};

	onMounted(() => {
		startAutoplay();
	});

	onUnmounted(() => {
		stopAutoplay();
	}); 

	const touchStartX = ref(0);
	const touchEndX = ref(0);

	// Quantos pixels o utilizador tem de arrastar para mudar a foto
	const minSwipeDistance = 50;

	const handleTouchStart = (e) => {
		touchStartX.value = e.touches[0].clientX;
	};

	const handleTouchEnd = (e) => {
		touchEndX.value = e.changedTouches[0].clientX;
		handleSwipe();
	};

	const handleSwipe = () => {
		const distance = touchStartX.value - touchEndX.value;

		if (Math.abs(distance) > minSwipeDistance) {
			if (distance > 0) {
				// Deslizou para a esquerda -> Próxima
				next();
			} else {
				// Deslizou para a direita -> Anterior
				prev();
			}
		}
	};
	/* Fim de slider */

	
	const selectedOption = computed(() => {
		
		if (pv.value.variations.length === 1) {
			const singleVar = pv.value.variations[0];
			Imperline.value = artigo.value.servicosExtra.filter(o => o.variation === singleVar?.pid);
			//StockLojas.value.visible = singleVar.stocklojas > 0;
			return singleVar;
		}

		
		const selectedKeys = Object.keys(selected.value);
		const availableKeys = Object.keys(availableOptions.value);

		if (selectedKeys.length === 0 || selectedKeys.length < availableKeys.length) {
			return false;
		}

		
		const foundVariation = pv.value.variations.find((v) => {
			return selectedKeys.every((key) => {
				// Verifica se a chave existe na variação e se o nome coincide
				return v[key] && v[key].name === selected.value[key];
			});
		});

		
		if (!foundVariation) {
			Imperline.value = []; // Limpa serviços se não houver variação
			images.value = artigo.value.images;
			stopAutoplay();
			startAutoplay();
			 
			activeIndex.value = 0;
			return null;
		}
		
		Imperline.value = artigo.value.servicosExtra.filter(o => o.variation === foundVariation?.pid);
		selectedImper.value = "";
 
		images.value = foundVariation?.images?.name?.length > 0 ? foundVariation?.images.name : artigo.value.images;
		stopAutoplay();
		startAutoplay();
		window.scrollTo({
			top: 0,
			behavior: 'smooth' // Rola suavemente
		});

		activeIndex.value = 0; // Reseta para a primeira imagem da variação

		return foundVariation;
	});

	// Aperçu immédiat : dès qu'UNE option (ex. COR) est choisie, on affiche la photo
	// de la 1re variante compatible — sans attendre que toutes les options soient
	// sélectionnées (sinon cliquer une couleur ne change rien à l'écran).
	watch(() => JSON.stringify(selected.value), () => {
		const sel = selected.value || {};
		const keys = Object.keys(sel).filter((k) => sel[k]);
		if (!keys.length) {
			images.value = artigo.value.images;
			activeIndex.value = 0;
			return;
		}
		const match = pv.value.variations.find((v) =>
			keys.every((k) => v[k] && v[k].name === sel[k])
		);
		if (match) {
			images.value = (match.images && match.images.name && match.images.name.length > 0)
				? match.images.name
				: artigo.value.images;
			activeIndex.value = 0;
			stopAutoplay();
			startAutoplay();
		}
	});
	
 
	function scrollToDiv(id) {
		const elemento = document.getElementById(id);
		const headerOffset = 300; // Ajuste aqui a altura do seu menu (ex: 100px)
		const elementPosition = elemento.getBoundingClientRect().top;
		const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
		window.scrollTo({
			top: offsetPosition,
			behavior: "smooth"
		});
	}

	const produtoErros = ref({
		
	})
	const addProduct = async () => {

	 
		if(Imperline.value?.length > 0 && !selectedImper.value){
			alert("Escolha uma opção de impermeabilização");
			scrollToDiv('Impermeabilizacao');
			
			return false;
		}
		
		let id = selectedOption.value?.pid;

		// Aucune option choisie : on retient automatiquement la 1ère variation
		// disponible (ou la 1ère tout court) au lieu de bloquer l'ajout.
		if (!id && pv.value.variations.length >= 1) {
			const auto = pv.value.variations.find(v => Number(v.stock) > 0) || pv.value.variations[0];
			id = auto?.pid;
			// reflète le choix dans l'interface si possible
			if (auto) {
				Object.keys(availableOptions.value).forEach((key) => {
					if (auto[key] && auto[key].name) selected.value[key] = auto[key].name;
				});
			}
		}

		loader.value = true;
		try {
			let response = await axios.post("/carrinho/add", {
				product: productId,
				variation: id,
				protec: selectedImper.value?.protec_erpid ?? '', //"document.querySelector(".Impermeabilizacao select")?.value || "",
				seguro: segurosSelecionados.value || ""
			});
	
			ob.Cart.value = response.data.cart;

			//addDataLayer(response.data.dl);
			window.dataLayer = window.dataLayer || [];
			dataLayer.push(JSON.parse(response.data.dl)); //dataLayer.push(response.data.dl);
			

			// Ne plus ouvrir le panier automatiquement : simple confirmation visuelle.
			if (typeof window.shopToast === 'function')
				window.shopToast('Produto adicionado ao carrinho 🛒');
			loader.value = false;
		} catch (err) {
			console.log(err);
			alert("Ocorreu um erro, tente novamente");
		}
	};
	
	const hasSomeStock = computed(() => {
		if(artigo.value?.vende_apenas_stock == 'N')
			return true;
		
		return pv.value.variations.some((variation) => variation.stock > 0)
	});

	const isInStock = computed(() => {
		const totalStockDisponivel = pv.value.variations.some(v => v.stock > 0);
		
		let hasStock = false;
		const hasSelection = Object.keys(selected.value).length > 0;

		if (!hasSelection) {
			hasStock = totalStockDisponivel;
		} else {
		
			hasStock = pv.value.variations.some((variation) => {
				const matchesSelection = Object.keys(selected.value).every((key) => {
					
					return variation[key]?.name.toLowerCase() === selected?.value[key].toLowerCase();
				});

				return matchesSelection && variation.stock > 0;
			});
		}

		//Definir o prazo de entrega (baseado no stock real, independentemente de vender sem stock)
		if (hasStock) {
			artigo.value.prazo_entrega = artigo.value.prazo_entrega_stock;
		} else {
			artigo.value.prazo_entrega = artigo.value.prazo_entrega_encomenda;
		}

		// Se o artigo permite vender sem stock ('N'), retorna sempre true
		if (artigo.value.vende_apenas_stock === 'N') {
			return true;
		}

		return hasStock;
	});

	const isOptionAvailable = (option, value) => {
		
		
		if(Object.entries(pv.value.variations).length <= 1)
			return false;

		/* let result = pv.value.variations.some(
			(variation) => variation[option]?.name.toLowerCase() === value.toLowerCase() && Object.keys(selected.value).every(
				(key) => key === option || !selected.value[key] || variation[key]?.name.toLowerCase() === selected.value[key]?.toLowerCase()
			) && variation.stock > 0
		); */

		let result = pv.value.variations.find((variation) => {
			const matchCurrentOption = variation[option]?.name?.toLowerCase() === value?.toLowerCase();
			const matchSelectedOptions = Object.keys(selected.value).every((key) => 
				key === option || 
				!selected.value[key] || 
				variation[key]?.name.toLowerCase() === selected?.value[key].toLowerCase()
			);
			return matchCurrentOption && matchSelectedOptions /* && hasStock */;
		});

		if(artigo.value.vende_apenas_stock == 'Y' && result?.stock <= 0)
			return false;
		if(!result?.pid)
			return false; 
		return true; 
		if(artigo.value.vende_apenas_stock == 'N')
			return true;
		return result;
	};

	
	const prestacaoScalapay = computed(() => {
		let price = selectedPrice.value ;
		if(price < 5 || price > 1500)
			return false; 
		
		return {
			prestacao3: (price / 3).toFixed(2),
			prestacao4: (price / 4).toFixed(2)
		}
	});

	const prestacaoSequra = computed(() => {
		let price = selectedPrice.value ;
		return (price / 3).toFixed(2);
	});
	const prestacaoCofidis = computed(() => {
		let price = Number(selectedPrice.value);
		if(price < 60 || price > 2500)
			return false; 
		return (price / 12).toFixed(2);
	});


	


 
    return {
        addProduct,
        isOptionAvailable,
        consultaStockLojas,
        validaSeguro,

		setActive,
		activeIndex,
		next,
		prev, 
		startAutoplay,
		stopAutoplay,
		handleTouchStart,
		handleTouchEnd,
		


		precos,
		//Cart,
		images,
        artigo,
        availableOptions,
        minPrice,
        selectedPrice,
        isInStock,
        selected,
        selectedOption,
        hasSomeStock,
		prestacaoScalapay,
        prestacaoSequra,
        prestacaoCofidis,
        StockLojas,
        loader, 
        modalFinalizar,
        Imperline,
        selectedImper,
        segurosSelecionados,
    };
};
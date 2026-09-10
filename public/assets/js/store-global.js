const {
                    createApp,
                    ref,
                    reactive,
                    computed,
                    nextTick,
                    watch,
                    onMounted,
                    onUnmounted
                } = Vue;

                const Validator = {
                    // Validação de Email
                    email: (customMsg) => (value) => {
                        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        const defaultMsg = "Email inválido";
                        return re.test(value) || customMsg || defaultMsg;
                    },

                    // Mínimo de palavras
                    minWords: (min, customMsg) => (value) => {
                        const words = (value || "").trim().split(/\s+/).filter(w => w.length > 0);
                        const defaultMsg = `Mínimo de ${min} palavras obrigatório`;
                        return words.length >= min || customMsg || defaultMsg;
                    },

                    // NIF Portugal
                    nifpt: (customMsg) => (value) => {
                        const nif = String(value);
                        const defaultMsg = "NIF inválido";
                        if (!/^[0-9]{9}$/.test(nif)) return customMsg || "NIF deve ter 9 dígitos";

                        const added = [1, 2, 3, 4, 5, 6, 7, 8, 9];
                        if (!added.includes(Number(nif[0]))) return customMsg || defaultMsg;

                        let checkSum = 0;
                        for (let i = 0; i < 8; i++) checkSum += nif[i] * (9 - i);

                        const res = checkSum % 11;
                        const checkDigit = res < 2 ? 0 : 11 - res;
                        return Number(nif[8]) === checkDigit || customMsg || defaultMsg;
                    },

                    // Telefone Fixo Portugal
                    telefone: (customMsg) => (value) => {
                        const defaultMsg = "Telefone fixo inválido (deve começar por 2)";
                        return /^[2][0-9]{8}$/.test(value) || customMsg || defaultMsg;
                    },

                    // Telemóvel Portugal
                    telemovel: (customMsg) => (value) => {
                        const defaultMsg = "Telemóvel inválido";
                        return /^[9][1236][0-9]{7}$/.test(value) || customMsg || defaultMsg;
                    },

                    // Código Postal (0000-000)
                    codPostal: (customMsg) => (value) => {
                        const defaultMsg = "Formato de código postal inválido (0000-000)";
                        return /^[0-9]{4}-[0-9]{3}$/.test(value) || customMsg || defaultMsg;
                    },

                    // Obrigatório
                    required: (customMsg) => (value) => {
                        const defaultMsg = "Campo obrigatório";
                        const isValid = value !== null && value !== undefined && value.toString().trim() !== "";
                        return isValid || customMsg || defaultMsg;
                    },

                    // Password complexa
                    password: (customMsg) => (value) => {
                        const val = value || "";
                        const rules = {
                            length: val.length >= 8,
                            upper: /[A-Z]/.test(val),
                            lower: /[a-z]/.test(val),
                            number: /[0-9]/.test(val)
                        };

                        if (!rules.length || !rules.upper || !rules.lower || !rules.number) {
                            return customMsg ||
                                "A senha deve ter pelo menos 8 caracteres, incluir maiúsculas, minúsculas e números";
                        }
                        return true;
                    }
                };

                function validateForm(data, rules) {
                    let errors = {};
                    const getDeepValue = (obj, path) => {
                        return path.split('.').reduce((acc, part) => acc && acc[part], obj);
                    };

                    for (let fieldPath in rules) {
                        const value = getDeepValue(data, fieldPath);
                        const result = rules[fieldPath](value);

                        if (result !== true) {
                            const errorKey = fieldPath.replace(/\./g, '_');
                            errors[errorKey] = result;
                        }
                    }

                    return {
                        isValid: Object.keys(errors).length === 0,
                        errors
                    };
                }

                const Global = {
                    delimiters: ['${', '}'],
                    setup() {

                        const offside = reactive({
                            overlay: false,
                            carrinho: false,
                            cliente: false,
                            procurar: false,
                            toggle: (menu) => {
                                //alert(menu);
                                let currentState = offside[menu];
                                offside.overlay = false;
                                offside.carrinho = false;
                                offside.cliente = false;
                                offside.procurar = false;
                                offside.mobilemenu = false;
                                offside.filtro = false;

                                //offside.overlay = !offside.overlay;
                                offside.overlay = offside[menu] = !currentState;

                                if (offside[menu] && menu === "procurar")
                                    nextTick(() => {
                                        document.getElementById("mobileProcurarField").focus();
                                    });

                                if (menu === "all") {
                                    offside.overlay = false;
                                    offside.carrinho = false;
                                    offside.cliente = false;
                                    offside.procurar = false;
                                    offside.mobilemenu = false;
                                    offside.filtro = false;
                                    return false;
                                }

                            }
                        });


                        /* Search Logic */
                        const Config = ref(_Config);
                        const Settings = ref(_Settings);
                        const Cliente = ref(_Cliente);
                        const searchBox = ref(false);
                        const searchField = ref();
                        const caixaProcura = ref();
                        const campoProcura = ref();
                        const searchResults = reactive({
                            results: _searchResults.results ?? [],
                            history: _searchResults.history ?? [],
                            sugestions: [],
                            categorias: _searchResults.categorias ?? []
                        });


                        const Cart = ref(_Cart);
                        const extraLogic = typeof window.pageLogic === 'function' ?
                            window.pageLogic({
                                Cart: Cart,
                                offside: offside,
                                Config: Config,
                                Cliente: Cliente,
                                Settings: Settings
                            }) : {};

                        const cookiesLogic = typeof window.cookiesLogic === 'function' ?
                            window.cookiesLogic({}) : [];

                        let timerid = null;

                        axios.defaults.headers.common['Accept'] = 'application/json';
                        axios.defaults.headers.common['Content-Type'] = 'application/json';
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = window.CSRF_TOKEN;


                        const handleClickOutside = (event) => {
                            if ((caixaProcura.value && !caixaProcura.value.contains(event.target)) &&
                                (campoProcura.value && !campoProcura.value.contains(event.target))
                            ) {
                                searchBox.value = false;
                            }
                        };
                        const openSearchBox = async () => {

                            await nextTick();
                            if (searchBox.value)
                                document.addEventListener("click", handleClickOutside);
                            else
                                document.removeEventListener("click", handleClickOutside);
                        }

                        const setSearch = (value) => {
                            searchField = value.value;
                            dooSearch();
                        };
                        const searchGo = () => {
                            window.location.href = '/procura/' + searchField.value;
                        };
                        //let timerid = null;
                        const dooSearch = () => {
                            // Limpa o timer anterior para evitar múltiplas chamadas
                            if (timerid) clearTimeout(timerid);

                            timerid = setTimeout(async function() {
                                const query = searchField.value.trim();
                                if (query.length < 3) {
                                    searchResults.categorias = [];
                                    searchResults.results = [];
                                    return;
                                }

                                try {
                                    const response = await axios.get('/procura/sugestoes?q=' +
                                        encodeURIComponent(query));

                                    const results = response.data?.results || [];
                                    searchBox.value = true;


                                    const productHits = results[1]?.hits || [];
                                    productHits.forEach(element => {
                                        // Limpeza do link da imagem
                                        const rawImage = element?.images?.[0] || "";
                                        element.image_link = rawImage.replace(
                                            "images/1000-1000/index.html", "");

                                        element.title = element.name;
                                        element.availability = element.in_stock ? "INSTOCK" :
                                            "OUTOFSTOCK";
                                    });


                                    searchResults.categorias = results[0]?.hits || [];
                                    searchResults.results = productHits;

                                } catch (error) {

                                }
                            }, 500);
                        };

                        /* Cart Logic */
                        const cartRemove = (item) => {
                            if (!confirm("Tem a certeza que quer remover este artigo?"))
                                return false;

                            axios.post('/carrinho/remove', {
                                    id: item.linhaid,
                                })
                                .then(function(result) {

                                    Cart.value = result.data.cart;

                                    window.dataLayer = window.dataLayer || [];
                                    dataLayer.push(result.data.dl);



                                })
                                .catch(function(error) {
                                    console.log(error);
                                });
                        };


                        /* Product Logic */

                        const sidelogin = ref({
                            username: '',
                            password: '',
                            errors: {},
                            error: ''
                        });
                        const doSideLogin = (e) => {
                            axios.post('/cliente/login', {
                                    username: sidelogin.value.username,
                                    password: sidelogin.value.password
                                })
                                .then(function(result) {
                                    if (result.data.success)
                                        window.location.href = 'cliente.html';
                                })
                                .catch(function(error) {
                                    sidelogin.value.errors = error.response.data.errors;
                                    if (error.response.data.error)
                                        sidelogin.value.error = error.response.data.error;

                                });
                            return false;
                        }

                        const sideRegisto = ref({
                            nome: '',
                            email: '',
                            telemovel: '',
                            password: '',
                            errors: {},
                            error: ''
                        });
                        const doSideRegisto = (e) => {

                            const regras = {
                                nome: Validator.minWords(2, "Insira nome e apelido"),
                                email: Validator.email("Insira um email válido"),
                                telemovel: Validator.telemovel("Insira um telefone válido"),
                                password: Validator.required("Insira uma password")
                            };

                            const result = validateForm(sideRegisto.value, regras);


                            if (!result.isValid) {
                                sideRegisto.value.errors = result.errors;
                                return false;
                            }

                            axios.post('/cliente/registo', {
                                    nome: sideRegisto.value.nome,
                                    email: sideRegisto.value.email,
                                    telemovel: sideRegisto.value.telemovel,
                                    password: sideRegisto.value.password
                                })
                                .then(function(result) {
                                    alert(result.data.success);
                                    window.location.href = 'cliente.html';
                                })
                                .catch(function(error) {

                                    sideRegisto.value.errors = error.response.data.errors;
                                    if (error.response.data.error)
                                        sideRegisto.value.error = error.response.data.error;

                                });
                            return false;
                        };

                        const recuperarSenha = ref({
                            username: '',
                            errors: {},
                            error: ''
                        });
                        const doRecuperarSenha = (e) => {
                            axios.post('/cliente/enviar_token_recuperacao', recuperarSenha.value)
                                .then(function(result) {
                                    recuperarSenha.value.success = result.data.success;
                                })
                                .catch(function(error) {
                                    sidelogin.value.errors = error.response.data.errors;
                                    if (error.response.data.error)
                                        sidelogin.value.error = error.response.data.error;

                                });
                            return false;
                        }

                        const newsletter = ref({
                            email: '',
                            error: '',
                            success: false
                        });
                        const addNewsletter = (e) => {
                            if (!newsletter.value.email) {
                                newsletter.value.error = ["Insira um email válido"];
                                return false;
                            }

                            axios.post('/cliente/newsletter', {
                                    email: newsletter.value.email
                                })
                                .then(function(result) {
                                    newsletter.value.success = result.data.success;
                                })
                                .catch(function(error) {
                                    newsletter.value.errors = error.response.data.errors;
                                    if (error.response.data.errorr)
                                        newsletter.value.error = error.response.data.error;

                                });
                            return false;
                        };

                        const openApoioCliente = () => {
                            window.open('/apoioaocliente', 'newwindow', 'width=632,height=750');
                        }

                        const minusQtd = (item) => {
                            if (item.qtd > 1) {
                                item.qtd--;
                            } else {
                                if (!confirm("Tem a certeza que quer remover este artigo?"))
                                    return;
                                item.qtd--;
                            }
                            axios.post('/carrinho/update', {
                                qtd: item.qtd,
                                id: item.linhaid,
                            }).then(function(result) {
                                Cart.value = result.data.cart;
                            }).catch(function(error) {
                                console.log(error);
                            });
                        };
                        const plusQtd = (item) => {
                            item.qtd++;
                            axios.post('/carrinho/update', {
                                qtd: item.qtd,
                                id: item.linhaid,
                            }).then(function(result) {
                                Cart.value = result.data.cart;
                            }).catch(function(error) {
                                console.log(error);
                            });
                        };



                        return {

                            doSideLogin,
                            sidelogin,
                            sideRegisto,
                            doSideRegisto,
                            recuperarSenha,
                            doRecuperarSenha,

                            Config,
                            Settings,
                            Cliente,
                            offside,

                            /* Search Logic */
                            Cart,
                            searchField,
                            searchResults,
                            searchBox,
                            setSearch,
                            dooSearch,
                            searchGo,
                            caixaProcura,
                            campoProcura,
                            openSearchBox,

                            /* Cart Logic */
                            cartRemove,
                            minusQtd,
                            plusQtd,

                            /* newsletter */
                            newsletter,
                            addNewsletter,
                            openApoioCliente,

                            ...cookiesLogic,
                            ...extraLogic
                        };
                    }
                }
                createApp(Global).mount("#_Global");

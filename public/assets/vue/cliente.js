window.pageLogic = function(ob) {

    const Cliente = ref(ob.Cliente || {});
    const Invoices = ref(window.Invoices || []);
   
    const moradas = ref(Cliente.value?.moradas || []);
    const loading = ref(false);
    const exibirNovoForm = ref(false);
    const novaMorada = ref({ 
        id: '', 
        titulo: '', 
        nome: '', 
        morada: '', 
        cpostal: '', 
        cidade: '', 
        telefone: '',
    });
    // Carregar Dados
    const fetchMoradas = async () => {
        return ;
        try {
            const response = await axios.get('/cliente/moradas_api');
            moradas.value = response.data;

        } catch (error) {
            alert("Erro ao carregar moradas");
        }
    };
    fetchMoradas();

    // Inserir
    const setMoradaDefault = async (id, tipo) => {
        loading.value = true;
        try {
            const response = await axios.post('/cliente/defaultmorada', { id, tipo });
            if(tipo == 'faturacao')
                Cliente.value.morada_faturacao_hash = id;
            if(tipo == 'entrega')
                Cliente.value.morada_entrega_hash = id;

        } catch (error) {
            alert("Erro ao salvar");
        } finally {
            loading.value = false;
        }
    };
    // Inserir
    const salvarNovaMorada = async () => {
        loading.value = true;
        try {
            const response = await axios.post('/cliente/moradas_api', novaMorada.value);
            console.log(response.data); 
            console.log(moradas.value);
            moradas.value.push(response.data); // Adiciona à lista local
            exibirNovoForm.value = false;
            novaMorada.value = { titulo: '', nome: '', morada: '', cpostal: '', cidade: '', telefone: '' };
        } catch (error) {
            console.log(error);
            alert("Erro ao salvar");
        } finally {
            loading.value = false;
        }
    };

    // Editar
    const atualizarMorada = async (morada) => {
        loading.value = true;
        try {
            await axios.put(`/cliente/moradas_api/${morada.uid}`, morada);
            alert("Morada atualizada!");
        } catch (error) {
            alert("Erro ao atualizar");
        } finally {
            loading.value = false;
        }
    };

    // Eliminar
    const eliminarMorada = async (id) => {
        if (!confirm("Tem a certeza?")) return;
        try {
            await axios.delete(`/cliente/moradas_api/${id}`);
            moradas.value = moradas.value.filter(m => m.uid !== id);
        } catch (error) {
            alert("Erro ao eliminar");
        }
    };
    

    // Historico
    const invoice_ativo = ref({});
    if(Invoices.value.length > 0)
        invoice_ativo.value = Invoices.value[0];

    const setInvoiceAtivo = (invoice) => {
        invoice_ativo.value = invoice;
        console.log(invoice);
    };

    // Formulario de dados pessoais
    const dadosPessoais = ref({
        nome : Cliente.value?.nome, 
        email : Cliente.value?.email, 
        telemovel : Cliente.value?.telemovel, 
        nif : Cliente.value?.nif, 
        errors : [], error : ''});
    const saveDadosPessoais = async () => {
        loading.value = true;
        axios.post('/cliente/dadospessoais', dadosPessoais.value)
        .then(function (result) {
            dadosPessoais.value.errors = [];
            dadosPessoais.value.result = 'Registo atualizado!';


        })
        .catch(function (error) {
            dadosPessoais.value.errors = error.response.data.errors;
            if(error.response.data.error) 
                dadosPessoais.value.error = error.response.data.error;
            
        });
    }

    // Formulario de dados acesso
    const dadosAcesso = ref({
        username : Cliente.value?.username, 
        atual : "", 
        nova : "", 
        confirmar : "", 
        errors : [], error : ''});
    const saveDadosAcesso = async () => {
        loading.value = true;
        axios.post('/cliente/dadosacesso', dadosAcesso.value)
        .then(function (result) {
            dadosAcesso.value.errors = [];
            dadosAcesso.value.result = 'Registo atualizado!';
        })
        .catch(function (error) {
            dadosAcesso.value.errors = error.response.data.errors;
            if(error.response.data.error) 
                dadosAcesso.value.error = error.response.data.error;
            
        });
    }

    // Formulario de dados acesso

    const setSenha = ref({
        token : typeof tokenResetPassword !== 'undefined' ? tokenResetPassword : '',
        nova : "", 
        confirmar : "", 
        errors : [], error : '', success : ''});
    const doSetNovaSenha = async () => {
        loading.value = true;
        axios.post('/cliente/recuperar', setSenha.value)
        .then(function (result) {
            setSenha.value.errors = [];
            setSenha.value.success = result.data.success;
        })
        .catch(function (error) {
            setSenha.value.errors = error.response.data.errors;
            if(error.response.data.error) 
                setSenha.value.error = error.response.data.error;
            
        });
    }

    const clientLogin = ref({ username: '', password: '', errors: {}, error: '' });
    const doClientLogin = (e) => {
        clientLogin.value.errors = {};
        if(!clientLogin.value.username) {
            clientLogin.value.errors.username = "Insira o telemóvel";
        }        
        if(!clientLogin.value.password) {
            clientLogin.value.errors.password = "Insira a senha";
        }
        if(clientLogin.value.errors.username || clientLogin.value.errors.password)
            return false;   
        
        
        axios.post('/cliente/login', {
            username: clientLogin.value.username,
            password: clientLogin.value.password
        })
        .then(function (result) {
            if(result.data.success)
                window.location.href = '/cliente';
        })
        .catch(function (error) {
            clientLogin.value.errors = error.response.data.errors;
            if(error.response.data.error) 
                clientLogin.value.error = error.response.data.error;
            
        });
        return false; 
    }

    const clientRegisto = ref({ nome: '', email: '', telemovel: '', password: '', errors: {}, error: '' });
    const doClientRegisto = (e) => {
        const regras = {
            nome: Validator.minWords(2, "Insira nome e apelido"),
            email: Validator.email("Insira um email válido"),
            telemovel: Validator.telemovel("Insira um telefone válido"),
            password: Validator.required("Insira uma password")
        };

        const result = validateForm(clientRegisto.value, regras);
        console.log(result);


        if (!result.isValid) {
            clientRegisto.value.errors = result.errors;
            return false;
        }

        axios.post('/cliente/registo', {
            nome: clientRegisto.value.nome,
            email: clientRegisto.value.email,
            telemovel: clientRegisto.value.telemovel,
            password: clientRegisto.value.password
        })
        .then(function (result) {
            alert(result.data.success);
            window.location.href = '/cliente';
        })
        .catch(function (error) {
            //console.log(error)
            clientRegisto.value.errors = error.response.data.errors;
            if(error.response.data.error) 
                clientRegisto.value.error = error.response.data.error;
            
        });
        return false;
    };

    

    
    

    return { 
        fetchMoradas, 
        salvarNovaMorada, 
        atualizarMorada, 
        eliminarMorada,
        setMoradaDefault,
        setInvoiceAtivo,

        saveDadosPessoais,
        dadosPessoais,
        saveDadosAcesso,
        dadosAcesso,
        doSetNovaSenha,
        setSenha,

        clientRegisto,
        doClientRegisto,
        clientLogin,
        doClientLogin,

        invoice_ativo,
        Cliente, 
        Invoices,
        moradas, 
        loading, 
        exibirNovoForm, 
        novaMorada 
    };
};
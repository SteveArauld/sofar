window.cookiesLogic = function(){
    const setCookie = (name, value, days) => {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${JSON.stringify(value)};expires=${date.toUTCString()};path=/`;
    };

    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) 
            return JSON.parse(parts.pop().split(';').shift());
        return null;
    };


    let consentCookies = getCookie("consentCookies");
   
    const consent = ref({
        visivel: false, 
        personalizar: false,
        cookies: consentCookies || {
            marketing: false,
            preferences: false,
            estatisticas: false,
            inclassificacos: false
        }
    })

    //alert()
    onMounted(() => {
        const savedConsent = getCookie("consentCookies");
        if (!savedConsent) {
            setTimeout(() => {
                consent.value.visivel = true;
            }, 2000);
        }
    });

    const cookie_rejeitarTodos = () => {
        Object.keys(consent.value.cookies).forEach(key => consent.value.cookies[key] = false);
        saveAndClose(180);
    };

    const cookie_aceitarTodos = () => {
        Object.keys(consent.value.cookies).forEach(key => consent.value.cookies[key] = true);
        saveAndClose(180);
    };

    const cookie_aceitarSelecionados = () => {
        saveAndClose(180);
    };
    const saveAndClose = (days) => {
        setCookie("consentCookies", consent.value.cookies, days);
        consent.value.visivel = false;
    };

    return {
        consent,
        cookie_rejeitarTodos,
        cookie_aceitarTodos,
        cookie_aceitarSelecionados
    }
}
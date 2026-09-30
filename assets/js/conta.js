"use strict";
// Conta do lojista na home: consulta a sessão, faz login ou cria conta pela API PHP (api/).
// O painel sempre abre em uma nova aba; a home continua aberta.
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
  const config = window.WEDTECH_CONFIG || {};
  const API = config.API_URL || "api/";
  const PAINEL = config.APP_URL || "painel.php";

  // Mesmo tema (escuro/claro) e cor de destaque escolhidos no painel
  const aplicarTema = () => {
    try {
      const cfg = JSON.parse(localStorage.getItem("estoque-inteligente-v2") || "null")?.cfg || {};
      document.body.setAttribute("data-theme", cfg.tema === "light" ? "light" : "dark");
      if (cfg.cor) document.documentElement.style.setProperty("--acc", cfg.cor);
    } catch {
      document.body.setAttribute("data-theme", "dark");
    }
  };
  aplicarTema();
  // Mudou o tema no painel (outra aba)? A home acompanha.
  window.addEventListener("storage", (e) => e.key === "estoque-inteligente-v2" && aplicarTema());

  // Sem vitrine de exemplo configurada, esconde o link
  if (!config.LOJA_URL) $$("[data-loja-link]").forEach((a) => (a.style.display = "none"));

  const api = async (rota, opcoes = {}) => {
    const r = await fetch(API + rota, { credentials: "same-origin", ...opcoes });
    const dados = await r.json().catch(() => ({}));
    return { status: r.status, ...dados };
  };
  const enviar = (rota, dados) =>
    api(rota, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(dados) });

  let logado = false;
  const marcarLogado = (usuario) => {
    logado = true;
    const nome = (usuario?.nome || "").trim().split(/\s+/)[0];
    $$("[data-login]").forEach((a) => {
      a.href = PAINEL;
      a.textContent = nome ? `Painel de ${nome}` : "Acessar painel";
    });
  };

  api("sessao.php")
    .then((s) => (s.logado ? marcarLogado(s.usuario) : $$("[data-login]").forEach((a) => (a.textContent = "Entrar"))))
    .catch(() => {});

  const modal = $("#login-modal");
  const loginForm = $("#login-form");
  const cadastroForm = $("#cadastro-form");
  const sucesso = $("#login-ok");
  const tabsBox = $(".tabs", modal || document);
  if (!modal || !loginForm || !cadastroForm) return;
  const tabs = $$('[role="tab"]', modal);

  // ---- Abas: Entrar | Criar conta ----
  const selecionarAba = (id) => {
    tabs.forEach((t) => {
      const on = t.id === id;
      t.setAttribute("aria-selected", String(on));
      t.tabIndex = on ? 0 : -1;
      $("#" + t.getAttribute("aria-controls")).hidden = !on;
    });
    sucesso.hidden = true;
    tabsBox.hidden = false;
    $$(".form-error", modal).forEach((e) => (e.textContent = ""));
    const form = id === "tab-criar" ? cadastroForm : loginForm;
    form.querySelector("input")?.focus();
  };
  tabs.forEach((t, i) => {
    t.addEventListener("click", () => selecionarAba(t.id));
    t.addEventListener("keydown", (e) => {
      if (e.key !== "ArrowRight" && e.key !== "ArrowLeft") return;
      const prox = tabs[(i + (e.key === "ArrowRight" ? 1 : -1) + tabs.length) % tabs.length];
      prox.focus();
      selecionarAba(prox.id);
    });
  });
  $$("[data-ir-aba]", modal).forEach((b) => b.addEventListener("click", () => selecionarAba(b.dataset.irAba)));

  const abrir = (aba = "tab-entrar") => {
    $$("dialog[open]").forEach((d) => d !== modal && d.close());
    modal.showModal ? modal.open || modal.showModal() : modal.setAttribute("open", "");
    selecionarAba(aba);
  };
  const fechar = () => (modal.close ? modal.close() : modal.removeAttribute("open"));

  // "Entrar" no topo: sem sessão abre o acesso aqui mesmo; com sessão o link abre o painel em nova aba
  $$("[data-login]").forEach((a) =>
    a.addEventListener("click", (e) => {
      if (logado) return; // segue o link: target="_blank"
      e.preventDefault();
      abrir("tab-entrar");
    }),
  );
  // Links diretos: index.html#entrar e index.html#criar-conta (usados pelo login.php)
  const porHash = () => {
    if (location.hash === "#criar-conta") abrir("tab-criar");
    else if (location.hash === "#entrar") abrir("tab-entrar");
  };
  window.addEventListener("hashchange", porHash);
  porHash();

  // ---- Preencher exemplo ----
  const exemplos = {
    login: { email: "admin@wedtech.com", senha: "admin123" },
    cadastro: () => {
      const nomes = ["Ana Souza", "Carlos Lima", "Juliana Alves", "Rafael Santos", "Cida Andrade"];
      const nome = nomes[Math.floor(Math.random() * nomes.length)];
      const usuario = nome.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "").replace(/\s+/g, ".");
      const n = String(Date.now()).slice(-4); // e-mail único a cada exemplo
      return { nome, email: `${usuario}${n}@minhaloja.com.br`, senha: "loja1234", senha2: "loja1234" };
    },
  };
  $$("[data-exemplo]", modal).forEach((b) =>
    b.addEventListener("click", () => {
      const tipo = b.dataset.exemplo;
      const form = tipo === "login" ? loginForm : cadastroForm;
      const dados = typeof exemplos[tipo] === "function" ? exemplos[tipo]() : exemplos[tipo];
      Object.entries(dados).forEach(([k, v]) => form.elements[k] && (form.elements[k].value = v));
      $(".form-error", form).textContent = "";
      $("button[type=submit]", form).focus();
    }),
  );

  // ---- Depois de entrar/criar conta: painel em nova aba ----
  const abrirPainel = (r, titulo) => {
    marcarLogado(r.usuario);
    loginForm.reset();
    cadastroForm.reset();
    const aba = window.open(r.redirect || PAINEL, "_blank");
    if (aba) {
      aba.opener = null;
      fechar();
      return;
    }
    // Navegador bloqueou a nova aba: mostra o botão para abrir com um clique
    loginForm.hidden = cadastroForm.hidden = tabsBox.hidden = true;
    $("#login-ok-title").textContent = titulo;
    sucesso.hidden = false;
    $("[data-open-painel]", modal)?.focus();
  };

  const aoEnviar = (form, validar, rota, rotuloBotao, titulo) =>
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const erro = $(".form-error", form);
      const dados = Object.fromEntries(new FormData(form));
      const problema = validar(dados);
      if (problema) {
        erro.textContent = problema;
        return;
      }
      const botao = $("button[type=submit]", form);
      botao.disabled = true;
      botao.textContent = "Aguarde...";
      erro.textContent = "";
      try {
        const r = await enviar(rota, dados);
        if (r.ok) abrirPainel(r, titulo);
        else erro.textContent = r.erro || "Não foi possível continuar. Tente de novo.";
      } catch {
        erro.textContent = "Sem conexão com o servidor. Verifique se ele está rodando.";
      }
      botao.disabled = false;
      botao.textContent = rotuloBotao;
    });

  aoEnviar(
    loginForm,
    (d) => (!d.email.trim() || !d.senha ? "Preencha o e-mail e a senha." : ""),
    "login.php",
    "Entrar",
    "Login feito!",
  );
  aoEnviar(
    cadastroForm,
    (d) => {
      if (d.nome.trim().length < 2) return "Informe seu nome.";
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email.trim())) return "Informe um e-mail válido.";
      if (d.senha.length < 6) return "A senha precisa ter pelo menos 6 caracteres.";
      if (d.senha !== d.senha2) return "As senhas não conferem.";
      return "";
    },
    "cadastro.php",
    "Criar conta e entrar",
    "Conta criada!",
  );

  $("[data-open-painel]", modal)?.addEventListener("click", () => setTimeout(fechar, 0));
})();

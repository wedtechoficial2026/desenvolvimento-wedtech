<?php
// modulos/index.php - Módulos omnichannel (balcão, canais, compras, ADS...) dentro do dashboard.
// Mesmo login do painel.php; abre dentro dele num iframe (modo "embed").
require_once __DIR__ . '/../auth.php';
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}
$currentUser = get_logged_user();
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="theme-color" content="#080a0d" />
    <title>WedTech — Painel do lojista</title>
    <link rel="icon" href="assets/img/favicon-wedtech.png" type="image/png" />
    <script>
      // Antes de pintar: modo embutido e o mesmo tema/cor de destaque do dashboard
      (function () {
        var html = document.documentElement;
        if (window.top !== window) html.classList.add("embed");
        window.aplicarTemaPainel = function () {
          var cfg = {};
          try {
            cfg = (JSON.parse(localStorage.getItem("estoque-inteligente-v2") || "null") || {}).cfg || {};
          } catch (e) {}
          html.setAttribute("data-theme", cfg.tema === "light" ? "light" : "dark");
          if (cfg.cor) html.style.setProperty("--acc", cfg.cor);
        };
        window.aplicarTemaPainel();
        window.addEventListener("storage", function (e) {
          if (e.key === "estoque-inteligente-v2") window.aplicarTemaPainel();
        });
      })();
    </script>
    <link rel="stylesheet" href="assets/css/app.css" />
    <link rel="stylesheet" href="assets/css/brand.css" />
    <link rel="stylesheet" href="assets/css/app-components.css" />
    <link rel="stylesheet" href="assets/css/painel-skin.css" />
  </head>
  <body>
    <div id="app"></div>
    <div id="toast" role="status" aria-live="polite"></div>

    <script>
      // Usuário autenticado pela sessão PHP
      window.CURRENT_USER = <?= json_encode($currentUser, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    </script>
    <script src="assets/config.js"></script>

    <!-- Bibliotecas locais (sem internet) -->
    <script src="assets/js/vendor/qrcode.js"></script>
    <script src="assets/js/shared/barcode.js"></script>

    <!-- Núcleo: regras de negócio, automações e IA (o mesmo usado pelo site da loja) -->
    <script src="assets/js/core/catalogos.js"></script>
    <script src="assets/js/core/calendario.js"></script>
    <script src="assets/js/core/wedtech-core.js"></script>

    <!-- Painel: base, estrutura, telas, ações e eventos -->
    <script src="assets/js/app/base.js"></script>
    <script src="assets/js/app/layout.js"></script>
    <script src="assets/js/app/paginas/inicio.js"></script>
    <script src="assets/js/app/paginas/balcao.js"></script>
    <script src="assets/js/app/paginas/pedidos.js"></script>
    <script src="assets/js/app/paginas/estoque.js"></script>
    <script src="assets/js/app/paginas/produtos.js"></script>
    <script src="assets/js/app/paginas/canais.js"></script>
    <script src="assets/js/app/paginas/compras.js"></script>
    <script src="assets/js/app/paginas/sazonalidade.js"></script>
    <script src="assets/js/app/paginas/ads.js"></script>
    <script src="assets/js/app/paginas/financeiro.js"></script>
    <script src="assets/js/app/paginas/automacoes.js"></script>
    <script src="assets/js/app/paginas/ia.js"></script>
    <script src="assets/js/app/paginas/config.js"></script>
    <script src="assets/js/app/acoes.js"></script>

    <script>
      // Ponte com o dashboard: login já feito no PHP, nome do usuário e "Sair" da sessão PHP
      ui.loggedIn = true;
      // Nome do usuário logado em toda tela (inclusive depois de escolher o tipo de loja)
      var nomeLogado = window.CURRENT_USER && window.CURRENT_USER.nome ? String(window.CURRENT_USER.nome).trim().split(/\s+/)[0] : "";
      var renderOriginal = render;
      render = function () {
        if (nomeLogado && state.store) state.store.owner = nomeLogado;
        return renderOriginal();
      };
      logout = function () {
        try {
          sessionStorage.removeItem("wedtech-session");
        } catch {}
        window.top.location.href = "../logout.php";
      };
      // Avisa o dashboard da tela atual (para marcar o item certo no menu)
      if (window.top !== window) {
        var avisarRota = function () {
          window.parent.postMessage({ wedtechModulo: decodeURIComponent(location.hash.slice(1)) || "inicio" }, location.origin);
        };
        window.addEventListener("hashchange", avisarRota);
        window.addEventListener("load", avisarRota);
      }
    </script>

    <script src="assets/js/app/main.js"></script>
  </body>
</html>

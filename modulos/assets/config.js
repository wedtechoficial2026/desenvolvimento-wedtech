// WedTech — endereços usados pelo painel e pela vitrine.
// Para apontar o "Voltar ao site" para outro lugar, mude só HOME_URL.
// Aberto direto do disco (file://), usa a pasta site/ ao lado.
window.WEDTECH_CONFIG = {
  HOME_URL: location.protocol === "file:" ? "../site/index.html" : "/",
};

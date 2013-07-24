-------------------------- TABELAS DE DESCRICAO DAS EMPRESAS --------------------------

CREATE TABLE IF NOT EXISTS empresa (
	-- Atributos Gerais
	id_empresa            AUTO_INCREMENT,
	caminho_logo          CHAR(1000),
	nome_empresa          CHAR(255),
	nome_pregao           CHAR(255),
	cod_negociacao        CHAR(255),
	cod_isin              CHAR(255),
	cod_cvm               CHAR(255),
	cnpj                  CHAR(255),
	atividade_principal   CHAR(1000),
	
	-- Atributos de Classificacao
	classificao_setorial  CHAR(255),
	setor                 CHAR(255),
	subsetor              CHAR(255),
	segmento              CHAR(255),

	-- Atributos de Contato
	site                  CHAR(500),
	rua                   CHAR(255),
	cep                   CHAR(255),
	cidade                CHAR(255),
	estado                CHAR(10),
	telefone              CHAR(500),
	fax                   CHAR(50),
	nomes                 CHAR(1000),
	emails                CHAR(1000),

	PRIMARY KEY (id_empresa)
);


---------------------------- TABELAS DO HISTORICO DE COTACOES -----------------------------
-- Mais informacoes ver o Dicionario de Dados (SeriesHistoricas_Layout.pdf)
CREATE TABLE IF NOT EXISTS acoes (
	acao_id	              AUTO_INCREMENT,
	data_pregao           DATE,
	cod_bdi               CHAR(02),
	cod_negociacao        CHAR(12),
	tipo_mercado          CHAR(3),
	nome_resumido         CHAR(12),
	especificacao_papel   CHAR(10),
	prazo_termo           CHAR(3),
	moeda_referencia      CHAR(4),
	preco_abertura        CHAR(11),
	preco_maximo          CHAR(11),
	preco_minimo          CHAR(11),
	preco_medio           CHAR(11),
	preco_ultimo          CHAR(11),
	preco_melhor_compra   CHAR(11),
	preco_melhor_venda    CHAR(11),
	total_negocios        INTEGER,
	qtd_titulos           INTEGER,
	volume_titulos        CHAR(18),
	preco_exercicio       CHAR(11),
	ind_mercado_opcoes    CHAR(1),
	data_vencimento       DATE,
	fator_cotacao         CHAR(7),
	pontos_exercicio      CHAR(7),
	cod_isin              CHAR(12),
	num_distribuicao      CHAR(3),
	PRIMARY KEY (acao_id)
);

-- TODO
-- Definir o tipo da tabela das cotacoes

-------------------------- TABELAS DE DESCRICAO DAS EMPRESAS --------------------------

-- TABELA EMPRESA
CREATE TABLE IF NOT EXISTS empresa (
	id_empresa            INTEGER,
	caminho_logo          CHAR(1000),
	nome_empresa          CHAR(255),
	nome_pregao           CHAR(255),
	cod_negociacao        CHAR(255),
	cod_isin              CHAR(255),
	cod_cvm               CHAR(255),
	cnpj                  CHAR(255),
	atividade_principal   CHAR(1000),
	classificao_setorial  CHAR(255),
	PRIMARY KEY (id_empresa)
);

-- TABELA CLASSIFICACAO
CREATE TABLE IF NOT EXISTS classificacao (
	id_classificacao    INTEGER,
	setor              CHAR(255),
	subsetor           CHAR(255),
	segmento           CHAR(255),
	PRIMARY KEY (id_classificacao)
);

-- TABELA CONTATO
CREATE TABLE IF NOT EXISTS contato (
	id_contato     INTEGER,
	site           CHAR(500),
	rua            CHAR(255),
	cep            CHAR(255),
	cidade         CHAR(255),
	estado         CHAR(10),
	telefone       CHAR(500),
	fax            CHAR(50),
	nomes          CHAR(1000),
	emails         CHAR(1000),
	PRIMARY KEY (id_contato)
);

CREATE TABLE IF NOT EXISTS empresa_contato (
	id_emp_cont     AUTO_INCREMENT,
	id_empresa      INTEGER NOT NULL,
	id_contato      INTEGER NOT NULL,
	PRIMARY KEY     (id_emp_cont),
	FOREIGN KEY     (id_empresa) REFERENCES empresa (id_empresa),
	FOREIGN KEY     (id_contato) REFERENCES contato (id_contato)
);

CREATE TABLE IF NOT EXISTS empresa_classificacao (
	id_emp_classe       AUTO_INCREMENT,
	id_empresa          INTEGER NOT NULL,
	classification_id   INTEGER NOT NULL,
	PRIMARY KEY         (id_emp_classe),
	FOREIGN KEY         (id_empresa) REFERENCES empresa (id_empresa),
	FOREIGN KEY         (id_classificacao) REFERENCES classificacao (id_classificacao)
);


---------------------------- TABELAS DO HISTORICO COTACOES -----------------------------
-- Mais informacoes ver o Dicionario de Dados (SeriesHistoricas_Layout.pdf)
CREATE TABLE IF NOT EXISTS historico_cotacoes (
	cotacao_id            AUTO_INCREMENT,
	data_pregao           CHAR(8),
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
	total_negocios        CHAR(5),
	qtd_titulos           CHAR(18),
	volume_titulos        CHAR(18),
	preco_exercicio       CHAR(11),
	ind_mercado_opcoes    CHAR(1),
	data_vencimento       CHAR(8),
	fator_cotacao         CHAR(7),
	pontos_exercicio      CHAR(7),
	cod_isin              CHAR(12),
	num_distribuicao      CHAR(3),
	PRIMARY KEY (cotacao_id)
);



-- TODO: Novo modelo de dados:
-- empresa (PK as cnpj)
-- empresa_isin (PK auto-increment)
-- cotacoes (PK auto_increment)
-- TODO: Add the FKs

------------------ SCRIPT DE CRIACAO DE TABELAS ------------------ 

-- =========== TABELA DE DESCRICAO DAS EMPRESAS =========== 

-- TODO: Atualizar depois do novo modelo de dados
CREATE TABLE IF NOT EXISTS empresa (
	-- Atributos Gerais
	id_empresa            AUTO_INCREMENT,
	caminho_logo          CHAR(1000),
	nome_empresa          CHAR(255),
	nome_pregao           CHAR(100),
	cod_negociacao        CHAR(255),
	cod_isin              CHAR(1000),
	cod_cvm               CHAR(255),
	cnpj                  CHAR(255),
	atividade_principal   CHAR(1000),
	
	-- Atributos de Classificacao
	classificao_setorial  CHAR(255),
	setor                 CHAR(255),
	sub_setor             CHAR(255),
	segmento              CHAR(255),

	-- Atributos de Contato
	site                  CHAR(500),
	rua                   CHAR(255),
	cep                   CHAR(50),
	cidade                CHAR(255),
	estado                CHAR(20),
	telefone              CHAR(500),
	fax                   CHAR(50),
	nomes                 CHAR(1000),
	emails                CHAR(1000),

	PRIMARY KEY           (id_empresa)
);

-- =========== TABELAS DO HISTORICO DE COTACOES =========== 
/* 
-- Significado dos dados:
-- 		Mais informacoes ver o Dicionario de Dados (SeriesHistoricas_Layout.pdf)
-- Diferenca entre tipos NUMERIC e MONEY:
-- 		Precos estao definidos como o tipo MONEY
-- 		Outros numeros em ponto flutuante estao como NUMERIC
-- 		No Vertica todo ponto flutuante eh um NUMERIC, a diferenca estah 
-- 		apenas na definicao default da precisao e casas decimais.
-- 		Mais informacoes: https://my.vertica.com/docs/6.1.x/HTML/index.htm#12295.htm
-- Chave estrangeira:
-- 		cotacao.nome_resumido REFERENCIA empresa.nome_pregao 
*/
-- TODO: Atualizar depois do novo modelo de dados
CREATE TABLE IF NOT EXISTS cotacao (
	id_cotacao            AUTO_INCREMENT,
	data_pregao           DATE,
	cod_bdi               CHAR(2),
	cod_negociacao        CHAR(12),
	tipo_mercado          INTEGER, 
	nome_resumido         CHAR(12) NOT NULL,
	especificacao_papel   CHAR(10),
	prazo_termo           CHAR(3),
	moeda_referencia      CHAR(4),
	preco_abertura        MONEY(11, 2),
	preco_maximo          MONEY(11, 2), 
	preco_minimo          MONEY(11, 2), 
	preco_medio           MONEY(11, 2), 
	preco_ultimo          MONEY(11, 2), 
	preco_melhor_compra   MONEY(11, 2), 
	preco_melhor_venda    MONEY(11, 2), 
	total_negocios        INTEGER,
	qtd_titulos           INTEGER,
	volume_titulos        NUMERIC(16, 2), 
	preco_exercicio       MONEY(11, 2), 
	ind_mercado_opcoes    INTEGER, 
	data_vencimento       DATE,
	fator_cotacao         INTEGER, 
	pontos_exercicio      NUMERIC(7, 6),
	cod_isin              CHAR(12),
	num_distribuicao      CHAR(3),
	PRIMARY KEY           (id_cotacao)
--	FOREIGN KEY           (cod_isin) REFERENCES empresa (cod_isin)
);


/*
	Para resovermos temporariamente o problema da existencia de cotacoes sem
	a sua correspondente empresa na tabela 'empresa', criamos uma tabela 
	com os 'nome_pregao' das empresas inexistentes. 
*/
 -- TODO: Atualizar depois do novo modelo de dados
CREATE TABLE IF NOT EXISTS empresas_inexistentes (
	id                    AUTO_INCREMENT,
	-- 'nome_pregao' aceita um CHAR um pouco maior que o tamanho fixado 
	-- para aceitar eventuais caracteres adicionais
	nome_pregao           CHAR(20), 
	PRIMARY KEY           (id)
);

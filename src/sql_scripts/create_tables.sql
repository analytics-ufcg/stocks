-- ENTERPRISE TABLE
-- Table columns in portuguese (same order as below):
-- 	<id_empresa>, caminho do ícone(logo), nome da empresa, nome de pregão, 
-- 	códigos de negociação, código isin, código cvm, cnpj, 
-- 	atividade principal, classificação setorial
CREATE TABLE IF NOT EXISTS enterprise (
	enterprise_id            INTEGER,
	logo_server_path         CHAR(1000),
	enterprise_name          CHAR(255),
	stock_name               CHAR(255),
	trade_codes              CHAR(255),
	isin_code                CHAR(255),
	cvm_code                 CHAR(255),
	cnpj                     CHAR(255),
	main_activity            CHAR(1000),
	sectoral_classification  CHAR(255),
	PRIMARY KEY (enterprise_id)
);

-- CLASSIFICATION TABLE
-- Table columns in portuguese (same order as below):
-- 	<id_classificacao>, setor, subsetor, segmento
CREATE TABLE IF NOT EXISTS classification (
	classification_id   INTEGER,
	sector              CHAR(255),
	subsector           CHAR(255),
	segment             CHAR(255),
	PRIMARY KEY (classification_id)
);

-- CONTACT TABLE
-- Table columns in portuguese (same order as below):
-- 	<id_contato>, site, endereços dividido em (Rua, CEP, Cidade, UF), 
-- 	telefones, fax, nomes, emails
CREATE TABLE IF NOT EXISTS contact (
	contact_id     INTEGER,
	site           CHAR(500),
	street         CHAR(255),
	zip_code       CHAR(255),
	city           CHAR(255),
	state          CHAR(10),
	fax            CHAR(50),
	PRIMARY KEY (contact_id)
);

-- TRADE_CODES TABLE
CREATE TABLE IF NOT EXISTS trade_code (
	trade_code_id   INTEGER,
	trade_code      CHAR(255),
	PRIMARY KEY (trade_code_id)
);

-- NAMES TABLE
CREATE TABLE IF NOT EXISTS name (
	name_id      INTEGER,
	name         CHAR(255),
	contact_type CHAR(255),
	PRIMARY KEY (name_id)
);

-- EMAILS TABLE
CREATE TABLE IF NOT EXISTS email (
	email_id     INTEGER,
	email        CHAR(255),
	contact_type CHAR(255),
	PRIMARY KEY (email_id)
);

-- PHONE_NUMBERS TABLE
CREATE TABLE IF NOT EXISTS phone (
	phone_id     INTEGER,
	phone        CHAR(255),
	contact_type CHAR(255),
	PRIMARY KEY (phone_id)
);

CREATE TABLE IF NOT EXISTS enterprise_contact (
	ent_cont_id     AUTO_INCREMENT,
	enterprise_id   INTEGER NOT NULL,
	contact_id      INTEGER NOT NULL,
	PRIMARY KEY     (ent_cont_id),
	FOREIGN KEY     (enterprise_id) REFERENCES enterprise (enterprise_id),
	FOREIGN KEY     (contact_id) REFERENCES contact (contact_id)
);

CREATE TABLE IF NOT EXISTS enterprise_classification (
	ent_class_id        AUTO_INCREMENT,
	enterprise_id       INTEGER NOT NULL,
	classification_id   INTEGER NOT NULL,
	PRIMARY KEY         (ent_class_id),
	FOREIGN KEY         (enterprise_id) REFERENCES enterprise (enterprise_id),
	FOREIGN KEY         (classification_id) REFERENCES classification (classification_id)
);

CREATE TABLE IF NOT EXISTS enterprise_trade_code (
	ent_trade_id    AUTO_INCREMENT,
	enterprise_id   INTEGER NOT NULL,
	trade_code_id   INTEGER NOT NULL,
	PRIMARY KEY     (ent_trade_id),
	FOREIGN KEY     (enterprise_id) REFERENCES enterprise (enterprise_id),
	FOREIGN KEY     (trade_code_id) REFERENCES trade_code (trade_code_id)
);

CREATE TABLE IF NOT EXISTS contact_name (
	cont_name_id    AUTO_INCREMENT,
	contact_id      INTEGER NOT NULL,
	name_id         INTEGER NOT NULL,
	PRIMARY KEY     (cont_name_id),
	FOREIGN KEY     (contact_id) REFERENCES contact (contact_id),
	FOREIGN KEY     (name_id) REFERENCES name (name_id)
);

CREATE TABLE IF NOT EXISTS contact_email (
	cont_email_id   AUTO_INCREMENT,
	contact_id      INTEGER NOT NULL,
	email_id        INTEGER NOT NULL,
	PRIMARY KEY     (cont_email_id),
	FOREIGN KEY     (contact_id) REFERENCES contact (contact_id),
	FOREIGN KEY     (email_id) REFERENCES email (email_id)
);

CREATE TABLE IF NOT EXISTS contact_phone (
	cont_phone_id   AUTO_INCREMENT,
	contact_id      INTEGER NOT NULL,
	phone_id        INTEGER NOT NULL,
	PRIMARY KEY     (cont_phone_id),
	FOREIGN KEY     (contact_id) REFERENCES contact (contact_id),
	FOREIGN KEY     (phone_id) REFERENCES phone (phone_id)
);

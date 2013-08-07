------------------ SCRIPT DE "BULK LOAD" DOS DADOS ------------------ 

-- Todos os arquivos de dados estao no servidor na pasta "/home/stocks/git/stocks/data"

-- ================= CARGA da tabela EMPRESA =================
-- CARREGA os dados das empresas a partir de um arquivo CSV local 
COPY empresa
FROM '/home/stocks/git/stocks/data/DadosEmpresas.csv'
DELIMITER ','    -- Delimitador das colunas
ENCLOSED BY '"'  -- Caractere que abre e fecha strings
ESCAPE AS '\'   -- Caractere de escape
NULL AS 'NA';    -- Como o NULL eh definido

select ANALYZE_CONSTRAINTS('empresa');


-- ================= CARGA da tabela COTACAO =================
-- CARREGA os dados das cotacoes a partir dos arquivos CSV locais 

-- OBS.: no futuro podemos refatorar esse SQL para gera-lo e fazer a transacao
-- 		 automaticamente no proprio codigo python (que leh o UTF e escreve o CSV)

COPY cotacao FROM '/home/stocks/git/stocks/data/cotacoes_*.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';

-- A funcao ANALYZE_CONSTRAINST retorna os valores das chaves estrangeiras
-- (sem repeticao) que não tiverem correspondencia.
INSERT INTO empresas_inexistentes (nome_pregao) 
	SELECT load_results.'Column Values' as nome_pregao 
	FROM (select ANALYZE_CONSTRAINTS('cotacao')) as load_results;
COMMIT;


/*
	ATENCAO:
	Eh importante checar os arquivos de log de excecoes ao termino de cada carga. 
	O Vertica rejeita linhas se houver alguma excecao na leitura (mais colunas, menos colunas, etc.)
	Ver arquivos de log abaixo:
		<db_dir>/<catalog_dir>/CopyErrorLogs/<tablename-filename-of-source>-copy-from-exceptions
		<db_dir>/<catalog_dir>/CopyErrorLogs/<tablename-filename-of-source>-copy-from-rejected-data
*/

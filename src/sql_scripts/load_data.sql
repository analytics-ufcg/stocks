------------------ SCRIPT DE "BULK LOAD" DOS DADOS ------------------ 

-- ================= CARGA da tabela EMPRESA =================
-- CARREGA os dados das empresas a partir de um arquivo CSV local 
COPY empresa
FROM '/home/stocks/data/DadosEmpresas.csv'
DELIMITER ','    -- Delimitador das colunas
ENCLOSED BY '"'  -- Caractere que abre e fecha strings
NULL 'NA'        -- Como o NULL eh definido
NO COMMIT;       -- Nao faz o commit (ver explicacao abaixo)

/*
	O Vertica nao realiza checagem de chaves na hora da carga de dados, 
	apenas na consulta. Entao nao fazemos COMMIT para que possamos 
	checar se houve quebra de restricoes com a funcao abaixo que checa
	se houve quebra de qualquer restricao (incluindo as chaves) da 
	tabela empresa
*/

select ANALYZE_CONSTRAINTS('empresa');

-- Termine a transacao apenas se nao existir PKs (nome_pregao) duplicadas
-- COMMIT;

-- ================= CARGA da tabela COTACAO =================
-- CARREGA os dados das cotacoes a partir dos arquivos CSV locais 

-- OBS.: no futuro podemos refatorar esse SQL para gera-lo e fazer a transacao
-- 		 automaticamente no proprio codigo python (que leh o UTF e escreve o CSV)

COPY cotacao FROM local '/home/stocks/data/cotacoes_1986.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1987.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1989.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1990.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1991.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1992.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1993.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1994.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1995.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1996.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1997.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1998.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_1999.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2000.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2001.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2002.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2003.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2004.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2005.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2006.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2007.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2008.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2009.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2010.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2011.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2012.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';
COPY cotacao FROM local '/home/stocks/data/cotacoes_2013.csv' DELIMITER ',' ENCLOSED BY '"' NULL 'NA';

-- A funcao ANALYZE_CONSTRAINST retorna os valores das chaves estrangeiras
-- (sem repeticao) que não tiverem correspondencia.
INSERT INTO empresas_inexistentes (nome_pregao) 
	SELECT load_results.'Column Values' as nome_pregao 
	FROM (select ANALYZE_CONSTRAINTS('cotacao')) as load_results;
COMMIT;

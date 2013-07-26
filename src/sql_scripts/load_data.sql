------------------ SCRIPT DE "BULK LOAD" DOS DADOS ------------------ 

-- ================= CARGA da tabela EMPRESA =================
-- CARREGA os dados das empresas a partir de um arquivo CSV local 
COPY empresa
FROM local '...'
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


-- ================= CARGA da tabela COTACAO =================
-- CARREGA os dados das cotacoes a partir dos arquivos CSV locais 
COPY cotacao
FROM local '...'
DELIMITER ','  ENCLOSED BY '"' NULL 'NA' NO COMMIT;

select ANALYZE_CONSTRAINTS('cotacao');

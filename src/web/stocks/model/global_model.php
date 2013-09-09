<?php

	$dsn = "StocksDSN";

	# Queries
	$query_map = array(

		// USED BY: model_empresa.php 
		"get_empresas_by_col" => 
			'SELECT * 
			FROM empresa as emp LEFT JOIN contato_investidor as cont on emp.cnpj = cont.cnpj 
	        WHERE emp.[EMP_COLUMN] = ?',

		// USED BY: model_empresa_col.php 
        "get_col_empresa" =>
        	'SELECT DISTINCT [EMP_COLUMN] FROM EMPRESA',

		// USED BY: model_top10.php 
	    "top_crescimento" =>
	    	'SELECT [SELECT_NOME_GRUPO_COL] AS nome_grupo, ROUND(MAX(preco_diff), 2) AS preco_diff
			FROM (SELECT [SUB_SELECT_EXTRA_COL]CONCAT(CONCAT(CONCAT (emp.nome_empresa,\' (\'), emp_isin.cod_isin), \')\') AS nome_empresa, 
				     CASE (COUNT(cot.preco_abertura) OVER (PARTITION BY emp.nome_empresa, emp_isin.cod_isin))
				                WHEN 2 THEN 
				                    ((LAST_VALUE(cot.preco_ultimo) OVER (w_part_emp_isin_order_date RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) - 
	                    			FIRST_VALUE(cot.preco_abertura) OVER(w_part_emp_isin_order_date RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING))/
	                    			FIRST_VALUE(cot.preco_abertura) OVER(w_part_emp_isin_order_date RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING)) * 100
				                WHEN (1 AND ? = ?) THEN -- Same initial and final dates
				                    cot.preco_ultimo - cot.preco_abertura
				                ELSE
				                    NULL
				     END AS preco_diff
				FROM empresa AS emp INNER JOIN empresa_isin emp_isin ON emp.cnpj = emp_isin.cnpj 
				                  INNER JOIN (
				                              SELECT slice_time as data_pregao, cod_isin, 
				                                     TS_FIRST_VALUE(preco_abertura IGNORE NULLS, \'const\') as preco_abertura, 
				                                     TS_FIRST_VALUE(preco_ultimo IGNORE NULLS, \'const\') as preco_ultimo
				                              FROM cotacao
				                              WHERE cod_bdi = 02
				                              TIMESERIES slice_time AS \'1 day\' OVER (PARTITION BY cod_isin ORDER BY data_pregao)
				                  ) AS cot ON emp_isin.cod_isin = cot.cod_isin
				WHERE cot.data_pregao = ? OR cot.data_pregao = ?
				WINDOW w_part_emp_isin_order_date AS (PARTITION BY emp.nome_empresa, emp_isin.cod_isin ORDER BY cot.data_pregao)
				) AS sub_query
			WHERE preco_diff is not NULL -- We delete the groups which the difference was not calculated
			GROUP BY [GROUP_BY_COLS]
			ORDER BY preco_diff ASC;',


	    "top_oscilacao" =>
	        'SELECT [SELECT_NOME_GRUPO_COL] AS nome_grupo, SUM(ABS(ISNULL(acao.diff_preco_medio, 0))) AS sum_abs_diff_preco_medio
	        FROM empresa AS emp INNER JOIN empresa_isin AS emp_isin ON emp.cnpj = emp_isin.cnpj
	             INNER JOIN (
	                        SELECT data_pregao, 
	                                cod_isin,
	                                preco_medio - LAG(preco_medio, 1, NULL) OVER (PARTITION BY cod_isin ORDER BY data_pregao) AS diff_preco_medio
	                        FROM cotacao
	                        WHERE cod_bdi = 02 
	                        ORDER BY cod_isin, data_pregao
	             ) AS acao ON emp_isin.cod_isin = acao.cod_isin
	        WHERE (acao.data_pregao BETWEEN ? AND ?)
	        GROUP BY [GROUP_BY_COLS]
	        ORDER BY sum_abs_diff_preco_medio DESC
	        LIMIT ?;',

		"top_liquidez" => 
	        'SELECT [SELECT_NOME_GRUPO_COL] AS nome_grupo, SUM(acao.volume_titulos) AS sum_volume_titulos
			FROM empresa AS emp INNER JOIN empresa_isin AS emp_isin ON emp.cnpj = emp_isin.cnpj
			     INNER JOIN cotacao AS acao ON emp_isin.cod_isin = acao.cod_isin
			WHERE (acao.data_pregao BETWEEN ? AND ?) 
			        AND acao.cod_bdi=02 
			GROUP BY [GROUP_BY_COLS]
			ORDER BY sum_volume_titulos ASC
			LIMIT ?;'
	);

?>
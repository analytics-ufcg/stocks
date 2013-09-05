<?php 
    
    /* ----------------------------------------------------------------------------------------------
        MAIN
    */

    # Reading Arguments...
    $agrupamento = $_GET['top10_grouping'];
    $metrica = $_GET['top10_metric'];
    $top = $_GET['top'];
    $data_inicial = $_GET['start_date_top10'];
    $data_final = $_GET['end_date_top10'];
    
    // $agrupamento = "Ação";
    // $metrica = "Queda";
    // $top = 10;
    // $data_inicial = "03/09/2012";
    // $data_final = "03/09/2012"; 
    
    # Argument casting...
    list ($dia_inicial, $mes_inicial, $ano_inicial) = split("/", $data_inicial);
    $data_inicial = $ano_inicial . "-" . $mes_inicial . "-" . $dia_inicial;

    list ($dia_final, $mes_final, $ano_final) = split("/", $data_final);
    $data_final = $ano_final . "-" . $mes_final . "-" . $dia_final;

    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    switch ($metrica) {
        case "Crescimento":
        case "Queda":
            list($nomes, $valores) = top_cresce_decresce($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Oscilação":
            list($nomes, $valores) = top_oscilacao($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Maior Liquidez":
        case "Menor Liquidez":
            list($nomes, $valores) = top_liquidez($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        default:
            echo "Metrica inexistente.";
            break;
    }

    # Close the connection
    odbc_close($conn);

    // echo print_r(array("nomes" => $nomes, "valores" => $valores));
    echo json_encode(array("nomes" => $nomes, "valores" => $valores));

    /* ----------------------------------------------------------------------------------------------
        FUNCTIONS
    */

    function top_cresce_decresce ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {
        # Prepare the query
        $query = "SELECT emp.nome_empresa as nome_empresa, emp_isin.cod_isin as isin, 
                     cot.data_pregao as data_pregao, 
                     CASE (COUNT(cot.preco_abertura) OVER (PARTITION BY emp.nome_empresa, emp_isin.cod_isin))
                                WHEN 2 THEN 
                                    LAST_VALUE(cot.preco_ultimo) OVER (w_part_emp_isin_order_date RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) - 
                                    FIRST_VALUE(cot.preco_abertura) OVER(w_part_emp_isin_order_date RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING)
                                WHEN (1 AND ? = ?) THEN -- Same initial and final dates
                                    cot.preco_ultimo - cot.preco_abertura
                                ELSE
                                    NULL
                     END AS preco_diff
                FROM empresa AS emp INNER JOIN empresa_isin emp_isin ON emp.cnpj = emp_isin.cnpj 
                                  INNER JOIN (
                                              SELECT slice_time as data_pregao, cod_isin, 
                                                     TS_FIRST_VALUE(preco_abertura IGNORE NULLS, 'const') as preco_abertura, 
                                                     TS_FIRST_VALUE(preco_ultimo IGNORE NULLS, 'const') as preco_ultimo
                                              FROM cotacao
                                              WHERE cod_bdi = 02
                                              TIMESERIES slice_time AS '1 day' OVER (PARTITION BY cod_isin ORDER BY data_pregao)
                                  ) AS cot ON emp_isin.cod_isin = cot.cod_isin
                WHERE cot.data_pregao = ? OR cot.data_pregao = ?
                WINDOW w_part_emp_isin_order_date AS (PARTITION BY emp.nome_empresa, emp_isin.cod_isin ORDER BY cot.data_pregao)
                ORDER BY emp_isin.cod_isin, cot.data_pregao;";

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);
       
        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial, $data_final, $data_inicial, $data_final));
        
        # Fetch all rows
        $map = array();
        $prev_nome = "";
        while ($row = odbc_fetch_array($resultset)) {
               
            $nome = $row['nome_empresa'] . "(" . $row['isin'] . ")";
        	$preco_diff = $row['preco_diff'];
        	if($nome != $prev_nome){
                if ($preco_diff != NULL){
        		    $map[$nome] = $preco_diff;
                }else{
                    // We do nothing by now.
                }
        	}else{
        		// The second name is not used
        	}
        	$prev_nome = $nome;
        }
        asort($map);
        $keys = array_keys($map);

        if($metrica == "Crescimento"){
            $nomes = array_reverse(array_slice($keys, count($keys) - $top, $top));
            $valores = array_reverse(array_slice(array_values($map), count($map) - $top, $top));
        }else{
            $nomes = array_reverse(array_slice($keys, 0, $top));
            $valores = array_reverse(array_slice(array_values($map), 0, $top));
        }
        
        return array($nomes, $valores);
    }

    function top_oscilacao ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {

        # Prepare the query
        $query = "SELECT emp.nome_empresa AS nome_empresa, emp_isin.cod_isin AS isin, SUM(ABS(ISNULL(acao.diff_preco_medio, 0))) AS sum_abs_diff_preco_medio
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
                    GROUP BY emp.nome_empresa, emp_isin.cod_isin
                    ORDER BY sum_abs_diff_preco_medio DESC
                    LIMIT ?;";

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);

        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial, $data_final, $top));

        # Fetch all rows
        $nomes = array();
        $valores = array();
        while ($row = odbc_fetch_array($resultset)) {
            array_push($nomes, $row['nome_empresa'] . "(" . $row['isin'] . ")");
            array_push($valores, $row['sum_abs_diff_preco_medio']);
        }

        return array($nomes, $valores);
    }

    function top_liquidez ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {

        # Prepare the query
        $query = "SELECT emp.nome_empresa AS nome_empresa, emp_isin.cod_isin AS isin, SUM(acao.volume_titulos) AS sum_volume_titulos
                    FROM empresa AS emp INNER JOIN empresa_isin AS emp_isin ON emp.cnpj = emp_isin.cnpj
                         INNER JOIN cotacao AS acao ON emp_isin.cod_isin = acao.cod_isin
                    WHERE (acao.data_pregao BETWEEN ? AND ?) 
                            AND acao.cod_bdi=02 
                    GROUP BY emp.nome_empresa, emp_isin.cod_isin
                    ORDER BY sum_volume_titulos ASC
                    LIMIT ?;";

        if($metrica == "Maior Liquidez"){
            $query = str_replace("sum_volume_titulos ASC", "sum_volume_titulos DESC", $query);
        } 

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);

        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial, $data_final, $top));

        # Fetch all rows
        $nomes = array();
        $valores = array();
        while ($row = odbc_fetch_array($resultset)) {
            array_push($nomes, $row['nome_empresa'] . "(" . $row['isin'] . ")");
            array_push($valores, $row['sum_volume_titulos']);
        }

        return array($nomes, $valores);
    }
?>

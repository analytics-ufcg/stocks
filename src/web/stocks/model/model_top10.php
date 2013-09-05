<?php 
    
    /* ----------------------------------------------------------------------------------------------
        MAIN
    */

    # Argument casting...
    
     //$agrupamento = $_GET['top10_grouping'];
     //$metrica = $_GET['top10_metric'];
    // $top = $_GET['top'];
     //$data_inicial = $_GET['start_date_wrapper'];
     //$data_final = $_GET['end_date_wrapper'];
    
    //$agrupamento = "Ação";
    $metrica = "Crescimento";
    $top = 10;
    $data_inicial = "03/09/2012";
    $data_final = "04/09/2012"; 
	
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
            list($nome_empresas, $valores) = top_cresce_decresce($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Oscilação":
            list($nome_empresas, $valores) = top_oscilacao($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Maior Liquidez":
        case "Menor Liquidez":
            list($nome_empresas, $valores) = top_liquidez($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        default:
            echo "Metrica inexistente.";
            break;
    }

    # Close the connection
    odbc_close($conn);

    // echo print_r(array("nomes" => $nomes, "valores" => $valores));
    echo json_encode(array("nomes" => $nome_empresas, "valores" => $valores));

    /* ----------------------------------------------------------------------------------------------
        FUNCTIONS
    */

    function top_cresce_decresce ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {
         # Prepare the query
        $query = "SELECT emp.nome_empresa as nome_empresa, emp_isin.cod_isin as isin, 
                         cot.data_pregao as data_pregao, cot.preco_abertura as preco_abertura, 
                         cot.preco_ultimo AS preco_ultimo 
                  FROM empresa AS emp INNER JOIN empresa_isin emp_isin ON emp.cnpj = emp_isin.cnpj 
                                      INNER JOIN cotacao AS cot ON emp_isin.cod_isin = cot.cod_isin
                  WHERE (cot.data_pregao = ? or cot.data_pregao = ?) AND cot.cod_bdi = 02
                  ORDER BY emp_isin.cod_isin, cot.data_pregao;"

        // With GAP Filling
        // $query = "SELECT emp.nome_empresa as nome_empresa, emp_isin.cod_isin as isin, 
        //                  cot.data_pregao as data_pregao, cot.preco_abertura as preco_abertura, 
        //                  cot.preco_ultimo AS preco_ultimo 
        //             FROM empresa AS emp INNER JOIN empresa_isin emp_isin ON emp.cnpj = emp_isin.cnpj 
        //                               INNER JOIN (
        //                                           SELECT slice_time as data_pregao, cod_isin, 
        //                                                  TS_FIRST_VALUE(preco_abertura) as preco_abertura, 
        //                                                  TS_FIRST_VALUE(preco_ultimo) as preco_ultimo
        //                                           FROM cotacao
        //                                           WHERE cod_bdi = 02
        //                                           TIMESERIES slice_time AS '1 day' OVER (PARTITION BY cod_isin ORDER BY data_pregao)
        //                               ) AS cot ON emp_isin.cod_isin = cot.cod_isin,
        //             WHERE (cot.data_pregao = '2010-10-1' OR cot.data_pregao = '2010-10-11')
        //             ORDER BY emp_isin.cod_isin, cot.data_pregao;"

        // Improved Query with DIFF
        // SELECT emp.nome_empresa as nome_empresa, emp_isin.cod_isin as isin, 
        //      cot.data_pregao as data_pregao, cot.preco_abertura as preco_abertura, 
        //      cot.preco_ultimo AS preco_ultimo,
        //      COUNT(cot.preco_abertura) OVER (PARTITION BY emp.nome_empresa, emp_isin.cod_isin) AS count_precos,
        //      CASE count_precos 
        //         WHEN 2 THEN 
        //             LAST_VALUE(cot.preco_ultimo) OVER(PARTITION BY emp.nome_empresa, emp_isin.cod_isin ORDER BY cot.data_pregao) - 
        //             FIRST_VALUE(cot.preco_abertura) OVER(PARTITION BY emp.nome_empresa, emp_isin.cod_isin ORDER BY cot.data_pregao) AS preco_diff
        //         ELSE
        //             NULL
        // FROM empresa AS emp INNER JOIN empresa_isin emp_isin ON emp.cnpj = emp_isin.cnpj 
        //                   INNER JOIN (
        //                               SELECT slice_time as data_pregao, cod_isin, 
        //                                      TS_FIRST_VALUE(preco_abertura) as preco_abertura, 
        //                                      TS_FIRST_VALUE(preco_ultimo) as preco_ultimo
        //                               FROM cotacao
        //                               WHERE cod_bdi = 02
        //                               TIMESERIES slice_time AS '1 day' OVER (PARTITION BY cod_isin ORDER BY data_pregao)
        //                   ) AS cot ON emp_isin.cod_isin = cot.cod_isin,
        // WHERE (cot.data_pregao = '2010-10-1' OR cot.data_pregao = '2010-10-11')
        // ORDER BY emp_isin.cod_isin, cot.data_pregao;

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);
       
        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial,$data_final));
        
        # Fetch all rows
        $map = array();
        $prev_acao = "";
        while ($row = odbc_fetch_array($resultset)) {
            #echo $row['data_pregao']." | ".$row['cod_isin']." | ".$row['preco_abertura']." | ".$row['preco_ultimo'];
        	#$current_preco_abertura = $row['preco_abertura'];
               
            $nome_acao = $row['nome_empresa'] . "(" . $row['cod_isin'] . ")";
        	$current_preco_ultimo = $row['preco_ultimo'];
        	if($nome_acao == $prev_acao){
        		$preco_ultimo = $current_preco_ultimo;	
        		$delta = $preco_ultimo - $preco_abertura;
        		$map[$nome_acao] = $delta;
        	}else{
        		$preco_abertura = $row['preco_abertura'];
        	}
        	$prev_acao = $nome_acao;

            #echo " | " . $delta."\n";
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
        # TODO
        // return array($nomes, $valores);
    }

    function top_liquidez ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {

        if($metrica == "Menor Liquidez"){
        $query = "select e.nome_empresa,avg(c.volume_titulos) 
              from cotacao c, empresa_isin e_i,empresa e 
              where c.cod_isin=e_i.cod_isin and e_i.cnpj=e.cnpj 
              and (c.data_pregao between ? and ?) 
              and c.cod_bdi=02 group by e.nome_empresa order by avg LIMIT 10";
        } else {
        $query = "select e.nome_empresa,avg(c.volume_titulos) 
              from cotacao c, empresa_isin e_i,empresa e 
              where c.cod_isin=e_i.cod_isin and e_i.cnpj=e.cnpj 
              and (c.data_pregao between ? and ?) 
              and c.cod_bdi=02 group by e.nome_empresa order by avg desc LIMIT 10";
        }# Prepare the query


        # Turn on error reporting
        error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

        # Connect to the Database
        $dsn = "StocksDSN";
        $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);

        # Execute the query
        $success = odbc_execute($resultset, array($data_inicio,$data_fim));

        # Fetch all rows

        $nomes = array();
        $valores = array();
        $isin = "";
        while ($row = odbc_fetch_array($resultset)) {

        array_push($nomes, $row['nome_empresa']);
        array_push($valores, $row['avg']);
        }

        return array($nomes, $valores);
    }
?>

<?php 
    
    include 'global_model.php';
    
    /* ----------------------------------------------------------------------------------------------
        MAIN
    */

    # Reading Arguments...
    // $agrupamento = $_GET['top10_grouping'];
    // $metrica = $_GET['top10_metric'];
    // $top = $_GET['top'];
    // $data_inicial = $_GET['start_date_top10'];
    // $data_final = $_GET['end_date_top10'];
    
    $agrupamento = "Ação";
    $metrica = "Queda";
    $top = 10;
    $data_inicial = "03/09/2012";
    $data_final = "03/09/2012"; 
    
    # Argument casting...
    $agrupamento = strtolower(str_replace("-", "_", $agrupamento));

    list ($dia_inicial, $mes_inicial, $ano_inicial) = split("/", $data_inicial);
    $data_inicial = $ano_inicial . "-" . $mes_inicial . "-" . $dia_inicial;

    list ($dia_final, $mes_final, $ano_final) = split("/", $data_final);
    $data_final = $ano_final . "-" . $mes_final . "-" . $dia_final;

    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    // $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    switch ($metrica) {
        case "Crescimento":
        case "Queda":
            list($nomes, $valores) = top_cresce_decresce($conn, $query_map, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Oscilação":
            list($nomes, $valores) = top_oscilacao($conn, $query_map, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Maior Liquidez":
        case "Menor Liquidez":
            list($nomes, $valores) = top_liquidez($conn, $query_map, $agrupamento, 
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

    function top_cresce_decresce ($conn, $query_map, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {
        # Prepare the query
        $query = $query_map['top_crescimento_acao'];

        if($metrica == "Crescimento"){
            $query = str_replace("preco_diff ASC", "preco_diff DESC", $query);
        }

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);
       
        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial, $data_final, $data_inicial, $data_final));
        
        # Fetch the $top rows
        $nomes = array();
        $valores = array();
        $counter = 0;
        while ($row = odbc_fetch_array($resultset)) {
            if($counter >= $top){
                break;
            }
            array_push($nomes, $row['nome_grupo']);
            array_push($valores, $row['preco_diff']);
        }

        // $map = array();
        // // $prev_nome = "";
        // $counter = 0;
        // while ($row = odbc_fetch_array($resultset)) {
        //     if($counter >= $top){
        //         break;
        //     }
        //     $map[$row['nome_grupo']] = $row['preco_diff'];
        //     $counter++;
        //  //    $nome = ;
        //     // $preco_diff = ;
        //     // if($nome != $prev_nome){
        //  //        if ($preco_diff != NULL){
        //     //      
        //  //        }else{
        //  //            // We do nothing by now.
        //  //        }
        //     // }else{
        //     //  // The second name is not used
        //     // }
        //     // $prev_nome = $nome;
        // }
        // asort($map);
        // $keys = array_keys($map);

        // if($metrica == "Crescimento"){
        //     $nomes = array_reverse(array_slice($keys, count($keys) - $top, $top));
        //     $valores = array_reverse(array_slice(array_values($map), count($map) - $top, $top));
        // }else{
        //     $nomes = array_reverse(array_slice($keys, 0, $top));
        //     $valores = array_reverse(array_slice(array_values($map), 0, $top));
        // }
        
        return array($nomes, $valores);
    }

    function top_oscilacao ($conn, $query_map, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {

        # Prepare the query
        $query = $query_map['top_oscilacao'];
        switch ($agrupamento) {
            case 'ação':
                // $query = $query_map['top_oscilacao_acao'];
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", 
                    "CONCAT(CONCAT(CONCAT (emp.nome_empresa,' ('), emp_isin.cod_isin), ')')", $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp.nome_empresa, emp_isin.cod_isin", $query);
                break;
            case 'setor':
            case 'sub_setor':
            case 'segmento':
                // $query = str_replace("[EMP_COLUMN]", $agrupamento, $query_map['top_oscilacao']);
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", "emp." . $agrupamento, $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp." . $agrupamento, $query);
                break;
            default:
                echo "Grupo inexistente.";
                break;
        }

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);

        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial, $data_final, $top));

        # Fetch all rows
        $nomes = array();
        $valores = array();
        while ($row = odbc_fetch_array($resultset)) {
            array_push($nomes, $row['nome_grupo']);
            array_push($valores, $row['sum_abs_diff_preco_medio']);
        }

        return array($nomes, $valores);
    }

    function top_liquidez ($conn, $query_map, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {

        # Prepare the query
        $query = $query_map['top_liquidez'];
        switch ($agrupamento) {
            case 'ação':
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", 
                    "CONCAT(CONCAT(CONCAT (emp.nome_empresa,' ('), emp_isin.cod_isin), ')')", $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp.nome_empresa, emp_isin.cod_isin", $query);
                break;
            case 'setor':
            case 'sub_setor':
            case 'segmento':
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", "emp." . $agrupamento, $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp." . $agrupamento, $query);
                break;
            default:
                echo "Grupo inexistente.";
                break;
        }

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
            array_push($nomes, $row['nome_grupo']);
            array_push($valores, $row['sum_volume_titulos']);
        }

        return array($nomes, $valores);
    }
?>

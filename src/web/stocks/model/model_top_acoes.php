<?php 
    
    include 'global_model.php';
    
    /* ----------------------------------------------------------------------------------------------
        MAIN
    */

    # Reading Arguments...
    $agrupamento = $_GET['top_grouping'];
    $metrica = $_GET['top_metric'];
    $top = $_GET['top_n'];
    $data_inicial = $_GET['start_date_top'];
    $data_final = $_GET['end_date_top'];
    
    // $agrupamento = "Setor";
    // $metrica = "Queda";
    // $top = 10;
    // $data_inicial = "03/09/2012";
    // $data_final = "03/09/2012"; 
    
    # Argument casting...
    $agrupamento = strtolower(str_replace("-", "_", $agrupamento));

    list ($dia_inicial, $mes_inicial, $ano_inicial) = split("/", $data_inicial);
    $data_inicial = $ano_inicial . "-" . $mes_inicial . "-" . $dia_inicial;

    list ($dia_final, $mes_final, $ano_final) = split("/", $data_final);
    $data_final = $ano_final . "-" . $mes_final . "-" . $dia_final;

    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
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
        $query = $query_map['top_crescimento'];
        switch ($agrupamento) {
            case 'ação':
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", "nome_empresa", $query);
                $query = str_replace("[SUB_SELECT_EXTRA_COL]", "", $query);
                $query = str_replace("[GROUP_BY_COLS]", "nome_empresa", $query);
                break;
            case 'setor':
            case 'sub_setor':
            case 'segmento':
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", $agrupamento, $query);
                $query = str_replace("[SUB_SELECT_EXTRA_COL]", "emp.$agrupamento AS $agrupamento, ", $query);
                $query = str_replace("[GROUP_BY_COLS]", $agrupamento, $query);

                // We aggregate the differences by summing all differences from a group
                $query = str_replace("MAX(preco_diff)", "AVG(preco_diff)", $query);
                break;
            default:
                echo "Grupo inexistente.";
                break;
        }

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
        while (($row = odbc_fetch_array($resultset)) && ($counter < $top)) {
            array_push($nomes, $row['nome_grupo']);
            array_push($valores, round($row['preco_diff'], 2));
            $counter++;
        }
        return array($nomes, $valores);
    }

    function top_oscilacao ($conn, $query_map, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {

        # Prepare the query
        $query = $query_map['top_oscilacao'];
        switch ($agrupamento) {
            case 'ação':
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", 
                    "CONCAT(CONCAT(CONCAT (emp.nome_empresa,' ('), emp_isin.cod_isin), ')')", $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp.nome_empresa, emp_isin.cod_isin", $query);
                break;
            case 'setor':
            case 'sub_setor':
            case 'segmento':
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", "emp.$agrupamento", $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp.$agrupamento", $query);
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
                $query = str_replace("[SELECT_NOME_GRUPO_COL]", "emp.$agrupamento", $query);
                $query = str_replace("[GROUP_BY_COLS]", "emp.$agrupamento", $query);
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

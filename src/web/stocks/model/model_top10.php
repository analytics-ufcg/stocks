<?php 

    # Argument casting...
    
    $agrupamento = $_GET['search_type'];
    $metrica = $_GET['search_type_parametro'];
    $data_inicial = $_GET['tAdata_inicial'];
    $data_final = $_GET['tADataFinal'];

    list ($dia_inicial, $mes_inicial, $ano_inicial) = split("/", $data_inicial);
    $data_inicial = $dia_inicial . "-" . $mes_inicial . "-" . $ano_inicial;

    list ($dia_final, $mes_final, $ano_final) = split("/", $data_final);
    $data_final = $dia_final . "-" . $mes_final . "-" . $ano_final;

    # TODO: Define the SQL query based on the parameters


    # Prepare the query
    $query_oscillation = "select cod_isin, max(preco_maximo), min(preco_minimo)
                             from cotacao where (data_pregao between ? and ?) 
                             group by data_pregao, cod_isin order by cod_isin";


    
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($value));

    # Fetch all rows
    $all_table = array();

    while ($row = odbc_fetch_array($resultset)) {

        # Trim the strings and replace null with --        
        foreach ($row as $key => $value) {
            if (is_null($row[$key]) || $row[$key] == ""){
                $row[$key] = "--";
            }else{
                $row[$key] = trim($value);
            }
        }

        
    }

    # Close the connection
    odbc_close($conn);

    # print_r()
    echo json_encode(array("table" => $all_table));


?>
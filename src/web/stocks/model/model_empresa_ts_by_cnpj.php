<?php 

    include 'global_model.php';

    # Read the GET parameters
    $cnpj = $_GET['cnpj'];

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # ----------------------------------------------------------------------------------------
    # QUERY 1: Get the ISIN with the largest acao
    # ----------------------------------------------------------------------------------------

    # Prepare the query
    // $cnpj = '86550951000150';
    $query = str_replace("[EMP_CNPJ]", $cnpj, $query_map['get_isin_with_largest_ts_by_cnpj']);

    # Execute the query
    $resultset = odbc_prepare($conn, $query);
    $success = odbc_execute($resultset, array());

    $isin_row = array();
    while ($row = odbc_fetch_array($resultset)) {
        array_push($isin_row, $row);
    }

    if ($isin_row[0]['tamanho_cotacao'] <= 0){
        # There is no acao to all ISINs of that CNPJ, so we return the empty time-serie.
        echo json_encode(array());
    }else{
        # ----------------------------------------------------------------------------------------
        # QUERY 2: Get the acao by isin
        # ----------------------------------------------------------------------------------------
        
        # Prepare the query
        $query = str_replace("[EMP_ISIN]", $isin_row[0]['cod_isin'], $query_map['get_ts_by_isin']);
       
        # Execute the query
        $resultset = odbc_prepare($conn, $query);
        $success = odbc_execute($resultset, array());

        # Fetch all rows
        $preco_ultimo_ts = array();
        while ($row = odbc_fetch_array($resultset)) {
            array_push($preco_ultimo_ts, array(strtotime($row['data_pregao']) * 1000, (float) $row['preco_ultimo']));
        }
        echo json_encode($preco_ultimo_ts);
    }
    # Close the connection
    odbc_close($conn);
    // print_r($preco_ultimo_ts);

//     include 'global_model.php';

//     $cnpj = $_GET['cnpj'];

//     # Prepare the query
//     // $cnpj = '86550951000150';
//     $query = str_replace("[EMP_CNPJ]", $cnpj, $query_map['get_largest_ts_by_cnpj']);
    
    
//     # Turn on error reporting
//     error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

//     # Connect to the Database
//     $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

//     # Prepare the query
//     $resultset = odbc_prepare($conn, $query);
   
//     # Execute the query
//     $success = odbc_execute($resultset, array());

//     # Fetch all rows
//     $name_list = array();
//     $list_response = array();
//     $data = array();
//     $valores = array();
//     while ($row = odbc_fetch_array($resultset)) {
//         array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_ultimo']));
//         array_push($data, $row['data_pregao']);
//         array_push($valores, $row['preco_ultimo']);
//     }
    
//     # Close the connection
//     odbc_close($conn);
//     echo json_encode($list_response);
//     // print_r($list_response);
?>


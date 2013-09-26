<?php 

    include 'global_model.php';

    $isin = $_GET['isin'];
   // $isin = "BRPETRACNOR9";
    # Prepare the query
    // $cnpj = '86550951000150';
    $query = str_replace("[EMP_ISIN]", $isin, $query_map['get_ts_by_isin']);
    
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array());

    # Fetch all rows
    $name_list = array();
    $list_response = array();
    $data = array();
    $valores = array();
    while ($row = odbc_fetch_array($resultset)) {
        array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_ultimo']));
        array_push($data, $row['data_pregao']);
        array_push($valores, $row['preco_ultimo']);
        
    }
    
	# Close the connection
	odbc_close($conn);
    echo json_encode($list_response);
    // print_r($list_response);
?>
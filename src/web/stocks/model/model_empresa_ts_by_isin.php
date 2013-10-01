<?php 

    include 'global_model.php';

    $isin = $_GET['isin'];
    // $isin = "BRPETRACNOR9";
    
    # Prepare the query
    $query = str_replace("[EMP_ISIN]", $isin, $query_map['get_ts_by_isin']);

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array());

    # Fetch all rows
    $list_response = array();
    while ($row = odbc_fetch_array($resultset)) {
        # OLD
        array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_ultimo']));
        
        # NEW
        // $is_solavanco = TRUE;
        // if (rand(1,2) == 1){
        //     $is_solavanco = FALSE;
        // }
        // array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_ultimo'], $is_solavanco));

    }
    
	# Close the connection
	odbc_close($conn);
    echo json_encode($list_response);
    // print_r($list_response);
?>
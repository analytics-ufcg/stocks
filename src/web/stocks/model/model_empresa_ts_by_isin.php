<?php 
    include 'global_model.php';

    $isin = $_GET['isin'];
    // $isin = "BRPETRACNOR9";

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # ----------------------------------------------------------------------------------------
    # QUERY: Get the acao by isin
    # ----------------------------------------------------------------------------------------
    
    # Prepare the query
    $query = str_replace("[EMP_ISIN]", $isin, $query_map['get_ts_by_isin_with_solavanco']);
   
    # Execute the query
    $resultset = odbc_prepare($conn, $query);
    $success = odbc_execute($resultset, array());

    # Fetch all rows
    $list_response = array();

    while ($row = odbc_fetch_array($resultset)) {
        array_push($list_response, array(strtotime($row['data_pregao']) * 1000, 
                                        (float) $row['preco_ultimo'], 
                                        $row['is_solavanco']));
    }

    echo json_encode($list_response);

    # Close the connection
    odbc_close($conn);
?>
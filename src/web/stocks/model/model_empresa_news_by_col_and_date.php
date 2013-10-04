<?php 

    include 'global_model.php';

    # Read the query arguments
    $query_type = $_GET['query_type'];
    $query_value = $_GET['query_value'];
    $date = $_GET['date'];

    // $query_type = 'isin';
    // $query_value = 'BRBBASACNOR3';
    // $query_type = 'cnpj';
    // $query_value = '191';
    // $date = '2011-03-21';
    
    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    if($query_type == "cnpj"){
        $query = str_replace("[EMP_CNPJ]", $query_value, $query_map['get_news_by_cnpj_and_date']);
    }else{
        $query = str_replace("[EMP_ISIN]", $query_value, $query_map['get_news_by_isin_and_date']);
    }
    $query = str_replace("[NEWS_DATE]", $date, $query);

    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array());

    # Final results
    $folha = array();
    $estadao = array();
   
    while ($row = odbc_fetch_array($resultset)) {
        
        if($row['fonte'] == 'Folha de S.Paulo'){
            array_push($folha, array($row['titulo'], $row['link']));
        }else{
            array_push($estadao, array($row['titulo'], $row['link']));
        }
    }
    $list_response = array($folha, $estadao);
    
    echo json_encode($list_response);

	# Close the connection
	odbc_close($conn);
?>
<?php 

    include 'global_model.php';

   
    // $column = "setor";

    # Prepare the query
   	$query = "select preco_medio, data_pregao from Cotacao where cod_isin = 'BRPETRACNOR9' and COD_BDI = '02' order by data_pregao asc";
    
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
        array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_medio']));
        array_push($data, $row['data_pregao']);
        array_push($valores, $row['preco_medio']);
        
    }
    #arrayD = array($data, $valores);
	# Close the connection
	odbc_close($conn);
//echo json_encode(array("data" => $data, "valores" => $valores)); array(["1158278400000",74.10],["1158278400000",90.10])
    // echo json_encode(array([1158270400000,74.10],[1158271400000,90.10], [1158272400000,74.10], [1158277400000,74.10]));
  //  echo json_encode(array([$data,$valores]));
    //print_r($list_response);
    echo json_encode($list_response);
?>
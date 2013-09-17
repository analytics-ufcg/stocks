<?php 

    include 'global_model.php';

   
    // $column = "setor";

    # Prepare the query
   	$query = "select preco_medio, data_pregao from Cotacao where cod_isin = 'BRPETRACNOR9' and COD_BDI = '02'";
    
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

    $data = array();
    $valores = array();
    while ($row = odbc_fetch_array($resultset)) {
        array_push($data, $row['data_pregao']);
        array_push($valores, $row['preco_medio']);
    }

	# Close the connection
	odbc_close($conn);

    echo json_encode(array("data" => $data, "valores" => $valores));
?>
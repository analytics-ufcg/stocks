<?php 

    include 'global_model.php';
    # Prepare the query
   	// $query = "select acao.preco_ultimo, acao.data_pregao 
    //           from (SELECT slice_time as data_pregao, cod_isin, 
    //                 TS_FIRST_VALUE(preco_ultimo IGNORE NULLS, \'const\') as preco_ultimo
    //                 FROM cotacao
    //                 WHERE cod_bdi = 02
    //                 TIMESERIES slice_time AS \'1 day\' OVER (PARTITION BY cod_isin ORDER BY data_pregao)
    //           ) AS acao 
    //           where acao.cod_isin = 'BRPETRACNOR9' 
    //           order by acao.data_pregao asc";
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
    
	# Close the connection
	odbc_close($conn);
    echo json_encode($list_response);
?>
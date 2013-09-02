<?php 

    $data_inicio = "1994-07-05";
    $data_fim = "1994-07-06";

    # Prepare the query
   	$query = "select data_pregao,cod_isin, preco_abertura, preco_ultimo
                             from cotacao where (data_pregao = ? or data_pregao = ?) and cod_bdi = 02 
                              order by cod_isin";


    
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($data_inicio,$data_fim));
    
    # Fetch all rows
    $all_table = array();
    $map = array();
    $isin = "";
    while ($row = odbc_fetch_array($resultset)) {
	echo $row['data_pregao']." | ".$row['cod_isin']." | ".$row['preco_abertura']." | ".$row['preco_ultimo']."\n";
       
	$current_isin = $row['cod_isin'];
	#$current_preco_abertura = $row['preco_abertura'];
	$current_preco_ultimo = $row['preco_ultimo'];
	if($current_isin == $isin){
		$preco_ultimo = $current_preco_ultimo;	
		$delta = $preco_ultimo - $preco_abertura;
		$map[$current_isin] = $delta;
	}else{
		$preco_abertura = $row['preco_abertura'];
	}
	$isin = $current_isin;
    }
    asort($map);
    $menor = array_keys($map);
    echo $menor[count($menor)-1]." | ".$menor[count($menor)-2];
	# Close the connection
	odbc_close($conn);

    # print_r()
    echo json_encode(array("table" => $all_table));
?>
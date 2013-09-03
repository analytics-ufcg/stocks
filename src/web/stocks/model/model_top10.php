<?php 

    # Argument casting...
    
    #$agrupamento = $_GET['top10_grouping'];
    #$metrica = $_GET['top10_metric'];
    #$data_inicial = $_GET['top10_data_inicial'];
    #$data_final = $_GET['top10_data_final'];
    $data_inicial = "1994/07/05";
    $data_final = "1994/07/06"; 

    list ($dia_inicial, $mes_inicial, $ano_inicial) = split("/", $data_inicial);
    $data_inicial = $dia_inicial . "-" . $mes_inicial . "-" . $ano_inicial;

    list ($dia_final, $mes_final, $ano_final) = split("/", $data_final);
    $data_final = $dia_final . "-" . $mes_final . "-" . $ano_final;

    # TODO: Define the SQL query based on the parameters


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
    $success = odbc_execute($resultset, array($data_inicial,$data_final));
    
    # Fetch all rows
    $all_table = array();
    $map = array();
    $isin = "";
    while ($row = odbc_fetch_array($resultset)) {
	#echo $row['data_pregao']." | ".$row['cod_isin']." | ".$row['preco_abertura']." | ".$row['preco_ultimo']."\n";
       
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
    $keys = array_keys($map);
    echo $keys[count($keys)-3]." | ".$keys[count($keys)-4];
    $highest = array($keys[count($keys)-1],$keys[count($keys)-2],$keys[count($keys)-3],$keys[count($keys)-4],$keys[count($keys)-5],$keys[count($keys)-6],$keys[count($keys)-7],$keys[count($keys)-8],$keys[count($keys)-9],$keys[count($keys)-10]);
    $lowest = array($keys[0],$keys[1],$keys[2],$keys[3],$keys[4],$keys[5],$keys[6],$keys[7],$keys[8],$keys[0]);

	# Close the connection
	odbc_close($conn);

    # print_r()
    echo json_encode(array("table" => $highest));



    # Prepare the query
    #$query_oscillation = "select cod_isin, max(preco_maximo), min(preco_minimo)
                             #from cotacao where (data_pregao between ? and ?) 
                             #group by data_pregao, cod_isin order by cod_isin";


    
    # Turn on error reporting
    #error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    #$dsn = "StocksDSN";
    #$conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
   # $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    #$success = odbc_execute($resultset, array($value));

    # Fetch all rows
    #$all_table = array();

    #while ($row = odbc_fetch_array($resultset)) {

        # Trim the strings and replace null with --        
        #foreach ($row as $key => $value) {
            #if (is_null($row[$key]) || $row[$key] == ""){
               # $row[$key] = "--";
            #}else{
               # $row[$key] = trim($value);
            #}
        #}

        
    #}

    # Close the connection
    #odbc_close($conn);

    # print_r()
   # echo json_encode(array("table" => $all_table));


?>
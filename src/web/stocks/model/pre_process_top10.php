<?php 

    

    # Prepare the query
   	$query_oscillation = "select cod_isin, max(preco_maximo), min(preco_minimo)
                             from cotacao where (data_pregao between ? and ?) 
                             group by data_pregao, cod_isin order by cod_isin";


    
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($value));

    # Fetch all rows
    $all_table = array();

    while ($row = odbc_fetch_array($resultset)) {

        # Trim the strings and replace null with --        
        foreach ($row as $key => $value) {
            if (is_null($row[$key]) || $row[$key] == ""){
                $row[$key] = "--";
            }else{
                $row[$key] = trim($value);
            }
        }

        
    }

	# Close the connection
	odbc_close($conn);

    # print_r()
    echo json_encode(array("table" => $all_table));
?>
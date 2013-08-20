<?php
function get_all_from_empresa($column, $value) {
   
   	$query = "select * from empresa where ". $column . " = ?";
    
    # Turn on error reportin
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");
    
    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $sucess = odbc_execute($resultset,array($value));

    # Fetch all resultant table
    $array_result = array();
	$row = array(); 
	while (odbc_fetch_into($resultset, $row)) {
	    array_push($array_result, $row);
	    echo $row;
	} 

	# Close the connection
	odbc_close($conn);

	return $array_result;
}
?>
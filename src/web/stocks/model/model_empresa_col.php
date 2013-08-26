<?php 

    # Argument casting...
    $column = $_GET['search_type'];

    if($column == 'Sub-Setor'){
       $column = 'sub_setor';
    }

    // $column = strtolower($column);
    // $column = "setor";

    # Prepare the query
   	$query = "select " . $column . " from empresa";
    
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
    $name_list = array();

    while ($row = odbc_fetch_array($resultset)) {
        if (! in_array($row[$column], $name_list)){
            array_push($name_list, $row[$column]);
        }
    }

	# Close the connection
	odbc_close($conn);

    echo json_encode(array("name_list" => $name_list));
?>
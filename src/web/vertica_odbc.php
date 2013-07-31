<?php

	$teste = "outro teste";
	echo <b>"".$teste.""</b>;
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # A simple function to trap errors from queries
    function errortrap_odbc($conn, $sql) {
        if(!$rs = odbc_exec($conn,$sql)) {
            echo "Failed to execute SQL: $sql" . odbc_errormsg($conn) . "\n";
        } else {
            echo "Success: " . $sql . "\n";
        }
        return $rs;
    }


    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");
    echo "Connected with DSN: $dsn" . "\n";
   
    # Create a table
//    $sql = "CREATE TABLE TEST(
//            C_ID INT,
//            C_FP FLOAT,
//            C_VARCHAR VARCHAR(100),
//            C_DATE DATE,
//            C_TIME TIME,
//            C_TS TIMESTAMP)";
//    $result = errortrap_odbc($conn, $sql);
   
    # Insert data into the table with a standard SQL statement

//    $sql = "INSERT into test values(1,1.1,'abcdefg1234567890','1901-01-01','23:12:34 ','1901-01-01 09:00:09')";
//    $result = errortrap_odbc($conn, $sql);
   
    # Insert data into the table with odbc_prepare and odbc_execute
//    $values = array(2,2.28,'abcdefg1234567890','1901-01-01','23:12:34','1901-01-01 09:00:09');
//    $statement = odbc_prepare($conn,"INSERT into test values(?,?,?,?,?,?)");
   
    if(!$result = odbc_execute($statement, $values)) {
        echo "odbc_execute Failed!" . "\n";
    } else {
        echo "Success: odbc_execute." . "\n";
    }
   
    # Get the data from the table and display it
    $sql = "SELECT * FROM EMPRESA WHERE cnpj = 01547749000116";
    if($result = errortrap_odbc($conn, $sql)) {
        while($row = odbc_fetch_array($result) ) {
            print_r($row);
        }
    }


    # Drop the table and projection
    $sql = "DROP TABLE TEST CASCADE";
    $result = errortrap_odbc($conn, $sql);
    # Close the ODBC connection
    odbc_close($conn);

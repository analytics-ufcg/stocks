<?php 

    include 'global_model.php';

    //$isin = $_GET['isin'];
   
    # Prepare the query
    $cnpj = 33000167000101;
    $date = '2013-09-10';
    //$query = str_replace("[EMP_CNPJ]", $cnpj, $query_map['get_news_by_cnpj_and_date']);
    //$query = str_replace("[EMP_DATE]", $date, $query);

    $query = 'SELECT  data_noticia,titulo
            FROM Link_Noticias_Empresa
            WHERE cnpj = ? and data_noticia = ?';

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($cnpj,$date));

    # Fetch all rows
    $name_list = array();
    $list_response = array();
   
    while ($row = odbc_fetch_array($resultset)) {
        array_push($list_response, array($row['data_noticia'], $row['titulo']));
        
    }
    
	# Close the connection
	odbc_close($conn);
    echo json_encode($list_response);
    // print_r($list_response);
?>
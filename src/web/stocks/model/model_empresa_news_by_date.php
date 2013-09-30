<?php 

    include 'global_model.php';

    $cnpj = $_GET['cnpj'];
    $date = $_GET['date'];
    # Prepare the query
    //$cnpj = 33000167000101;
    //$date = '2013-09-10';
    //$query = str_replace("[EMP_CNPJ]", $cnpj, $query_map['get_news_by_cnpj_and_date']);
    //$query = str_replace("[EMP_DATE]", $date, $query);

    $query = 'SELECT  fonte,titulo,link
            FROM Link_Noticias_Empresa
            WHERE cnpj = ? and data_noticia = ?';

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($cnpj,$date));

    # Arrays para noticias de cada fonte
    $folha = array();
    $estadao = array();
   
    while ($row = odbc_fetch_array($resultset)) {
        
        # Coloca as noticias no array destinado a cada fonte
        if($row['fonte'] == 'Folha de S.Paulo'){
            array_push($folha, array($row['titulo'], $row['link']));
        }else{
            array_push($estadao, array($row['titulo'], $row['link']));
        }
        
    }
    $list_response = array($estadao, $folha);
    
    
	# Close the connection
	odbc_close($conn);
    echo json_encode($list_response);
    // print_r($list_response);
?>
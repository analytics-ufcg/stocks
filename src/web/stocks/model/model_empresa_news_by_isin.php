<?php 

    include 'global_model.php';

    $isin = $_GET['isin'];
    $date = $_GET['date'];
    # Prepare the query
    //$isin = 'BRVALEACNPA3';
    //$date = '2012-06-20';
    $query = 'SELECT  noticias.titulo,noticias.link
            FROM Link_Noticias_Empresa as noticias, Empresa_Isin as empresa
            WHERE empresa.cod_isin = ? and noticias.data_noticia = ? and empresa.cnpj = noticias.cnpj';

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($isin,$date));

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
<?php 

    # TODO: This could be handled before sending the GET request
    $value = $_GET['textArea'];
    $column = $_GET['combo'];

    if($column == 'ISIN'){
       $column = 'cod_isin';
    }else if($column == 'CVM'){
       $column = 'cod_cvm';
    }else if($column == 'Sub-Setor'){
       $column = 'sub_setor';
    }

    $column = strtolower($column);
    // $column = "sub_setor";
    // $value = "Transporte";

    # Prepare the query
   	$query = "select * from empresa where ". $column . " = ?";
    
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
	
    $row = array(); 
    while (odbc_fetch_into($resultset, $row)) {

        # TODO: Fix Bug HERE. No image being presented.
        $icon_filename = "./images/logos/" . $row['cnpj'] . ".jpg";

        if (!file_exists($icon_filename)){
            $icon_filename = "./images/logos/sem_imagem.jpg";
        }
        
        # TODO: This can be improved with array_push with a foreach
        # (http://stackoverflow.com/questions/5108293/php-array-mapping)
        $row_as_map = array("nome_empresa" => $row[0], "nome_pregao" => $row[1], 
                            "cod_negociacao" => $row[2], "cod_cvm" => $row[3],
                            "cnpj" => $row[4], "atividade_principal" => $row[5],
                            "setor" => $row[6], "sub_setor" => $row[7], "segmento" => $row[8],
                            "site" => $row[9], "rua" => $row[10], "cidade" => $row[11], 
                            "cep" => $row[12], "estado" => $row[13], "telefone" => $row[14], 
                            "fax" => $row[15], "nomes" => $row[16], "emails" => $row[17], 
                            "icon_filename" => $icon_filename);
	    array_push($all_table, $row_as_map);
	} 

	# Close the connection
	odbc_close($conn);

     echo json_encode(array("success" => $success, "table" => $all_table));
#}
?>
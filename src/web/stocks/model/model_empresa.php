<?php 

    include 'global_model.php';

    # Argument casting...
    $value = $_GET['text_area'];
    $column = $_GET['search_type'];

    if($column == 'ISIN'){
       $column = 'cod_isin';
    }else if($column == 'CVM'){
       $column = 'cod_cvm';
    }else if($column == 'Sub-Setor'){
       $column = 'sub_setor';
    }

    $column = strtolower($column);
    // $column = "cnpj";
    // $value = "56720428000163";

    # Prepare the query
    $query = str_replace("[EMP_COLUMN]", $column, $query_map['get_empresas_by_col']);
    
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
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

        $icon_filename = "../img/logos/" . $row['cnpj'] . ".jpg";
        
        # The client searches from the root.
        if (!file_exists($icon_filename)){
            $icon_filename = "./img/logos/sem_imagem.jpg";
        }else{
            $icon_filename = "./img/logos/" . $row['cnpj'] . ".jpg";
        }
        
        $row['icon_filename']  = $icon_filename;

        array_push($all_table, $row);
    }

	# Close the connection
	odbc_close($conn);

    echo json_encode(array("table" => $all_table));
?>
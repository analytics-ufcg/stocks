<?php 

    include 'global_model.php';

    $isin = $_GET['isin'];
    // $isin = "BRPETRACNOR9";
    
    # Prepare the query
    $query = str_replace("[EMP_ISIN]", $isin, $query_map['get_ts_by_isin']);

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array());

    # Fetch all rows
    $list_response = array();
    while ($row = odbc_fetch_array($resultset)) {
        # OLD
        //array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_ultimo']));
        
        # NEW
        $is_solavanco = TRUE;
        if (rand(1,2) == 1){
            $is_solavanco = FALSE;
        }
        array_push($list_response, array(strtotime($row['data_pregao'])*1000, (float)$row['preco_ultimo'], $is_solavanco));

    }
    //$arraySolavancos = gerarArraySolavancos($list_response);
    $list_final = array($list_response, (array) gerarArraySolavancos($list_response));
    //echo json_encode($list_final);
	# Close the connection
	odbc_close($conn);
    echo json_encode($list_final);
    // print_r($list_response);


    function gerarArraySolavancos($a1)
    {
        // $a1=array(array(12,13),array(14,12),array(34,128));
        // $a2=array(false,true,false);
        $y_anterior = null;
        $array_resposta = array();
        for($i = 0; $i < count($a1); $i++)
        {

            if(!$a1[$i][2])//n eh solavanco
            {
                $array_resposta[$i] = array($a1[$i][0],null,$a1[$i][2]);
            }else{//eh solavanco
                //$array_resposta[$i - 1][1] = $y_anterior;
                $array_resposta[$i] = $a1[$i];
            }
            $y_anterior = $a1[$i][1];
        }
        return $array_resposta;
    }
?>
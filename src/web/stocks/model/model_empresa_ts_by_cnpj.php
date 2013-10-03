<?php 

    include 'global_model.php';

    # Read the GET parameters
    $cnpj = $_GET['cnpj'];

    # Connect to the Database
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # ----------------------------------------------------------------------------------------
    # QUERY 1: Get the ISIN with the largest acao
    # ----------------------------------------------------------------------------------------

    # Prepare the query
    // $cnpj = '86550951000150';
    $query = str_replace("[EMP_CNPJ]", $cnpj, $query_map['get_isin_with_largest_ts_by_cnpj']);

    # Execute the query
    $resultset = odbc_prepare($conn, $query);
    $success = odbc_execute($resultset, array());

    $isin_row = array();
    while ($row = odbc_fetch_array($resultset)) {
        array_push($isin_row, $row);
    }
    
    if ($isin_row[0]['tamanho_cotacao'] <= 0){
        # There is no acao to all ISINs of that CNPJ, so we return the empty time-serie.
        echo json_encode(array());
    }else{
        # ----------------------------------------------------------------------------------------
        # QUERY 2: Get the acao by isin
        # ----------------------------------------------------------------------------------------
        
        # Prepare the query
        $query = str_replace("[EMP_ISIN]", $isin_row[0]['cod_isin'], $query_map['get_ts_by_isin']);
       
        # Execute the query
        $resultset = odbc_prepare($conn, $query);
        $success = odbc_execute($resultset, array());

        # Fetch all rows
        $list_response = array();
        while ($row = odbc_fetch_array($resultset)) {
            # OLD
           // array_push($list_response, array(strtotime($row['data_pregao']) * 1000, (float) $row['preco_ultimo']));
            # NEW
            $is_solavanco = TRUE;
            if (rand(1,2) == 1){
                $is_solavanco = FALSE;
            }
            array_push($list_response, array(strtotime($row['data_pregao']) * 1000, (float) $row['preco_ultimo'], $is_solavanco));

        }
       // $arraySolavancos = gerarArraySolavancos($list_response);

        $list_final = array($list_response, gerarArraySolavancos($list_response));
        echo json_encode($list_final);
    }
    # Close the connection
    odbc_close($conn);




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
                array_push($array_resposta, array($a1[$i][0],null,$a1[$i][2]));
            }else{//eh solavanco
               
                //$array_resposta[$i - 1][1] = $y_anterior;
                array_push($array_resposta, $a1[$i]);
                //$array_resposta[$i] = $a1[$i];
            }
            $y_anterior = $a1[$i][1];
        }
        return (array) $array_resposta;
    }
?>


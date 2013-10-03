<?php 
    # =========================================================================
    # MAIN
    # =========================================================================

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
        $query = str_replace("[EMP_ISIN]", $isin_row[0]['cod_isin'], $query_map['get_ts_by_isin_with_solavanco']);
       
        # Execute the query
        $resultset = odbc_prepare($conn, $query);
        $success = odbc_execute($resultset, array());

        # Fetch all rows
        $list_response = array();
        while ($row = odbc_fetch_array($resultset)) {
            array_push($list_response, array(strtotime($row['data_pregao']) * 1000, 
                                            (float) $row['preco_ultimo'], 
                                            $row['is_solavanco']));

        }

        echo json_encode(array($list_response, geraArraySolavancos($list_response)));
    }
    # Close the connection
    odbc_close($conn);

    # =========================================================================
    # FUNCTIONS
    # =========================================================================

    function geraArraySolavancos($list_series)
    {
        $array_resposta = array();
        for($i = 0; $i < count($list_series); $i++){
            if(!$list_series[$i][2]){
                //n eh solavanco
                array_push($array_resposta, array($list_series[$i][0], null, $list_series[$i][2]));
            }else{
                //eh solavanco
                array_push($array_resposta, $list_series[$i]);
            }
        }
        return $array_resposta;
    }
?>


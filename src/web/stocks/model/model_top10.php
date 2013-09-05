<?php 

    # Argument casting...
    
     //$agrupamento = $_GET['top10_grouping'];
     //$metrica = $_GET['top10_metric'];
    // $top = $_GET['top'];
     //$data_inicial = $_GET['start_date_wrapper'];
     //$data_final = $_GET['end_date_wrapper'];
    
    //$agrupamento = "Ação";
    $metrica = "Crescimento";
    $top = 10;
    $data_inicial = "03/09/2012";
    $data_final = "04/09/2012"; 
	
    list ($dia_inicial, $mes_inicial, $ano_inicial) = split("/", $data_inicial);
    $data_inicial = $ano_inicial . "-" . $mes_inicial . "-" . $dia_inicial;

    list ($dia_final, $mes_final, $ano_final) = split("/", $data_final);
    $data_final = $ano_final . "-" . $mes_final . "-" . $dia_final;

    

    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    switch ($metrica) {
        case "Crescimento":
        case "Queda":
            list($nome_empresas, $isins, $values) = top_cresce_decresce($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Oscilação":
            list($nome_empresas, $isins, $values) = top_oscilacao($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        case "Maior Liquidez":
        case "Menor Liquidez":
            list($nome_empresas, $isins, $values) = top_liquidez($conn, $agrupamento, 
                                                    $metrica, $top, $data_inicial, $data_final);
            break;
        default:
            echo "Metrica inexistente.";
            break;
    }

    # Close the connection
    odbc_close($conn);

    // echo print_r(array("rank" => $rank, "nome_empresa" => $nome_empresas, 
    //     "isins" => $isins, "value" => $values));
    echo json_encode(array("nome_empresas" => $nome_empresas, 
        "isins" => $isins, "values" => $values));


    function top_cresce_decresce ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {
         # Prepare the query
        $query = "select data_pregao,cod_isin, preco_abertura, preco_ultimo
                                 from cotacao where (data_pregao = ? or data_pregao = ?) and cod_bdi = 02 
                                  order by cod_isin";

        # Prepare the query
        $resultset = odbc_prepare($conn, $query);
       
        # Execute the query
        $success = odbc_execute($resultset, array($data_inicial,$data_final));
        
        # Fetch all rows
        $all_table = array();
        $map = array();
        $isin = "";
        while ($row = odbc_fetch_array($resultset)) {
        #	echo $row['data_pregao']." | ".$row['cod_isin']." | ".$row['preco_abertura']." | ".$row['preco_ultimo'];
               
        	$current_isin = $row['cod_isin'];
        	#$current_preco_abertura = $row['preco_abertura'];
        	$current_preco_ultimo = $row['preco_ultimo'];
        	if($current_isin == $isin){
        		$preco_ultimo = $current_preco_ultimo;	
        		$delta = $preco_ultimo - $preco_abertura;
        		$map[$current_isin] = $delta;
        	}else{
        		$preco_abertura = $row['preco_abertura'];
        	}
        	$isin = $current_isin;

         #   echo " | " . $delta."\n";
        }
        asort($map);
        $keys = array_keys($map);

        if($metrica == "Crescimento"){
            $nome_empresas = range(1, 10);
            $isins = array_reverse(array_slice($keys, count($keys) - $top, $top));
            $values = array_reverse(array_slice(array_values($map), count($map) - $top, $top));
        }else{
            $nome_empresas = range(11, 20);
            $isins = array_reverse(array_slice($keys, 0, $top));
            $values = array_reverse(array_slice(array_values($map), 0, $top));
        }
        
        return array($nome_empresas, $isins, $values);
    }

    function top_oscilacao ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {
        # TODO
        // return array($nome_empresas, $isins, $values);
    }

    function top_liquidez ($conn, $agrupamento, $metrica, $top, $data_inicial, $data_final)
    {
      
      if($metrica == "Menor Liquidez"){
        $query = "select e.nome_empresa,avg(c.volume_titulos) 
              from cotacao c, empresa_isin e_i,empresa e 
              where c.cod_isin=e_i.cod_isin and e_i.cnpj=e.cnpj 
              and (c.data_pregao between ? and ?) 
              and c.cod_bdi=02 group by e.nome_empresa order by avg LIMIT 10";
      } else {
        $query = "select e.nome_empresa,avg(c.volume_titulos) 
              from cotacao c, empresa_isin e_i,empresa e 
              where c.cod_isin=e_i.cod_isin and e_i.cnpj=e.cnpj 
              and (c.data_pregao between ? and ?) 
              and c.cod_bdi=02 group by e.nome_empresa order by avg desc LIMIT 10";
      }# Prepare the query
    


    
    # Turn on error reporting
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");

    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $success = odbc_execute($resultset, array($data_inicio,$data_fim));
    
    # Fetch all rows
    
    $nome_empresas = array();
    $values = array();
    $isin = "";
    while ($row = odbc_fetch_array($resultset)) {
    
       array_push($nome_empresas, $row['nome_empresa']);
       array_push($values, $row['avg']);
    }
    

    # Close the connection
    return array($nome_empresas,$values);
    }
?>

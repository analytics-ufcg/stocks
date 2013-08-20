<?php
    $textArea=$_POST['textArea'];

    $combo = $_POST['combo'];
    if($combo == 'ISIN'){
	   $combo = 'cod_isin';
    }else if($combo == 'CVM'){
	   $combo = 'cod_cvm';
    }else if($combo == 'Sub-Setor'){
	   $combo = 'sub_setor';
    }
    $combo = strtolower($combo);
  
    $query = "select * from empresa where ". $combo . " = ?";
    
    # Turn on error reportin
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");
    
    # Prepare the query
    $resultset = odbc_prepare($conn, $query);
   
    # Execute the query
    $sucess = odbc_execute($resultset,array($textArea));

    # Create the table result as html
    $table = "<table border='1'><tr><th>Logomarca</th><th>Dados Da Empresa</th><th>Contato</th><th>Qualificacao</th></tr><tbody><tr>";
  
    while($row = odbc_fetch_array($resultset)){
        $table = $table."<td><img src='./images/logos/3M.jpg'></td><td>Nome Empresa: "
        .$row['nome_empresa']."<br>Nome de Pregao: ".$row['nome_pregao']
        ."<br>Codigos de Negociacao <br>"."<br>Codigo CVM: ".$row['cod_cvm']."<br>CNPJ: "
        .$row['cnpj']."<br>Atividade Principal: ".$row['atividade_principal']."</td><td>"
        ."Site:".$row['site']."<br>Rua: ".$row['rua']."<br>CEP: ".$row['cep']."<br>Cidade: "
        .$row['cidade']."<br>Telefones: ".$row['telefone']."<br>Fax: ".$row['fax']."<br>Nomes:"
        .$row['nomes']."<br>Emails: ".$row['emails']
        ."</td><td>Setor: ".$row['setor']."<br>Subsetor: ".$row['sub_setor']."<br>Segmento: "
        .$row['segmento']."</td></tr>";
    }
    $table = $table."</tbody></table>";
	
    # Close the ODBC connection
    odbc_close($conn);
?>
<div><? echo $table;?></div>

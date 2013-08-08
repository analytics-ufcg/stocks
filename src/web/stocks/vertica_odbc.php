<html>
<body>
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
  
   $consulta = "select caminho_logo,nome_empresa,NOME_PREGAO,cod_isin,cod_cvm,cnpj,atividade_principal,setor,sub_setor,segmento,site,rua,cep,cidade,estado,telefone,fax,nomes,emails from Empresa where ".$combo . " = ?";
    # Turn on error reportin
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");
    #echo "Connected with DSN: $dsn" . "\n";

    $nome_empresa = "BCO SOFISA S.A.";
 
    $resultset = odbc_prepare($conn, $consulta);
   
    $sucess = odbc_execute($resultset,array($textArea));

    $table = "<table border='1'><tr><th>Logomarca</th><th>Dados Da Empresa</th><th>Contato</th><th>Qualificacao</th></tr><tbody><tr>";

  
   while($row = odbc_fetch_array($resultset)){
  	 $table = $table."<td>".$row['caminho_logo']."</td><td>Nome Empresa: ".$row['nome_empresa']."<br>Nome de Pregao: ".$row['NOME_PREGAO']."<br>Codigos de Negociacao <br>Codigo ISIN: ".$row['cod_isin']."<br>Codigo CVM: ".$row['cod_cvm']."<br>CNPJ: ".$row['cnpj']."<br>Atividade Principal: ".$row['atividade_principal']."</td><td>"."Site:".$row['site']."<br>Rua: ".$row['rua']."<br>CEP: ".$row['cep']."<br>Cidade: ".$row['cidade']."<br>Telefones: ".$row['telefone']."<br>Fax: ".$row['fax']."<br>Nomes:".$row['nomes']."<br>Emails: ".$row['emails']."</td><td>Setor: ".$row['setor']."<br>Subsetor: ".$row['sub_setor']."<br>Segmento: ".$row['segmento']."</td></tr>";
}
  $table = $table."</tbody></table>";
	#echo json_encode($table);




    # Close the ODBC connection
    odbc_close($conn);
?>	
<div><? echo $table;?></div>

</body>
</html>

<?php




$file = fopen("DadosEmpresas.csv","a+");
$array1 = array("NomeEmpr","NomePregao","CodNego","ISIN","CVM","CNPJ","ATIVIDADE","SETOR","SUBSETOR","SEGM","SITE","RUA","CIDADE","CEP","UF","TEL","FAX","NOME","EMAIL");
fputcsv($file, $array1, ',', '"');
$arquivo = file_get_contents("C:\\wamp\\www\\htmlPagina.txt");

$array_text = preg_split("/table/i",$arquivo);
$array_emp = preg_split("/<tr class=/i", $array_text[2]);


set_time_limit (0); 
ignore_user_abort(true);

for($i = 1;$i < count($array_emp);$i++){
//for($i = 1;$i < 10;$i++){
	


	
	$url = "http://www.bmfbovespa.com.br/cias-listadas/empresas-listadas/";
	$url2 = "http://www.bmfbovespa.com.br/";
	
	$linha = $array_emp[$i];
	$array_linha = preg_split("/href=/i",$linha);
	//echo $linha;
	$variavel = $array_linha[1];
	$indice_um = preg_split("/>/i",$variavel);
	$complemento_url = substr($indice_um[0],1,-1);
	$nome_empresa = substr($indice_um[1],0,-3);
	
	$indice_dois = preg_split("/>/i",$array_linha[2]);
	$nome_pregao = substr($indice_dois[1],0,-3);
	$segmento = $indice_dois[4];
	
	
	
	
	
	//function getPerfilEmpresa($url){
	$url = $url . $complemento_url;
	//echo "<p>Nome: .$nome_empresa</p>";
	//echo "<p>Url 1: .$url</p>";
	$arquivo2 = file_get_contents($url);
	$array_body = preg_split("/<body/i", $arquivo2);
	$array_frame = preg_split("/<iframe/i",$array_body[1]);
	$array_src = preg_split('/\ /',$array_frame[1]);
	$complemento_url2 = substr($array_src[2],11,-1);
	//}
	
	$url2 = $url2 . $complemento_url2;
	echo "<p>".$url2."</p>";
	//echo "<p>Url 2: .$url2</p>";
	$arquivo3 = file_get_contents($url2);
	$array_codigos_neg = preg_split("/C&oacute;digos de Negocia&ccedil;&atilde;o:/",$arquivo3);
	$codigo_neg2 = "";
	if (isset($array_codigos_neg[1])){
		$array_codigos_neg = preg_split("/<td class=/",$array_codigos_neg[1]);
		$array_codigos_neg2 = preg_split("/Mais C&oacute;digos/",$array_codigos_neg[1]);
		$array_codigos_neg2 = preg_split("/href/",$array_codigos_neg2[0]);
		for($j=1;$j < count($array_codigos_neg2) - 1;$j++){
			$codigo_neg2 = $codigo_neg2 . substr(preg_split("/>/",$array_codigos_neg2[$j])[1],0,-3).",";
		}
		
		$codigo_neg = preg_split("/</",$array_codigos_neg[1]);
		
		$codigo_neg = $codigo_neg[0];
		$codigo_neg = substr($codigo_neg,8);
		$codigoTemp = $codigo_neg;
		$codigo_neg_saida = $codigo_neg;
		
		if(count(preg_split("/Nenhum ativo/",$codigoTemp)) == 2){
			$codigo_neg_saida = $codigo_neg;
		}else{
			$codigo_neg_saida = substr($codigo_neg2,0,-1);
		}
		
	}else{
		$codigo_neg_saida = "";
	}
	$codigo_neg_saida = html_entity_decode(trim($codigo_neg_saida), ENT_COMPAT, "UTF-8");
	
	$codigo_isin = preg_split("/C&oacute;digos ISIN:/",$arquivo3);
	if(isset($codigo_isin[1])){
		$codigo_isin = preg_split("/<td class=/",$codigo_isin[1]);
		$codigo_isin = preg_split("/</",$codigo_isin[1]);
		$codigo_isin = $codigo_isin[0];
		$codigo_isin = substr($codigo_isin,8);
	}else{
		$codigo_isin = "isin";
	}
	$codigo_isin = html_entity_decode(trim($codigo_isin), ENT_COMPAT, "UTF-8");
	
	$codigo_cvm = preg_split("/C&oacute;digos CVM:/",$arquivo3);
	if(isset($codigo_cvm[1])){
		$codigo_cvm = preg_split("/<td class=/",$codigo_cvm[1]);
		$codigo_cvm = preg_split("/</",$codigo_cvm[1]);
		$codigo_cvm = $codigo_cvm[0];
		$codigo_cvm = substr($codigo_cvm,8);
	}else{
		$codigo_cvm = "cvm";
	}
	
	$codigo_cvm = html_entity_decode(trim($codigo_cvm), ENT_COMPAT, "UTF-8");
	
	$cnpj = preg_split("/CNPJ:/", $arquivo3);
	if(isset($cnpj[1])){
		$cnpj = preg_split("/<td class=/", $cnpj[1]);
		$cnpj = preg_split("/</", $cnpj[1]);
		$cnpj = $cnpj[0];
		$cnpj = substr($cnpj,8);
	}else{
		$cnpj = "cnpj";
	}
	$cnpj = html_entity_decode(trim($cnpj), ENT_COMPAT, "UTF-8");
	
	$atividade_princ = preg_split("/Atividade Principal:/", $arquivo3);
	if(isset($atividade_princ[1])){
		$atividade_princ = preg_split("/<td class=/",$atividade_princ[1]);
		$atividade_princ = preg_split("/</",$atividade_princ[1]);
		$atividade_princ = $atividade_princ[0];
		$atividade_princ = substr($atividade_princ, 8);
	}else{
		$atividade_princ = "ativ_prin";
	}
	$atividade_princ = html_entity_decode(trim($atividade_princ), ENT_COMPAT, "UTF-8");
	
	$classif_setorial = preg_split("/Classifica&ccedil;&atilde;o Setorial:/", $arquivo3);
	if(isset($classif_setorial[1])){
		$classif_setorial = preg_split("/<td class=/",$classif_setorial[1]);
		$classif_setorial = preg_split("/</",$classif_setorial[1]);
		$classif_setorial = $classif_setorial[0];
		$classif_setorial = substr($classif_setorial,8);
		$classif_setorial = preg_split("'/'",$classif_setorial);
 		$setor = $classif_setorial[0];
 		$subsetor = $classif_setorial[1];
 		$segmento = $classif_setorial[2];
	}else{
		$setor = "";
		$subsetor = "";
		$segmento = "";
	}
	$setor = html_entity_decode(trim($setor), ENT_COMPAT, "UTF-8");
	$subsetor = html_entity_decode(trim($subsetor), ENT_COMPAT, "UTF-8");
	$segmento = html_entity_decode(trim($segmento), ENT_COMPAT, "UTF-8");
	
	$site = preg_split("/Site:/",$arquivo3);
	if (isset($site[1])){
		$site = preg_split("/<td class=/",$site[1]);
		$site = preg_split("/</",$site[1]);
		$site = $site[1];
		$site = substr($site,8);
		$site = preg_split("/>/",$site);
		$site = $site[1];
	}else{
		$site = "";
	}
	$site = html_entity_decode(trim($site), ENT_COMPAT, "UTF-8");
	
	$endereco = preg_split("/Endere&ccedil;o:/",$arquivo3);
	if (isset($endereco[1])){
		$endereco = preg_split("/>/",$endereco[1]);
		$rua = substr($endereco[2],0,-6);
		$cep = substr($endereco[5],0,-18);
		$cidade = substr($endereco[7],0,-12);
		$cidade = preg_replace("/<span/", "", $cidade);
		$uf = substr($endereco[9],0,-5);
	}else{
		$rua = "";
		$cep = "";
		$cidade = "";
		$uf = "";
	}
	$rua = html_entity_decode(trim($rua), ENT_COMPAT, "UTF-8");
	$cep = html_entity_decode(trim($cep), ENT_COMPAT, "UTF-8");
	$cidade = html_entity_decode(trim($cidade), ENT_COMPAT, "UTF-8");
	$uf = html_entity_decode(trim($uf), ENT_COMPAT, "UTF-8");
	
	
	$array_telefone = preg_split("/Telefone:/", $arquivo3);
	$telefone = "";
	$fax = "";
	for($u =1;$u < count($array_telefone);$u++){
	if (isset($array_telefone[$u])){
			$array_telefone_aux = preg_split("/&nbsp;/",$array_telefone[$u]);
			$ddd = substr($array_telefone_aux[0],-4);
			$numero = $array_telefone_aux[1];
			$telefone = $telefone.$ddd. $numero.",";
			$fax = $fax .$array_telefone_aux[4].substr($array_telefone_aux[5],0,9).",";
		}else{
			$telefone = "";
			$fax ="";
		}
	}
		$telefone = substr($telefone,0,-1);
		$fax = substr($fax,0,-1);
		
	$telefone = trim($telefone);
	$fax = trim($fax);	
	
	$array_email = preg_split("/E-mail:/",$arquivo3);
	$emailAux = "";
	for($t=1;$t < count($array_email);$t++){
		if (isset($array_email[$t])){
			$email = preg_split("/>/",$array_email[$t]);
			$emailAux = $emailAux. trim(substr($email[2],0,-4)).",";
		}else{
			$emailAux= "";
		}
	}
		
	
	$emailAux = substr($emailAux, 0,-1);
	$emailAux = html_entity_decode($emailAux, ENT_COMPAT, "UTF-8");
	
	$nome = preg_split("/Nome:/",$arquivo3);
	if (isset($nome[1])){
		$nome = preg_split("/>/",$nome[1]);
		$nome = substr($nome[2],0,-4);
	}else{
		$nome = "";
	}
	$nome = trim($nome);
	
	
	$contato = preg_split("/Contato:/",$arquivo3);
		if(count($contato) > 1){
			if (isset($contato[1])){
				$contato = preg_split("/<td class=/",$contato[1]);
				$contato = preg_split("/>/",$contato[1]);
				$contato = trim(substr($contato[1],0,-5));
				$nome = $nome.",".$contato;
				$nome = html_entity_decode($nome, ENT_COMPAT, "UTF-8");
			}else{
				$contato = "";
			}
		}else{
			$contato = "";
		}
		
		

 	$saida = array($nome_empresa,$nome_pregao,$codigo_neg_saida,$codigo_isin,$codigo_cvm,$cnpj,$atividade_princ,$setor,$subsetor,$segmento,$site,$rua,$cidade,$cep,$uf,$telefone,$fax,$nome,$emailAux);
 	//echo "<p>".$saida."</p>";
 	//$saida = html_entity_decode($saida, ENT_COMPAT, "UTF-8");

 	//$arraysaida = preg_split("/,/",$saida);

 	fputcsv($file, $saida, ',', '"');
	
	

	}
 	fclose($file);
?>

<?php

/*
 * @author Elias Paulino
 * Script para coleta de informacoes de empresas listada na Bovespa.
 * A coleta acontece em 3 passos:
 * 	Passo 1 - Leitura do HTML onde existe URLs de todas as empresas listadas.
 * 	Passo 2 - Leitura do HTML por empresa.
 * 	Passo 3 - Leitura do HTML do campo de informacoes lincado na pagina individual da empresa.
 * 			  Com esse HTML pega-se todas as informacoes necessarias.  
 */

//Cria arquivo de saida com todas as informacoes para cada empresa.
$file = fopen("data/DadosEmpresas.csv","a+");
//Lê arquivo HTML com a lista de empresas.
$arquivo = file_get_contents("data/htmlPagina.txt");
//Divide pagina HTML da lista de empresas para pegar campo de informacao de cada empresa.
$array_text = preg_split("/table/i",$arquivo);
$array_emp = preg_split("/<tr class=/i", $array_text[2]);
for($i = 1;$i < count($array_text);$i++){
 #echo $array_text[i];
}
set_time_limit (0); 
ignore_user_abort(true);

//Loop na lista de empresas
for($i = 1;$i < count($array_emp);$i++){

	$empresa = $array_emp[$i];
	
	$nome_empresa = getNomeEmpresa($empresa);
	$nome_pregao = getNomePregao($empresa);
	$arquivo2 = getHTMLPorEmpresa($empresa);
	$arquivo3 = getHTMLcampoInformacoes($arquivo2);
	$codigoNegociacao = getCodigoNegociacao($arquivo3);
	$codigoISIN = getCodigoISIN($arquivo3);
	$codigoCVM = getCodigoCVM($arquivo3);
	$cnpj = getCNPJ($arquivo3);
	$ativPrincipal = getAtividadePrincipal($arquivo3);
	$classifSetorial = getClassificacaoSetorial($arquivo3);
	$setor = getSetor($classifSetorial);
	$subsetor = getSubSetor($classifSetorial);
	$segmento = getSegmento($classifSetorial);
	$site = getSite($arquivo3);
	$rua = getRua($arquivo3);
	$cep = getCep($arquivo3);
	$uf = getUF($arquivo3);
	$cidade = getCidade($arquivo3);
	$telefone = getTelefone($arquivo3);
	$fax = getFax($arquivo3);
	$email = getEmail($arquivo3);
	$nome = getNome($arquivo3);
	$contato = getContato($arquivo3,$nome);
	$caminhoLogo = getCaminhoLogo($cnpj);
	//array com informacoes para escrita no csv
	$saida = array($caminhoLogo,$nome_empresa,$nome_pregao,$codigoNegociacao,$codigoISIN,$codigoCVM,$cnpj,$ativPrincipal,$setor,$subsetor,$segmento,$site,$rua,$cidade,$cep,$uf,$telefone,$fax,$nome,$email);
 	//escrita das informacoes no csv.
	fputcsv($file, $saida, ',', '"');
	
}
	//fecha arquivo de saida.
fclose($file);

 	/*
 	 * Metodo retorna pagina html de uma empresa.
 	 * Argumento: campo com informacao da empresa na pagina html da lista de empresas.
 	 */
 	function getHTMLPorEmpresa($empresa){

 		$url = "http://www.bmfbovespa.com.br/cias-listadas/empresas-listadas/";
 		$array_empresa = preg_split("/href=/i",$empresa);
 		$indice_um = $array_empresa[1];
 		$array_indice_um = preg_split("/>/i",$indice_um);
 		$complemento_url = substr($array_indice_um[0],1,-1);
 		$url = $url . $complemento_url;
 		$html = file_get_contents($url);
 		return $html;
 	}
 	
 	/*
 	 * Metodo retorna pagina html do campo de informacoes anexa na pagina principal da empresa.
 	 * Argumento: pagina html principal da empresa.
 	 */
 	function getHTMLcampoInformacoes($htmlEmpresa){

 		$url2 = "http://www.bmfbovespa.com.br/";
 		$array_body = preg_split("/<body/i", $htmlEmpresa);
 		$array_frame = preg_split("/<iframe/i",$array_body[1]);
 		$array_src = preg_split('/\ /',$array_frame[1]);
 		$complemento_url2 = substr($array_src[2],11,-1);
 		$url2 = $url2 . $complemento_url2;
 		$html = file_get_contents($url2);
 		return $html;
 	}
 	
 	/*
 	 * Metodo retorna nome da empresa.
 	 * Argumento: campo com informacao da empresa na pagina html da lista de empresas.
 	 */
 	function getNomeEmpresa($empresa){

 		$array_empresa = preg_split("/href=/i",$empresa);
 		$indice_um = $array_empresa[1];
 		$array_indice_um = preg_split("/>/i",$indice_um);
 		$nome_empresa = substr($array_indice_um[1],0,-3);
 		return $nome_empresa;
 	}
 	
 	/*
 	 * Metodo retorna nome da empresa no pregao.
 	 * Argumento: campo com informacao da empresa na pagina html da lista de empresas.
 	 */
 	function getNomePregao($empresa){
 		$array_empresa = preg_split("/href=/i",$empresa);
 		$indice_dois = preg_split("/>/i",$array_empresa[2]);
 		$nome_pregao = substr($indice_dois[1],0,-3);
 		return $nome_pregao;
 	}
 	
 	/*
 	 * Metodo retorna codigo de negociacao da empresa.
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getCodigoNegociacao($paginaHTML){
 		$array_codigos_neg = preg_split("/C&oacute;digos de Negocia&ccedil;&atilde;o:/",$paginaHTML);
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
 			$codigo_neg_saida = "NA";
 		}
 		$codigo_neg_saida = html_entity_decode(trim($codigo_neg_saida), ENT_COMPAT, "UTF-8");
 		if($codigo_neg_saida == ""){
 			$codigo_neg_saida = "NA";
 		}
 		return $codigo_neg_saida;
 	}
 	
 	/*
 	 * Metodo retorna o codigo ISIN da empresa.
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getCodigoISIN($paginaHTML){

 		$codigo_isin = preg_split("/C&oacute;digos ISIN:/",$paginaHTML);
 		if(isset($codigo_isin[1])){
 			$codigo_isin = preg_split("/<td class=/",$codigo_isin[1]);
 			$codigo_isin = preg_split("/</",$codigo_isin[1]);
 			$codigo_isin = $codigo_isin[0];
 			$codigo_isin = substr($codigo_isin,8);
 		}else{
 			$codigo_isin = "NA";
 		}
 		$codigo_isin = html_entity_decode(trim($codigo_isin), ENT_COMPAT, "UTF-8");
 		if($codigo_isin == ""){
 			$codigo_isin = "NA";
 		}

 		return $codigo_isin;
 	}
 	
 	/*
 	 * Metodo retorna codigo CVM da empresa.
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getCodigoCVM($paginaHTML){

 		$codigo_cvm = preg_split("/C&oacute;digos CVM:/",$paginaHTML);
 		if(isset($codigo_cvm[1])){
 			$codigo_cvm = preg_split("/<td class=/",$codigo_cvm[1]);
 			$codigo_cvm = preg_split("/</",$codigo_cvm[1]);
 			$codigo_cvm = $codigo_cvm[0];
 			$codigo_cvm = substr($codigo_cvm,8);
 		}else{
 			$codigo_cvm = "NA";
 		}

 		$codigo_cvm = html_entity_decode(trim($codigo_cvm), ENT_COMPAT, "UTF-8");
 		if($codigo_cvm == ""){
 			$codigo_cvm = "NA";
 		}

 		return $codigo_cvm;
 	}
 	
 	/*
 	 * Metodo retorna cnpj da empresa.
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getCNPJ($paginaHTML){

 		$cnpj = preg_split("/CNPJ:/", $paginaHTML);
 		if(isset($cnpj[1])){
 			$cnpj = preg_split("/<td class=/", $cnpj[1]);
 			$cnpj = preg_split("/</", $cnpj[1]);
 			$cnpj = $cnpj[0];
 			$cnpj = substr($cnpj,8);
 		}else{
 			$cnpj = "NA";
 		}
 		$cnpj = html_entity_decode(trim($cnpj), ENT_COMPAT, "UTF-8");
 		$cnpj = str_replace(".","",$cnpj);
 		$cnpj = str_replace("-", "",$cnpj);
 		$cnpj = str_replace("/", "",$cnpj);
 		if($cnpj == ""){
 			$cnpj = "NA";
 		}

 		return $cnpj;
 	}
 	
 	/*
 	 * Metodo retorna principal atividade da empresa.
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getAtividadePrincipal($paginaHTML){

 		$atividade_princ = preg_split("/Atividade Principal:/", $paginaHTML);
 		if(isset($atividade_princ[1])){
 			$atividade_princ = preg_split("/<td class=/",$atividade_princ[1]);
 			$atividade_princ = preg_split("/</",$atividade_princ[1]);
 			$atividade_princ = $atividade_princ[0];
 			$atividade_princ = substr($atividade_princ, 8);
 		}else{
 			$atividade_princ = "NA";
 		}
 		$atividade_princ = html_entity_decode(trim($atividade_princ), ENT_COMPAT, "UTF-8");
 		if($atividade_princ == ""){
 			$atividade_princ = "NA";
 		}
 		return $atividade_princ;
 	}
 	
 	/*
 	 * Metodo retorna a classificacao setorial da empresa.Esse campo e uma string com o setor 
 	 * subsetor e segmento da empresa separados por "/".
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getClassificacaoSetorial($paginaHTML){

 		$classif_setorial = preg_split("/Classifica&ccedil;&atilde;o Setorial:/", $paginaHTML);
 		if(isset($classif_setorial[1])){
 			$classif_setorial = preg_split("/<td class=/",$classif_setorial[1]);
 			$classif_setorial = preg_split("/</",$classif_setorial[1]);
 			$classif_setorial = $classif_setorial[0];
 			$classif_setorial = substr($classif_setorial,8);

 		}else{
 			$classif_setorial = "NA";

 		}

 		return $classif_setorial;
 	}
 	
 	/*
 	 * Metodo retorna o setor de atuacao da empresa.
 	 * Argumento: classificacao setorial da empresa.
 	 */
 	function getSetor($classifSetorial){

 		if($classifSetorial != "NA"){
 			$arrayClassifSetorial = preg_split("'/'",$classifSetorial);
 			$setor = $arrayClassifSetorial[0];
 			$setor = html_entity_decode(trim($setor), ENT_COMPAT, "UTF-8");
 		}else{
 			$setor = "NA";
 		}

 		return $setor;
 	}
 	
 	/*
 	 * Metodo retorna o subsetor de atuacao da empresa.
 	* Argumento: classificacao setorial da empresa.
 	*/
 	function getSubSetor($classifSetorial){

 		if($classifSetorial != "NA"){
 			$arrayClassifSetorial = preg_split("'/'",$classifSetorial);
 			$subsetor = $arrayClassifSetorial[1];
 			$subsetor = html_entity_decode(trim($subsetor), ENT_COMPAT, "UTF-8");
 		}else{
 			$subsetor = "NA";
 		}

 		return $subsetor;
 	}
 	
 	/*
 	 * Metodo retorna o segmento de atuacao da empresa.
 	* Argumento: classificacao setorial da empresa.
 	*/
 	function getSegmento($classifSetorial){

 		if($classifSetorial != "NA"){
 			$arrayClassifSetorial = preg_split("'/'",$classifSetorial);
 			$segmento = $arrayClassifSetorial[2];
 			$segmento = html_entity_decode(trim($segmento), ENT_COMPAT, "UTF-8");
 		}else{
 			$segmento = "NA";
 		}

 		return $segmento;

 	}
 	
 	/*
 	 * Metodo retorna site da empresa.
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getSite($paginaHTML){

 		$site = preg_split("/Site:/",$paginaHTML);
 		if (isset($site[1])){
 			$site = preg_split("/<td class=/",$site[1]);
 			$site = preg_split("/</",$site[1]);
 			$site = $site[1];
 			$site = substr($site,8);
 			$site = preg_split("/>/",$site);
 			$site = $site[1];
 		}else{
 			$site = "NA";
 		}
 		$site = html_entity_decode(trim($site), ENT_COMPAT, "UTF-8");
 		if($site == ""){
 			$site = "NA";
 		}
 		return $site;
 	}
 	
 	/*
 	 * Metodo retorna nome da rua do campo de endereco da empresa
 	 * Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	 */
 	function getRua($paginaHTML){

 		$endereco = preg_split("/Endere&ccedil;o:/",$paginaHTML);
 		if (isset($endereco[1])){
 			$endereco = preg_split("/>/",$endereco[1]);
 			$rua = substr($endereco[2],0,-6);
 		}else{
 			$rua = "NA";

 		}
 		$rua = html_entity_decode(trim($rua), ENT_COMPAT, "UTF-8");

 		if($rua == ""){
 			$rua = "NA";
 		}

 		return $rua;
 	}
 	
 	/*
 	 * Metodo retorna o codigo UF do campo de endereco da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getUF($paginaHTML){
 		$endereco = preg_split("/Endere&ccedil;o:/",$paginaHTML);
 		if (isset($endereco[1])){
 			$endereco = preg_split("/>/",$endereco[1]);
 			$uf = substr($endereco[9],0,-5);
 		}else{
 			$uf = "NA";
 		}

 		$uf = html_entity_decode(trim($uf), ENT_COMPAT, "UTF-8");

 		if($uf == ""){
 			$uf = "NA";
 		}
 		return $uf;

 	}
 	
 	/*
 	 * Metodo retorna nome da cidade do campo de endereco da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getCidade($paginaHTML){

 		$endereco = preg_split("/Endere&ccedil;o:/",$paginaHTML);
 		if (isset($endereco[1])){
 			$endereco = preg_split("/>/",$endereco[1]);
 			$cidade = substr($endereco[7],0,-12);
 			$cidade = preg_replace("/<span/", "", $cidade);
 		}else{
 			$cidade = "NA";
 		}

 		$cidade = html_entity_decode(trim($cidade), ENT_COMPAT, "UTF-8");

 		if($cidade == ""){
 			$cidade = "NA";
 		}

 		return $cidade;
 	}
 	
 	/*
 	 * Metodo retorna o CEP do campo de endereco da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getCep($paginaHTML){

 		$endereco = preg_split("/Endere&ccedil;o:/",$paginaHTML);
 		if (isset($endereco[1])){
 			$endereco = preg_split("/>/",$endereco[1]);
 			$cep = substr($endereco[5],0,-18);
 		}else{
 			$cep = "NA";
 		}

 		$cep = html_entity_decode(trim($cep), ENT_COMPAT, "UTF-8");
 		if($cep == ""){
 			$cep = "NA";
 		}
 		return $cep;
 	}
 	
 	/*
 	 * Metodo retorna telefone da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getTelefone($paginaHTML){

 		$array_telefone = preg_split("/Telefone:/", $paginaHTML);
 		$telefone = "";
 		for($u =1;$u < count($array_telefone);$u++){
 			if (isset($array_telefone[$u])){
 				$array_telefone_aux = preg_split("/&nbsp;/",$array_telefone[$u]);
 				$ddd = substr($array_telefone_aux[0],-4);
 				$numero = $array_telefone_aux[1];
 				$telefone = $telefone.$ddd. $numero.",";
 			}else{
 				$telefone = "NA";
 			}
 		}
 		$telefone = substr($telefone,0,-1);
 		$telefone = trim($telefone);
 		if($telefone == ""){
 			$telefone = "NA";
 		}
 		return $telefone;
 	}
 	
 	/*
 	 * Metodo retorna o numero de fax da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getFax($paginaHTML){

 		$array_telefone = preg_split("/Telefone:/", $paginaHTML);
 		$fax = "";
 		for($u =1;$u < count($array_telefone);$u++){
 			if (isset($array_telefone[$u])){
 				$array_telefone_aux = preg_split("/&nbsp;/",$array_telefone[$u]);
 				$fax = $fax .$array_telefone_aux[4].substr($array_telefone_aux[5],0,9).",";
 			}else{
 				$fax ="NA";
 			}
 		}
 		$fax = substr($fax,0,-1);
 		$fax = trim($fax);

 		if($fax == ""){
 			$fax = "NA";
 		}
 		return $fax;
 	}
 	
 	/*
 	 * Metodo retorna endereco de email da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getEmail($paginaHTML){

 		$array_email = preg_split("/E-mail:/",$paginaHTML);
 		$emailAux = "";
 		for($t=1;$t < count($array_email);$t++){
 			if (isset($array_email[$t])){
 				$email = preg_split("/>/",$array_email[$t]);
 				$emailAux = $emailAux. trim(substr($email[2],0,-4)).",";
 			}else{
 				$emailAux= "NA";
 			}
 		}

 		$emailAux = substr($emailAux, 0,-1);
 		$emailAux = html_entity_decode($emailAux, ENT_COMPAT, "UTF-8");
 		if($emailAux == ""){
 			$emailAux = "NA";
 		}
 		return $emailAux;
 	}
 	
 	/*
 	 * Metodo retorna nome do contato da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	*/
 	function getNome($paginaHTML){
 		$nome = preg_split("/Nome:/",$paginaHTML);
 		if (isset($nome[1])){
 			$nome = preg_split("/>/",$nome[1]);
 			$nome = substr($nome[2],0,-4);
 		}else{
 			$nome = "NA";
 		}
 		$nome = trim($nome);
 		if($nome == ""){
 			$nome = "NA";
 		}
 		return $nome;
 	}
 	
 	/*
 	 * Metodo retorna nome do escriturador da empresa
 	* Argumento: pagina html do campo de Dados da companhia anexa na pagina principal da empresa.
 	* Nome de contato da empresa
 	*/
 	function getContato($paginaHTML,$nome){

 		$contato = preg_split("/Contato:/",$paginaHTML);
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
 		return $contato;
 	}
 	
 	/*
 	 * Metodo retorna um caminho no servidor criado para cada logomarca.
 	* Argumento: cnpj da empresa.
 	*/
 	function getCaminhoLogo($cnpj){
 		return "/home/stocks/web_gui/".$cnpj;
 	}
 	
 	
 	?>

<?php  
/*
> Sprint 1 - US 2
> Danilo Gomes
> Codigo para pegar as logomarcas das empresas que estão na bolsa de valores no site infomoney
> Tem como saida as imagens das logomarcas das empresas que existem no site no formato .jpg, com o nome do 
arquivo sendo o nome de pregão da empresa

*/
$sitePrincipal = file_get_contents('http://www.infomoney.com.br/mercados/empresas-bovespa');//pega o html do site, como uma unica string
$padrao = "<div class=\"por-setor ordem";//as empresas sao organizadas de tres formas na pagina, irei quebra ai para pegar as logos deapenas uma dessas listagens
$quebraSite = explode($padrao, $sitePrincipal);//quebra a string(parametro 2) pelo padrao(parametro 1) deixando as partes em um array
$codigoParteIcones = $quebraSite[2];//pega a listagem por ordem alfabetica
$empresas = explode("<li>", $codigoParteIcones);//quebrando em <li> cada elemento do array sera o codigo de cada empresa, exceto pelo elemento 0 que nao eh empresa

//iterar em cada empresa
for($i = 1; $i <= count($empresas); $i++){
	set_time_limit ( 0 );//ao passar 30 segundos de execucao da erro, utilizando esse metodo nesse local, a cada iteracao esse tempo eh zerado
	$empresa = $empresas[$i];
	
	
	$href = explode(" ", $empresa);//quebra nos espacos em branco, para pegar cada parte
	$site = explode("\"", $href[1]);//href[1] eh a parte href="/3m", sendo /3m o complemento do site para pegar a logo dessa empresa
	
	$empresaSemEspacos = trim($empresa);//eliminar espacos, tabulacoes... no inicio e final da string
	$linhaTitle = explode("title=", $empresaSemEspacos);//nessa parte title fica o nome de pregao da empresa
	$title = explode("\"",$linhaTitle[1]);//o elemento $linhaTitle[1] tem tudo depois de title=", como o nome da empresa so vai ate a proxima aspa, entao quebra-se a string na aspa
	$nomeEmpresa = $title[1];//title[1] = nome de pregao da empresa
	


	$siteImagem = file_get_contents('http://www.infomoney.com.br/' . $site[1]);//site da empresa que contem a logo dela

	$figColor = explode("figColor", $siteImagem);//id da imagem da logomarca eh figColor
	$codigoImagem = explode("<", $figColor[1]);
	$quebraLink = explode("src=", $codigoImagem[1]);//depois do src= eh o complemento do link da logomarca
	$linkImagem = trim($quebraLink[1]);
	$url = substr($linkImagem, 1, -4);//pega a partir do segundo caractere até o quarto ultimo

	$urlInicio = "http://www.infomoney.com.br/";
	file_put_contents("iconesSemEspacos//" . utf8_decode( $nomeEmpresa ) . ".jpg", file_get_contents($urlInicio . $url));//baixa o arquivo da pagina(parametro 2) no caminho e com o nome do parametro 1
}

?>

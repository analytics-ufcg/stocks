<?php 
$sitePrincipal = file_get_contents('http://www.infomoney.com.br/mercados/empresas-bovespa');
$padrao = "<div class=\"por-setor ordem";
$quebraSite = split($padrao, $sitePrincipal);
$codigoParteIcones = $quebraSite[2];
$empresas = split("<li>", $codigoParteIcones);//elemento 0 nao eh empresa, eliminar


for($i = 1; $i <= count($empresas); $i++){
	set_time_limit ( 0 );
	$empresa = $empresas[$i];
	
	
	$href = split(" ", $empresa);//não ta certo quebra por espaço, tem empresas com nome composto.
	$site = split("\"", $href[1]);
	echo $site[1];//pegando certo o complemento do site
	
	$empresaSemEspacos = trim($empresa);
	$linhaTitle = split("title=", $empresaSemEspacos);
	$title = split("\"",$linhaTitle[1]);
	$nomeEmpresa = $title[1];
	


	$siteImagem = file_get_contents('http://www.infomoney.com.br/' . $site[1]);

	$f = split("figColor", $siteImagem);
	$d = split("<", $f[1]);
	$e = split("src=", $d[1]);
	$linkImagem = trim($e[1]);
	$url = substr($linkImagem, 1, -4);

	$urlInicio = "http://www.infomoney.com.br/";
	file_put_contents("iconesSemEspacos//" . utf8_decode( $nomeEmpresa ) . ".jpg", file_get_contents($urlInicio . $url));
}

?>
<?php
/*
 * > Sprint 1 - US 2 > Danilo Gomes > Codigo para pegar as logomarcas das empresas que estão na bolsa de valores no site infomoney > Tem como saida as imagens das logomarcas das empresas que existem no site no formato .jpg, com o nome do arquivo sendo o nome de pregão da empresa
 */

function get_http_response_code($domain1) {
	$headers = get_headers($domain1);
	return substr($headers[0], 9, 3);
}


$url = 'http://www.infomoney.com.br/mercados/empresas-bovespa';
$html = file_get_contents ( 'http://www.infomoney.com.br/mercados/empresas-bovespa' );

$dom = new DOMDocument ();
$dom->loadHTML ( $html );

$xpath = new DomXPath ( $dom );
$dom = $xpath->query ( '//div[@class="por-setor ordem-alfa"]' )->item ( 0 );

$links = $dom->getElementsByTagName ( 'a' );

foreach ( $links as $link ) {
	echo $link->getAttribute ( 'href' ) . '--' . $link->getAttribute ( 'title' );
	
	$href = 'http://www.infomoney.com.br/' . $link->getAttribute ( 'href' );
	
	if (get_http_response_code ( $href ) == 200) {
		$html = file_get_contents ( $href );
		
		$dom = new DOMDocument ();
		$dom->loadHTML ( $html );
		
		$img = $dom->getElementByID ( 'imgLogoCompany' );
		
		$img_filename = $img->getAttribute ( 'title' ) . '.jpg';
		if(!file_exists($img_filename)){
			$img_src = $img->getAttribute ( 'src' );
			if (! file_exists ( 'icons' )) {
				mkdir ( 'icons', 0777, true );
			}
			file_put_contents ( "icons/" . $img_filename, file_get_contents ( 'http://www.infomoney.com.br/' . $img_src ) );
		}
	} else {
		echo "error downloading: " . $href;
	}
}
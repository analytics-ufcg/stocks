<?php

set_time_limit (0); 
ignore_user_abort(true);

print("============= Coletor de Links de Noticias do Estadão por Empresa =============\n\n");

$query_string = "petrobras";
$nome_pregao = "PETROBRAS";
$cnpj = '33000167000101';

$links_emp_csv_filename = "data/news/links_folha_" . str_replace(' ', '_', strtolower($nome_pregao)) . ".csv";
$links_emp_csv_file = fopen($links_emp_csv_filename, "w");

# CSV Header: Fonte, Sub-Fonte, CNPJ, Data, Titulo, Link
$links_array = array('Folha.com.br', 'Economia & Negócios', $cnpj, 'NA', 'NA', 'NA');


printf("Empresa: %s (busca: %s)\n", $nome_pregao, $query_string);
//,"2003","2004","2005","2006","2007","2008","2009","2010","2011","2012","2013"
//,"04","05","06","07","08","09","10","11","12"
$year_list = array("2002","2003","2004","2005","2006","2007","2008","2009","2010","2011","2012","2013");
$month_list = array("01","02","03","04","05","06","07","08","09","10","11","12");
foreach ($year_list as $year) {
	for($month=0; $month < count($month_list) - 1; $month++) {
		
		$complemento_url = $query_string."&site=online%2Fdinheiro&sd=02%2F".$month_list[$month]
		."%2F".$year."&ed=01%2F".$month_list[$month + 1]."%2F".$year;

		print("DATA CONSULTA: 02/".$month_list[$month]."/".$year." - 01/".$month_list[$month + 1]."/".$year);
		
		# Create the empresa links file

		$result_set = get_result_set(1,$complemento_url);
		//echo $result_set;
		$list_links = get_list_string_with_link_title_date($result_set);
		//echo count($list_links)."--";
		$number_links = get_number_pages($result_set);
		$number_links = (int) str_replace(".", "", $number_links);
		
		printf("  Total de Links: %d\n", $number_links);
		//valido os indices de 1 a 3

		for($i=0;$i < count($list_links);$i++){

			$hifen_position = strrpos($list_links[$i], " - ");
			$date = substr($list_links[$i],$hifen_position + 3, $hifen_position + 8);
			list ($day, $month, $year) = split("/", $date);
			
			$link_title = preg_split('/">/', substr($list_links[$i], 0, $hifen_position));
			$link = $link_title[0];
			$title = str_replace('Folha Online - ', '', str_replace('"', '\"', $link_title[1]));

			//$title = preg_replace("/[^a-zA-Z0-9\síóáúôûâêÁÉÍÓÚÂÊÎÔÛãõÃÕÇç,$:;()?!.-]/", "", $title);
			//$title = preg_replace('//', "", $title);
			//$title = preg_replace('/</b>/', "", $title);

			//valido indice 1
			$links_array[3] =  "$year-$month-$day";
			$links_array[4] =  $title;
			$links_array[5] =  $link;
			fputcsv($links_emp_csv_file, $links_array, ',', '"');			
		}

		$iterator_after_first_page = 26;
		while($iterator_after_first_page <= $number_links){

			printf("    Links coletados: %d/%d\n", $iterator_after_first_page, $number_links);
			
			$result_set = get_result_set($iterator_after_first_page,$complemento_url);
			$list_links = get_list_string_with_link_title_date($result_set);
			

			//valido os indices de 1 a 3
			for($i=0;$i < count($list_links);$i++){
				
				$hifen_position = strrpos($list_links[$i], " - ");
				$date = substr($list_links[$i], $hifen_position + 3, $hifen_position + 8);
				list ($day, $month, $year) = split("/", $date);
				$link_title = preg_split('/">/', substr($list_links[$i], 0,$hifen_position));
				$links_array[3] =  "$year-$month-$day";
				$link = $link_title[0];
				$title = str_replace('Folha Online - ', '', str_replace('"', '\"', $link_title[1]));
				//$title = preg_replace('/"/', "", $title);
				//$title = preg_replace("/[^a-zA-Z0-9\síóáúôûâêÁÉÍÓÚÂÊÎÔÛ$:;()?!.-]/", "", $title);
				//$title = preg_replace('/<b>/', "", $title);
				//$title = preg_replace('/</b>/', "", $title);

				//valido indice 1
				$links_array[4] =  $title;
				$links_array[5] =  $link;
				fputcsv($links_emp_csv_file, $links_array, ',', '"');
				
			}
			$iterator_after_first_page +=  25;
			sleep(rand(1, 3));
		}
	}
}
fclose($links_emp_csv_file);


function get_between($input, $start, $end) 
{ 
	$list = array();
	while(strpos($input, $start) !== false && strpos($input, $end) !== false){
		$substr = substr($input, strlen($start)+strpos($input, $start), (strlen($input) - strpos($input, $end))*(-1));
		$input = substr($input, (strlen($end) + strpos($input, $end)),strlen($input)); 
		array_push($list, $substr);

	}

	return $list;
} 

function get_result_set($number_page,$complemento_url){
	$page_news_url = "http://search.folha.com.br/search?q=".$complemento_url."&sr=".$number_page;
	$html_page = file_get_contents($page_news_url);
	$result = get_between($html_page,'<!--RESULTSET-->','<!--/RESULTSET-->');
	
	return $result[0];
	
}

function get_number_pages($result_set){
	$pages_number = get_between($result_set,'resultados <span>(',')</span>');
	$pages_number = preg_split('/de/',$pages_number[0]);
	
	return $pages_number[1];
}

function get_list_links_result_set($result_set){
	$list_results = get_between($result_set,'<span class="url">','</span>');
	return $list_results;
}

function get_list_string_with_link_title_date($result_set){
	$result = get_between($result_set,'<a href="',"</a><br>");
	return $result;
}


?>
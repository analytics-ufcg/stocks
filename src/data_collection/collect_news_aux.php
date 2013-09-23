<?php

# Create the COMMON data directory
$news_dir = 'data/news';
if (!file_exists($news_dir)) {
	mkdir($news_dir, 0777, true);
}

function read_text_between($text, $start_tag, $end_tag){
	$starts_at = strpos($text, $start_tag) + strlen($start_tag);
	$ends_at = strpos($text, $end_tag, $starts_at);
	return substr($text, $starts_at, $ends_at - $starts_at);
}

function match_all_between($text, $start_tag, $end_tag){
	$delimiter = '#';
	$regex = $delimiter . preg_quote($start_tag, $delimiter) 
	. '(.*?)' 
	. preg_quote($end_tag, $delimiter) 
	. $delimiter 
	. 's';
	preg_match_all($regex, $text, $result_array);
	return $result_array;
}

function collect_estadao($news_dir, $nome_pregao, $cnpj, $query_string){

	# Define the complete ESTADAO search URL with the query string
	$emp_search_url = str_replace(" ", "%20", "http://economia.estadao.com.br/busca/$query_string");

	# Create the empresa links file
	$links_emp_csv_filename = "$news_dir/links_estadao_" . str_replace(' ', '_', strtolower($nome_pregao)) . ".csv";

	# Open the file
	$links_emp_csv_file = fopen($links_emp_csv_filename, "w");

	# CSV Header: Fonte, Sub-Fonte, CNPJ, Data, Titulo, Link
	$links_row = array('Estadão.com.br', 'Economia & Negócios', $cnpj, 'NA', 'NA', 'NA');

	$html_content = file_get_contents($emp_search_url);
	// print_r($html_content);
	// $html_content = file_get_contents('./file.txt');

	# Get the number of links
	$number_links = read_text_between($html_content, 'encontrados <em>', '</em> registros para');
	printf("  Total de Links: %d\n", $number_links);

	$count_links_retrieved = 0;
	$count_pages = 1;
	do{
		# Select the desired part of the page
		$html_content = read_text_between($html_content, '<div class="topoPagBusca">', '<div class="c3">');

		# Match the html text with the: NEWS_TITLE, DATE AND LINK
		$all_links_data = match_all_between($html_content, '<p class="listaNoticias_data">', 'class="listaNoticias_chamada">');
		# Select the complete match (with leading and trailing strings)
		$all_links_data = $all_links_data[0]; 

		for ($i = 0; $i < count($all_links_data); $i++){
			# Date
			$date_news = read_text_between($all_links_data[$i], '<p class="listaNoticias_data">', '</p>');
			list ($day, $month, $year) = split("/", $date_news);
			$links_row[3] =  "$year-$month-$day";

			# Title
			$title_news = read_text_between($all_links_data[$i], 'class="listaNoticias_titulo" title="', '">');
			$links_row[4] = str_replace('"', '\"', $title_news);
			# Link
			$links_row[5] = read_text_between($all_links_data[$i], '<a href="', '">');

			# STORE THEM IN THE links_[empresa] CSV FILE
			fputcsv($links_emp_csv_file, $links_row, ',', '"');

		}
		# Increment the retrieved links counter
		$count_links_retrieved += count($all_links_data);
		
		printf("    Links coletados: %d/%d\n", $count_links_retrieved, $number_links);
		
		if ($count_links_retrieved < $number_links){
			# Wait some seconds (from 1 to 5 secs randomly)
			sleep(rand(1, 3));

			# Get the html file of the next page (only adds a /2 in the end of the URL)
			$count_pages++;
			$html_content = file_get_contents($emp_search_url . ";/$count_pages");
		}else{
			break;
		}

	}while (true);

	# Close the links CSV file
	fclose($links_emp_csv_file);

}

function collect_folha_sao_paulo($news_dir, $nome_pregao, $cnpj, $query_string){

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


	# Create the empresa links file
	$links_emp_csv_filename = "$news_dir/links_folha_" . str_replace(' ', '_', strtolower($nome_pregao)) . ".csv";

	# Open the file
	$links_emp_csv_file = fopen($links_emp_csv_filename, "w");

	# CSV Header: Fonte, Sub-Fonte, CNPJ, Data, Titulo, Link
	$links_array = array('Folha de S.Paulo', 'Mercado', $cnpj, 'NA', 'NA', 'NA');

	
	$year_list = range(2002, 2013);
	$month_list = range(01, 12);
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

}

?>

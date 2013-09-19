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

function collect_estadao($news_dir, $nome_pregao, $cnpj, $emp_search_url){

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

function collect_folha_sao_paulo($news_dir, $nome_pregao, $cnpj, $emp_search_url){
	# TODO
}

?>
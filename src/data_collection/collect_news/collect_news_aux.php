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

		# Match all the html text with the: NEWS_TITLE, DATE AND LINK
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
			sleep(rand(1, 2));

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

	# Create the empresa links file
	$links_emp_csv_filename = "$news_dir/links_folha_" . str_replace(' ', '_', strtolower($nome_pregao)) . ".csv";

	# Open the file
	$links_emp_csv_file = fopen($links_emp_csv_filename, "w");

	# CSV Header: Fonte, Sub-Fonte, CNPJ, Data, Titulo, Link
	$links_row = array('Folha de S.Paulo', 'Mercado e Dinheiro', $cnpj, 'NA', 'NA', 'NA');

	$initial_date = '2000-01-01';
	$final_date = date( "Y-m-d"); # TODAY

	$month_steps = 3;

	$curr_start_date = $initial_date;
	$curr_end_date = $initial_date;
	
	$total_links = 0;	
	$last_date = $curr_start_date;
	$last_year = '2000';

	while(true){
		if($curr_end_date >= $final_date){
			break;
		}

		$next_date = date( "Y-m-d", strtotime("$curr_start_date +$month_steps month"));
		if ($next_date >= $final_date){
			$curr_end_date = $final_date; # Last iteration
		}else {
			$curr_end_date = $next_date; # Update to the next 3 months
		}
		
		// echo $curr_start_date, "  ", $curr_end_date, "\n";
		list($year, $month, $day) = split('-', $curr_start_date);
		$start_date = "$day-$month-$year";
		list($year, $month, $day) = split('-', $curr_end_date);
		$end_date = "$day-$month-$year";

		print("Date Interval: $start_date - $end_date\n");

		# Create the empresa links file
		$full_url = 'http://search.folha.com.br/search?q=' . str_replace(" ", "%20", $query_string) . 
					'&site=online%2Fdinheiro&sd=' . str_replace('-', '%2F', $start_date) .
					'&ed=' . str_replace('-', '%2F', $end_date);
		// echo $full_url, "\n" ;

		# Update the next start_date
		$curr_start_date = date( "Y-m-d", strtotime("$curr_end_date +1 day"));

		$html_content = utf8_encode(file_get_contents($full_url));
		
		// $html_content = file_get_contents('file.txt');
		// echo $html_content;

		if (strstr($html_content, 'Nenhum resultado de busca')){
			printf("  No link found!\n");
			continue;
		}
		
		# Select the desired part of the page
		$html_content = read_text_between($html_content, '<!--RESULTSET-->', '<!--/RESULTSET-->');

		# Get the number of links
		$number_links = split(' de ', read_text_between($html_content, 
			'<h2 class="localSearchTarja">resultados <span>(', ')</span></h2>'));
		$number_links = $number_links[1];
		printf("  Number of Links: %d\n", $number_links);
		
		$count_links_retrieved = 0;
		
		do{

			# Match all the html text with the: NEWS_TITLE, DATE AND LINK
			$all_links_data = match_all_between($html_content, '<a href="','</a><br>');
			# Select the complete match (with leading and trailing strings)
			$all_links_data = $all_links_data[0]; 
			// print_r($all_links_data);
			
			for ($i = 0; $i < count($all_links_data); $i++){

				$hyphen = ' - ';

				# Date
				// echo $all_links_data[$i], "\n";
				$last_hyphen = strrpos($all_links_data[$i], $hyphen);
				$date_news = str_replace('</a><br>', '', substr($all_links_data[$i], $last_hyphen + strlen($hyphen)));
				
				if (strlen($date_news) < 10){
					# Error condition: Non existing date. So we repeat the last date retrieved.
					$links_row[3] = $last_date;
				}else{
					list ($day, $month, $year) = split("/", $date_news);

					if(strlen($year) < 4){
						# Error condition: Year with 3 character only. So we repeat the last year retrieved.
						$year = $last_year;
					}

					$links_row[3] =  "$year-$month-$day";
					
					# Persist the last date and year to be used in the next error conditions
					$last_date = $links_row[3];
					$last_year = $year;
				}
				// echo $links_row[3], "\n";

				# Title
				$first_hyphen = strpos($all_links_data[$i], $hyphen);
				$second_hyphen = strpos($all_links_data[$i], $hyphen, $first_hyphen + strlen($hyphen));
				$title_news = substr($all_links_data[$i], $second_hyphen + strlen($hyphen));
				$last_hyphen_title = strrpos($title_news, $hyphen);
				$title_news = substr($title_news, 0, (strlen($title_news) - $last_hyphen_title) * -1);
				$title_news = str_replace('</b>', '', str_replace('<b>', '', $title_news));
				$links_row[4] = str_replace('"', '\"', $title_news);
				// echo $links_row[4], "\n";

				# Link
				$links_row[5] = read_text_between($all_links_data[$i], '<a href="', '">');
				// echo $links_row[5], "\n";

				# Sub-Fonte
				$sub_fonte = read_text_between($all_links_data[$i], $hyphen, $hyphen);
				$links_row[1] = $sub_fonte;
				// echo $links_row[1], "\n\n";

				# STORE THEM IN THE links_[empresa] CSV FILE
				fputcsv($links_emp_csv_file, $links_row, ',', '"');
			}


			# Increment the retrieved links counter
			$count_links_retrieved += count($all_links_data);
			
			printf("    Links coletados: %d/%d\n", $count_links_retrieved, $number_links);
			
			if ($count_links_retrieved < $number_links){
				# Wait some seconds (from 1 to 5 secs randomly)
				sleep(rand(1, 2));

				# Get the html file of the next page (only add 1 to the links retrieved)				
				$html_content = file_get_contents($full_url . '&sr=' . ($count_links_retrieved + 1));
				# Select the desired part of the page
				$html_content = read_text_between($html_content, '<!--RESULTSET-->', '<!--/RESULTSET-->');
			}else{
				break;
			}

		}while(true);
		
		$total_links += $number_links;
		// echo $total_links, "\n";
	}
	printf("Total of Retrieved Links: %d\n", $total_links);

	fclose($links_emp_csv_file);

}

?>

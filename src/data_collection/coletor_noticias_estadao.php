<?php

include 'coletor_noticias_aux.php';

# Define the ESTADAO search URL
$search_url = 'http://economia.estadao.com.br/busca/';

# =============================================================================
# QUERY by EMPRESA
# =============================================================================

print("============= Coletor de Links de Noticias do Estadão por Empresa =============\n\n");

# TODO then: Read the EMPRESA queries from the .csv file and iteratively collect the
# data by the query_strings

# For instance we define a unique EMPRESA: PETROBRAS and do not filter the interval
// "query_string","query_start_date","query_end_date","nome_empresa","nome_pregao","cnpj","setor","sub_setor","segmento"
// "petroleo brasileiro petrobras",1995-03-27,2013-07-05,"PETROLEO BRASILEIRO S.A. PETROBRAS","PETROBRAS","33000167000101","Petroleo. Gas e Biocombustiveis","Petroleo. Gas e Biocombustiveis","Exploracao e/ou Refino"
$nome_pregao = "PETROBRAS";
$cnpj = '33000167000101';
$query_string = 'petrobras';

printf("Empresa: %s (busca: %s)\n", $nome_pregao, $query_string);

# -----------------------------------------------------------------------------
# LINKS RETRIEVAL
# -----------------------------------------------------------------------------

# Create the empresa links file
$links_emp_csv_filename = "$news_dir/links_estadao_" . str_replace(' ', '_', strtolower($nome_pregao)) . ".csv";
$links_emp_csv_file = fopen($links_emp_csv_filename, "w");

# CSV Header: Fonte, Sub-Fonte, CNPJ, Data, Titulo, Link
$links_array = array('Estadão.com.br', 'Economia & Negócios', $cnpj, 'NA', 'NA', 'NA');

$emp_search_url = str_replace(" ", "%20", "$search_url$query_string");

$html_content = file_get_contents($emp_search_url);
// print_r($html_content);
// $html_content = file_get_contents('./file.txt');

# Get the number of links
$number_links = read_text_between($html_content, 'encontrados <em>', '</em> registros para');
printf("  Total de Links: %d\n", $number_links);

$count_links_retrieved = 0;
do{
	# Select the desired part of the page
	$html_content = read_text_between($html_content, '<div class="topoPagBusca">', '<div class="c3">');

	# Match the html text with the: NEWS_TITLE, DATE AND LINK
	$all_links_data = match_all_between($html_content, '<p class="listaNoticias_data">', 'class="listaNoticias_chamada">');
	# Select the complete match (with leading and trailing strings)
	$all_links_data = $all_links_data[0]; 

	for ($i = 0; $i < count($all_links_data); $i++){
		# Date
		$links_array[3] = read_text_between($all_links_data[$i], '<p class="listaNoticias_data">', '</p>');
		# Title
		$links_array[4] = read_text_between($all_links_data[$i], 'class="listaNoticias_titulo" title="', '">');
		# Link
		$links_array[5] = read_text_between($all_links_data[$i], '<a href="', '">');

		# STORE THEM IN THE links_[empresa] CSV FILE
		fputcsv($links_emp_csv_file, $links_array, ',', '"');

	}
	# Increment the retrieved links counter
	$count_links_retrieved += count($all_links_data);

	printf("    Links coletados: %d/%d\n", $count_links_retrieved, $number_links);
	
	if ($count_links_retrieved < $number_links){
		# Wait some seconds (from 1 to 5 secs randomly)
		sleep(rand(1, 3));

		# Get the html file of the next page (only adds a /2 in the end of the URL)
		$html_content = file_get_contents($emp_search_url . '/2');
	}else{
		break;
	}

}while (true);

# Close the links CSV file
fclose($links_emp_csv_file);

?>
<?php

# Create the COMMON data directories
$news_dir = 'data/news';
$estadao_dir = "$news_dir/estadao";

if (!file_exists($news_dir)) {
	mkdir($news_dir, 0777, true);
}

if (!file_exists($estadao_dir)) {
	mkdir($estadao_dir, 0777, true);
}

# Define the ESTADAO search URL
$search_url = 'http://economia.estadao.com.br/busca/';

# =============================================================================
# QUERY by EMPRESA
# =============================================================================

# TODO: Read the EMPRESA queries from the .csv file and iteratively collect the
# data by the query_strings

# For instance we define a unique EMPRESA
$nome_empresa = 'petrobras';
$search_query = 'petrobras';

# -----------------------------------------------------------------------------
# LINKS RETRIEVAL
# -----------------------------------------------------------------------------

# Create the empresa links file
$links_emp_csv_filename = "$estadao_dir/links_" . str_replace(' ', '_', $nome_empresa) . ".csv";
$links_emp_csv_file = fopen($links_emp_csv_filename, "w");

# CSV Header: Fonte, Caderno, Data, Titulo, Link
$links_array = array('Estadao', 'NA', 'NA', 'NA', 'NA');

$emp_search_url = str_replace(" ", "%20", "$search_url$search_query");

# TODO: Change this!!!!!!!!!!!!!!
// $html_content = file_get_contents($emp_search_url);
$html_content = file_get_contents('./file.txt');

# TODO: Get Caderno!

# SELECT THE DESIRED PART OF THE PAGE
$html_content = read_text_between($html_content, '<div class="topoPagBusca">', '<div class="c3">');

# GET THE NUMBER OF PAGES RETURNED
$number_links = read_text_between($html_content, 'encontrados <em>', '</em> registros para');

echo("There are $number_links links.\n");

# GET ALL: NEWS_TITLE, DATE AND LINK
// preg_match_all(pattern, subject, matches)
// <p class="listaNoticias_data">13/09/2013</p>
// <a href="http://economia.estadao.com.br/noticias/negocios-geral,petrobras-aprova-venda-na-colombia-por-us-380-mi,164711,0.htm">
// <h2 class="listaNoticias_titulo" title="Petrobras aprova venda na Colômbia por US$ 380 mi">Petrobras aprova venda na Colômbia por US$ 380 mi</h2></a>
// <a href="http://economia.estadao.com.br/noticias/negocios-geral,petrobras-aprova-venda-na-colombia-por-us-380-mi,164711,0.htm" class="listaNoticias_chamada">

$all_links_data = match_all_between($html_content, '<p class="listaNoticias_data">', 'class="listaNoticias_chamada">');
$all_links_data = $all_links_data[0]; # Select the complete match (with leading and trailing strings)

for ($i = 0; $i < count($all_links_data); $i++){
	$date = read_text_between($all_links_data[$i], '<p class="listaNoticias_data">', '</p>');
	$link = read_text_between($all_links_data[$i], '<a href="', '">');
	$title = read_text_between($all_links_data[$i], 'class="listaNoticias_titulo" title="', '">');

	$links_array[2] = $date;
	$links_array[3] = $title;
	$links_array[4] = $link;
	
	# STORE THEM IN THE links_[empresa] CSV FILE
	fputcsv($links_emp_csv_file, $links_array, ',', '"');
}

# CHECK IF THERE IS MORE LINKS TO RETRIEVE

# CLOSE THE LINKS CSV FILE
fclose($links_emp_csv_file);

# -----------------------------------------------------------------------------
# TEXT RETRIEVAL
# -----------------------------------------------------------------------------

# Create the empresa text data file
$text_emp_csv_file = fopen("$estadao_dir/text_" . str_replace(' ', '_', $nome_empresa) . ".csv", "w");

# CSV Header: Link, Data, Titulo, Corpo
$text_array = array('NA', 'NA', 'NA', 'NA');

# TODO
# READ THE link_[empresa] CSV FILE
# RETRIEVE THE TITLE, DATA AND BODY
# STORE THEM IN THE data_[empresa] CSV FILE

fputcsv($text_emp_csv_file, $text_array, ',', '"');

fclose($text_emp_csv_file);

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


// ?>
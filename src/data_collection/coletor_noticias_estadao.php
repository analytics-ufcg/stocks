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
$links_emp_csv_file = fopen("$estadao_dir/links_" . str_replace(' ', '_', $nome_empresa) . ".csv", "w");

# CSV Header: Fonte, Caderno, Link, Data, Titulo
$links_array = array('Estadao', 'NA', 'NA', 'NA', 'NA', 'NA');


$emp_search_url = str_replace(" ", "%20", "$search_url$search_query");

$html_content = file_get_contents($emp_search_url);

print_r($html_content);

# TODO

# SELECT THE DESIRED PART OF THE PAGE
// FROM: <div class="topoPagBusca">
// TO: <div class="reset">

# GET THE NUMBER OF PAGES RETURNED

// preg_match("Foram encontrados <em>8983</em> registros para", $html_content);


# GET ALL: NEWS_TITLE, DATE AND LINK
// preg_match_all(pattern, subject, matches)
// <p class="listaNoticias_data">13/09/2013</p>
// <a href="http://economia.estadao.com.br/noticias/negocios-geral,petrobras-aprova-venda-na-colombia-por-us-380-mi,164711,0.htm">
// <h2 class="listaNoticias_titulo" title="Petrobras aprova venda na Colômbia por US$ 380 mi">Petrobras aprova venda na Colômbia por US$ 380 mi</h2></a>
// <a href="http://economia.estadao.com.br/noticias/negocios-geral,petrobras-aprova-venda-na-colombia-por-us-380-mi,164711,0.htm" class="listaNoticias_chamada">

# CHECK IF THERE IS MORE LINKS TO RETRIEVE

# STORE THEM IN THE links_[empresa] CSV FILE
fputcsv($links_emp_csv_file, $links_array, ',', '"');


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

?>
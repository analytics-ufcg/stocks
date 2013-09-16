<?php

include 'collect_news_aux.php';

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

# Define the complete query string
$emp_search_url = str_replace(" ", "%20", "$search_url$query_string");

# Run the Collection to the Estadão.com.br
collect_estadao($news_dir, $nome_pregao, $cnpj, $emp_search_url);

# Run the Collection to the Estadão.com.br
collect_folha_sao_paulo($news_dir, $nome_pregao, $cnpj, $emp_search_url);

?>
<?php

include 'collect_news_aux.php';

# =============================================================================
# QUERY by EMPRESA
# =============================================================================

print("============= Coletor de Links de Noticias por Empresa =============\n\n");

# TODO then: Read the EMPRESA queries from the .csv file and iteratively collect the
# data by the query_strings

# For instance we define a unique EMPRESA: PETROBRAS and do not filter the interval
// "query_string","query_start_date","query_end_date","nome_empresa","nome_pregao","cnpj","setor","sub_setor","segmento"
// "petroleo brasileiro petrobras",1995-03-27,2013-07-05,"PETROLEO BRASILEIRO S.A. PETROBRAS","PETROBRAS","33000167000101","Petroleo. Gas e Biocombustiveis","Petroleo. Gas e Biocombustiveis","Exploracao e/ou Refino"
$nome_pregao = "PETROBRAS";
$cnpj = '33000167000101';
$query_string = 'petrobras';

printf("Empresa: %s (busca: %s)\n\n", $nome_pregao, $query_string);

# -----------------------------------------------------------------------------
# LINKS RETRIEVAL
# -----------------------------------------------------------------------------

# Run the Collection to the Estadão.com.br
// printf("===== Estadão.com.br =====\n\n");
// collect_estadao($news_dir, $nome_pregao, $cnpj, $query_string);

# Run the Collection to the Folha de São Paulo
printf("===== Folha de São Paulo =====\n\n");
collect_folha_sao_paulo($news_dir, $nome_pregao, $cnpj, $query_string);

?>
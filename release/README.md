# stocks - Releases

## Sprint 01

### Documentação
 * Critério de Seleção do Banco de Dados.pdf
 * Modelo de Dados - stock_db.png
 * Sketch - Stocks.png

### Código
 * Script de Coleta dos Dados das Empresas (PHP)
	* src/data_collection/coletor_empresas.php
 * Script de Coleta dos Dados das Logomarcas (PHP)
 	* src/data_collection/coletor_logomarcas.php
 * Script de Tradução das Cotacoes para CSV (Python)
	* src/data_collection/traduz_cotacoes_csv.py
 * Script de Criação, Remoção e Carga dos Dados para o Banco de Dados Vertica (SQL)
	* src/db_scripts/create_tables.sql
	* src/db_scripts/drop_tables.sql
	* src/db_scripts/load_data.sql


## Sprint 02

### Documentação
 * US 5 - Como posso visualizar as informações das ações coletadas estruturadas em uma página web?.pdf
 * US 6 - Sketch do Produto.png
 * US 7 - O que é um "solavanco"?.png
 * Novo Modelo de Dados - stock_db.png

### Código
 * Scripts de seleção e detecção de solavancos (R)
	* src/ts_analytics/* 
 * Mudanças em todos os scripts anteriomente gerados

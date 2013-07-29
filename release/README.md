# stocks - Releases

## Sprint 01

### Documentação:
 * Critério de Seleção do Banco de Dados.pdf
 * Modelo de Dados - stock_db.png
 * Sketch - Stocks.png

### Código
 * Script de Coleta dos Dados das Empresas (PHP)
	* src/data_collection/coletor_empresas.php
 * Script de Coleta dos Dados das Logomarcas (PHP)
 	* src/data_collection/coletor_logomarcas.php
	* As figuras estão no servidor do Stocks
 * Script de Tradução das Cotacoes para CSV (Python)
	* src/data_collection/traduz_cotacoes_csv.py
 * Script de Criação, Remoção e Carga dos Dados para o Banco de Dados Vertica (SQL)
	* src/db_scripts/create_tables.sql
	* src/db_scripts/drop_tables.sql
	* src/db_scripts/load_data.sql


## Sprint 02


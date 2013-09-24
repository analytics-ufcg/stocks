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

## Sprint 03

### Documentação
 * US 10 - Sketch do Produto.png
 * US 11 - O que é um "solavanco"?.png
 * US 12 e 13 - Como as empresas e acionistas estão identificados nas mídias sociais-
 * Novo Modelo de Dados - stock_db.png


## Sprint 04

### Documentação
 * Novo Modelo de Dados - Stocks_DB.png
 * US 16 - Figura 1 - Boxplot da Correlação entre Pares de Cotações (Retorno) por Segmento.pdf
 * US 16 - Figura 2 - Heatmap da Correlação entre Cotações (Retorno) por Segmento.pdf
 * US 16 - Empresas de um mesmo segmento oscilam de maneira semelhante dado um período de tempo?

### Código
* Script de análise das cotações por segmento (R)
	* src/ts_analytics/analyse_segments.R
* Desenvolvimento da Interface Web para consulta das métricas por agrupamento, ação/setor/sub-setor/segmento (Javascript, PHP e VerticaSQL)
	* src/web/stocks/*

## Sprint 05

### Documentação
 * US 17 - Novo Modelo de Dados - Stocks_DB.png
 * US 18 - É possível verificar correlações entre notícias e solavancos.pdf
 * US 18 - Séries Temporais com Solavancos e Correlações.pdf
 * US 19 - Atualização do Sketch do produto.pdf

### Código
* Script de análise das cotações por segmento (R)
	* src/ts_analytics/analyse_news_correlation.R
* Desenvolvimento da Interface Web para Exposição das Séries Temporais das Empresas (Javascript, PHP e VerticaSQL)
	* src/web/stocks/*
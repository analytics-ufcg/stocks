<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Stocks</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">

<link href="css/stocks.css" rel="stylesheet">


<!-- CSS -->
<!--link href="css/bootstrap.css" rel="stylesheet"-->
<!--link href="bootstrap2/docs/assets/css/bootstrap-responsive.css" rel="stylesheet"-->

<script type="text/javascript" src="javascript/stocks-main.js"></script>

<script type="text/javascript" src="javascript/jquery-1.10.2.js"></script>
<script type="text/javascript" src="javascript/jquery-ui.js"></script>

<script type="text/javascript" src="javascript/d3.v3.js"></script>

</head>

<body>
	<!-- Part 1: Wrap all page content here -->
	<div id="wrap">

		<!-- Begin page content -->
		<div class="row-fluid offset5">

			<h1>Stocks</h1>

			<div class="span6"></div>
		</div>

		<div class="container-fluid offset1">
			<form action="index.php" method="post">
				<div>
					<input type="radio" name="tipo_de_busca"
						onclick="fill_combo_options('CNPJ', 'CVM', 'ISIN')" id="optionsRadios1"
						value="codigo" checked> Busca por código <input
						type="radio" name="tipo_de_busca"
						onclick="fill_combo_options('Setor', 'Sub-Setor', 'Seguimento')"
						id="optionsRadios2" value="classificacao"> Busca por
					classificacao
				</div>
				<div>
					<select name="combo" id="combo"></select>
					<input name="textArea" id="textArea" type="text">
					<input type="submit" value="ok" onclick="show_main_table()">
				</div>
				<div id="main_table">
<?php
	include("vertica_odbc.php");
?>
				</div>
			</form>
		</div>
</body>
</html>

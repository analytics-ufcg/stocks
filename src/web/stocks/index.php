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
  	
  </head>
  
  <body>
<div>	

<?php 

	function executaConsulta(){
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	$dsn = "StocksDSN";
	$conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");
		
	$resultset = odbc_prepare($conn,"fazConsulta()");
	$sucess = odbc_execute($resultset,array("pegaEntradaTextArea()"));
	$row = odbc_result($resultset,'cep');
	if($row != ""){
		echo $row;
	}else{
		echo "consultou";
	}
	odbc_close($conn);
	}
	# Turn on error reportin
    #require_once 'vertica_odbc.php';
	#$$output = require_once("vertica_odbc.php");
  	#$output = call_page('vertica_odbc.php');
	#echo 'Go for it!<br>';
	 
   
?>

</div>

    <!-- Part 1: Wrap all page content here -->
    <div id="wrap">

      <!-- Begin page content -->
	  <div class="row-fluid offset5">
		
		<h1>Stocks</h1>
		
		<div class="span6"></div>
	  </div>
	  
	  <div class="container-fluid offset1">
<form action="index.php" method="post">
	  <div class= "row-fluid">
	  <div class="span3">
        <label class="radio inline">
			<input type="radio" name="optionsRadios" onclick  = "addCombo1('CNPJ', 'CVM', 'ISIN')" id="optionsRadios1" value="option1" checked>
				Codigos
		</label>
		
		</div>
		<div class="span3 ">
		<label class="radio inline">
			<input type="radio" name="optionsRadios" onclick  = "addCombo1('Setor', 'Sub-Setor', 'Seguimento')" id="optionsRadios2" value="option2">
				Classificacao setorial
		</label>
		</div>
		<div class="span6"></div>
		<div class="span6"></div>
	  </div>
      
		<div class="row-fluid">
			<fieldset>
				
				Combobox: <select name="combo" id="combo"></select>
			</fieldset>
			<div class = "span4">
			<div class="input-append">
			<input class="span8" name="textArea" id="textArea" type="text">
                        <input type = "submit" value= "ok"  onclick="mudarVisibilidadeDivTable()">
			
			</div>
			</div>
		</div>
		
<div id="main_table" style="display:none" >
<?php
	include("vertica_odbc.php");
?>
</div>
</form>
		</div>
  </body>
</html>

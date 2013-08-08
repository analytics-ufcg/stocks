<!DOCTYPE html>
<html lang="en">
  <head>

    <meta charset="utf-8">
    <title>Stocks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- CSS -->
    <!--link href="css/bootstrap.css" rel="stylesheet"-->
    <link href="css/stocks.css" rel="stylesheet">

    <link href="bootstrap2/docs/assets/css/bootstrap-responsive.css" rel="stylesheet">

	
	
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="../assets/ico/favicon.png">
  </head>
  <script>
	
	function mudarVisibilidadeDivTable(){
		var divTable = document.getElementById("exibeTable");
		divTable.style.visibility = 'block';
	
	}	
	function echoConsulta(){
		alert("<?php executaConsulta(); ?>");
	}



	
	function pegaEntradaTextArea()
	{
		var textArea = document.getElementById("textArea");
		textoEntrada = textArea.value;
		return textoEntrada
	}
	
	function pegaEntradaCombo()
	 {
	 var combo = document.getElementById("combo");
	  var x = combo.selectedIndex;
	  nomeSelecionado = combo.options[x].text;
	if(nomeSelecionado == "ISIN"){
		nomeSelecionado = "cod_isin";
	}else if(nomeSelecionado == "CVM"){
		nomelecionado = "cod_cvm";
	}else if(nomeSelecionado == "CNPJ"){
		nomeSelecionado = "cnpj";
	}else if(nomeSelecionado == "Setor"){
		nomeSelecionado = "setor";
	}else if(nomeSelecionado == "Sub-Setor"){
		nomeSelecionado = "sub_setor";
	}else if(nomeSelecionado == "Segmento"){
		nomeSelecionado = "segmento";
	}
	  return nomeSelecionado
	}
	
	function fazConsulta(){
		var consulta = "select * from Empresa where ".concat(pegaEntradaCombo(), " = ?" , ";");
		alert(consulta);
	return consulta;
	}
	
	function removeOptions(selectbox)
{
    var i;
    for(i=selectbox.options.length-1;i>=0;i--)
    {
        selectbox.remove(i);
    }
}
  
	function addCombo1(nome1, nome2, nome3) {
	
    
	removeOptions(document.getElementById("combo"));
	var textb = 1;
    var combo = document.getElementById("combo");
	//combo.empty();
    var option1 = document.createElement("option");
    option1.text = nome1;
	var option2 = document.createElement("option");
    option2.text = nome2;
	var option3 = document.createElement("option");
    option3.text = nome3;
	
    //option.value = textb.value;
    try {
		combo.add(option1, null); //Standard
		combo.add(option2, null);
		combo.add(option3, null);
    }catch(error) {
        combo.add(option1); // IE only
    }
	
    textb.value = "";
}
  </script>
  
  <script>
	function addCombo2() {
	
    
	removeOptions(document.getElementById("combo"));
	var textb = 1;
    var combo = document.getElementById("combo");
	//combo.empty();
    var option1 = document.createElement("option");
    option1.text = "Setor";
	var option2 = document.createElement("option");
    option2.text = "Sub-Setor";
	var option3 = document.createElement("option");
    option3.text = "Seguinte";
	
    //option.value = textb.value;
    try {
		combo.add(option1, null); //Standard
		combo.add(option2, null);
		combo.add(option3, null);
    }catch(error) {
        combo.add(option1); // IE only
    }
	
    textb.value = "";
}
  </script>
  
  
  
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

</div>
	
	
    <!-- Part 1: Wrap all page content here -->
    <div id="wrap">

      <!-- Begin page content -->
	  <div class="row-fluid offset5">
		
		<h1>Stocks</h1>
		
		<div class="span6"></div>
	  </div>
	  
	  <div class="container-fluid offset1">
<form action="pagina.php" method="post">
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
		
<div id="exibeTable" style="visibility:block" >
<?php
	include("vertica_odbc.php");
?>
</div>
</form>
		</div>
      
	  

      <div id="push"></div>
    </div>
	
	

	<div class="span6">
          
	</div>
    <div id="footer">
      <div class="container">
        <p class="muted credit">Example courtesy <a href="http://martinbean.co.uk">Martin Bean</a> and <a href="http://ryanfait.com/sticky-footer/">Ryan Fait</a>.</p>
      </div>
    </div>

	

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap-transition.js"></script>
    <script src="../assets/js/bootstrap-alert.js"></script>
    <script src="../assets/js/bootstrap-modal.js"></script>
    <script src="../assets/js/bootstrap-dropdown.js"></script>
    <script src="../assets/js/bootstrap-scrollspy.js"></script>
    <script src="../assets/js/bootstrap-tab.js"></script>
    <script src="../assets/js/bootstrap-tooltip.js"></script>
    <script src="../assets/js/bootstrap-popover.js"></script>
    <script src="../assets/js/bootstrap-button.js"></script>
    <script src="../assets/js/bootstrap-collapse.js"></script>
    <script src="../assets/js/bootstrap-carousel.js"></script>
    <script src="../assets/js/bootstrap-typeahead.js"></script>

  </body>
</html>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Stocks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- CSS -->
    <link href="../../library/bootstrap2/docs/assets/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">

      /* Sticky footer styles
      -------------------------------------------------- */

      html,
      body {
        height: 100%;
        /* The html and body elements cannot have any padding or margin. */
      }

      /* Wrapper for page content to push down footer */
      #wrap {
        min-height: 100%;
        height: auto !important;
        height: 100%;
        /* Negative indent footer by it's height */
        margin: 0 auto -60px;
      }

      /* Set the fixed height of the footer here */
      #push,
      #footer {
        height: 60px;
      }
      #footer {
        background-color: #f5f5f5;
      }

      /* Lastly, apply responsive CSS fixes as necessary */
      @media (max-width: 767px) {
        #footer {
          margin-left: -20px;
          margin-right: -20px;
          padding-left: 20px;
          padding-right: 20px;
        }
      }



      /* Custom page CSS
      -------------------------------------------------- */
      /* Not required for template or sticky footer method. */

      .container {
        width: auto;
        max-width: 680px;
      }
      .container .credit {
        margin: 20px 0;
      }

    </style>
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
	
	function pegarEntrada()
	{
		var textArea = document.getElementById("textArea");
		textoEntrada = textArea.value;
		return textoEntrada
	}
	
	function vai()
	 {
	 var combo = document.getElementById("combo");
	  var x = combo.selectedIndex;
	  nomeSelecionado = combo.options[x].text;
	  return nomeSelecionado
	}
	
	function sqlCode(){
		var consulta = "select * from Empresa where ".concat(vai(), " = ", pegarEntrada(), ";");
		alert(consulta);
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
	
	

<?php 
	# Turn on error reportin
   error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	//# Connect to the Database
    $dsn = "StocksDSN";
    $conn = odbc_connect($dsn,'','') or die ("CONNECTION ERROR\n");
	$nome_empresa = "BCO SOFISA S.A.";
	$resulset = odbc_prepare($conn,"SELECT * FROM EMPRESA WHERE nome_empresa = ?");
	$sucess = odbc_execute($resultset,array($nome_empresa));
		
	while($row = odbc_fetch_array($resultset)){
		print_r( $row);
	}
	$d ="sdfsdgsdf";
		
	odbc_close($conn);
   
   
?>
	
	
    <!-- Part 1: Wrap all page content here -->
    <div id="wrap">

      <!-- Begin page content -->
	  <div class="row-fluid offset5">
		
		<h1>Stocks</h1>
		<div class="span6"></div>
	  </div>
	  
	  <div class="container-fluid offset1">
	  <div class= "row-fluid">
	  <div class="span3">
        <label class="radio inline">
			<input type="radio" name="optionsRadios" onclick  = "addCombo1('CNPJ', 'CVM', 'ISIN')" id="optionsRadios1" value="option1" checked>
				Codigos
		</label>
		
		</div>
		<div class="span3 ">
		<label class="radio inline">
			<input type="radio" name="optionsRadios" onclick  = "addCombo2('Setor', 'Sub-Setor', 'Seguimento')" id="optionsRadios2" value="option2">
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
			<input class="span8" id="textArea" type="text">
			<button class="btn" onclick = "sqlCode()" type="button">Ok</button>
			</div>
			</div>
		</div>
		
			<div class="bs-docs-example">
<table class="table table-striped">
<thead>
<tr>

<th>Logomarca</th>
<th>Dados da empresa</th>
<th>Contato</th>
<th>Classificacao</th>
</tr>
</thead>
<tbody>
<tr>

<td>Mark</td>
<td>Otto</td>
<td>@mdo</td>
<td>coluna4</td>
</tr>
<tr>

<td>Jacob</td>
<td>Thornton</td>
<td>@man</td>
</tr>
<tr>

<td>Larry</td>
<td>the Bird</td>
<td>@twitter</td>
</tr>
<tr>

<td>L</td>
<td>the Bird</td>
<td>@twitter</td>
</tr>
</tbody>
</table>
</div>
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
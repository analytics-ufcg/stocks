function mudarVisibilidadeDivTable() {
	var divTable = document.getElementById("main_table");
	divTable.style.display = '';

}
function echoConsulta() {
	alert("<?php executaConsulta(); ?>");
}

function pegaEntradaTextArea() {
	var textArea = document.getElementById("textArea");
	textoEntrada = textArea.value;
	return textoEntrada
}

function pegaEntradaCombo() {
	var combo = document.getElementById("combo");
	var x = combo.selectedIndex;
	nomeSelecionado = combo.options[x].text;
	if (nomeSelecionado == "ISIN") {
		nomeSelecionado = "cod_isin";
	} else if (nomeSelecionado == "CVM") {
		nomelecionado = "cod_cvm";
	} else if (nomeSelecionado == "CNPJ") {
		nomeSelecionado = "cnpj";
	} else if (nomeSelecionado == "Setor") {
		nomeSelecionado = "setor";
	} else if (nomeSelecionado == "Sub-Setor") {
		nomeSelecionado = "sub_setor";
	} else if (nomeSelecionado == "Segmento") {
		nomeSelecionado = "segmento";
	}
	return nomeSelecionado
}

function fazConsulta() {
	var consulta = "select * from Empresa where ".concat(pegaEntradaCombo(),
			" = ?", ";");
	alert(consulta);
	return consulta;
}

function removeOptions(selectbox) {
	var i;
	for (i = selectbox.options.length - 1; i >= 0; i--) {
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
	} catch (error) {
		combo.add(option1); // IE only
	}

	textb.value = "";
}

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
	} catch (error) {
		combo.add(option1); // IE only
	}

	textb.value = "";
}

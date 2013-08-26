/*
	MAIN CONTROLLER METHOD
*/

function main_controller(){

	$("#search_form").submit(function(e){
		// Avoid refreshing the page
		e.preventDefault(); 
		run_search();
	})

	$("#search_type").change(function(){
		value = $("#search_type").val();
		if(value == "Setor" || value == "Sub-Setor" || value == "Segmento"){
			fill_text_area_typeahed(value);
		}
	})
}

/*
	AJAX CALL METHODS
*/
function fill_text_area_typeahed(search_type){
	var call_data = "search_type=" + search_type;
	
	// set_search_typeahed_list(["Algo", "Alguem", "Alguma", "Algoritmo", 
	// 						  "Algorithm", "Alcorao", "Alcalino", "Alambique", "Alo", "Alho", "Alarido"]);
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: call_data,
		success: function(response) {
			set_search_typeahed_list(response.name_list);
		}
	});
	return false;
}

function run_search(){
	$("#go_search").button('loading');
	var call_data = $('#search_form').serialize();
	// show_empresa_table([]);
	// show_empresa_table([{icon_filename : "", nome_empresa: "IGUATEMI EMPRESA DE SHOPPING CENTERS S.A", 
	// 	nome_pregao : "IGUATEMI", cod_negociacao: "IGTA3", cod_cvm: "20494", cnpj: "51218147000193", 
	// 	atividade_principal: "Empresa Full Service No Setor de Shopping Centers. Suas Atividades Englobam A Concepção. O Planejamento. O Desenvolvimento E A Administração de Shopping Centers Regionais E Complexos Imobiliários.", 
	// 	site:"www.iguatemi.com.br", rua:"R Angelina Maffei Vita 200 - 9 Andar", cep:"1455070", cidade: "São Paulo", estado:"SP", telefone:"(11)3137 6872,(11)3137 6872,(11)5029 7780", fax:"(11)3137 7097,(11)3137 7097,(11)5029 3141", 
	// 	nomes:"Cristina Anne Betts", emails:"ri@iguatemi.com.br,investfone@itau-unibanco.com.br", setor:"Financeiro e Outros", sub_setor:"Exploração de Imóveis", segmento:"Exploração de Imóveis"}]);
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: call_data,
		success: function(response) {			
			$("go_search").button('reset');
			show_empresa_table(response.table);
		}
	});
	return false;
}
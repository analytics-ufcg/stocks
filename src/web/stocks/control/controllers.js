
var typeahed_name_list = Array();

/*
	MAIN CONTROLLER METHOD
*/
function main_controller(){

	// TAB BUSCA EMPRESA
	$("#search_form").submit(function(e){
		// Avoid refreshing the page
		e.preventDefault(); 
		run_search();
	});

	$("#search_type").change(function(){
		fill_text_area_typeahed($("#search_type").val());
	});

	$('#text_area').typeahead({
        source : function(){
            return typeahed_name_list;
    }});

	/*
		TAB TOP10
	*/
	$("#top10_form").submit(function(e){
		// Avoid refreshing the page
		e.preventDefault(); 
		run_top10();
	});

    $('#top10_datetimepicker1').datetimepicker({
		language: 'pt-BR',
	  	pickTime: false,
	  	maskInput: true
	});

	$('#top10_datetimepicker2').datetimepicker({
		language: 'pt-BR',
	  	pickTime: false,
	  	maskInput: true
	});
}

/*
	AJAX CALL METHODS
*/
function run_search(){
	$("#go_search").button('loading');
	var call_data = $('#search_form').serialize();

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("#go_search").button('reset');
			// console.log(response);
			show_empresa_table(response.table);
		}
	});
	return false;
}

function fill_text_area_typeahed(search_type){
	if(!(search_type == "Setor" || search_type == "Sub-Setor" || search_type == "Segmento")){
		typeahed_name_list = [];
		return false;
	}
	var call_data = "search_type=" + search_type;
		
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa_col.php',
		async: true,
		data: call_data,
		success: function(response) {		
			typeahed_name_list = response.name_list;
		}
	});
	return false;
}

function run_top10(){
	// $("#go_top10").button('loading');
	var call_data = $('#top10_form').serialize();
	call_data += "&top=10";
	console.log(call_data);

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_top10.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("#go_top10").button('reset');
			console.log(response);
			// show_top10_result(response.table);
		}
	});
	return false;
}
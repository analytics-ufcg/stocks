/*
	MAIN CONTROLLER METHOD
*/
function main_controller(){

	/*
		TAB SEARCH
	*/
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
		TAB TOP
	*/
	$("#top10_form").submit(function(e){
		// Avoid refreshing the page
		e.preventDefault(); 
		if(isValidDate($('#start_date').val(), "inicial") && 
			isValidDate($('#end_date').val(), "final") )
		{
			run_top10();
		}
		
	});


	$("#start_date_wrapper").datepicker('setValue', new Date(2010, 0, 1, 0, 0, 0, 0));
	$("#start_date").click(function() {
		$("start_date_wrapper").datepicker('show');
	});

	$("#end_date_wrapper").datepicker('setValue', new Date(2010, 0, 10, 0, 0, 0, 0));
	$("#end_date").click(function() {
		$("end_date_wrapper").datepicker('show');
	});

	$("#start_date").keypress(function(){
		add_barra_date(this);
	});

	$("#end_date").keypress(function(){
		add_barra_date(this);
	});


	$( "#ts_news_container_top" ).dialog({
		autoOpen: false,
		height: 800,
		width: 1000,
		modal: true
	});

	$( "#ts_news_container_search" ).dialog({
		autoOpen: false,
		height: 800,
		width: 1000,
		modal: true
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
	$("#go_top10").button('loading');
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
	 		// console.log(response);
			show_top10_result(response);
	 	}
	 });
	 return false;
}

function create_time_serie_search(nome_empresa, nome_pregao, cnpj){
	$("#loading_ts_search").show();
	
	// Clean the previous time-serie
	$('#ts_news_container_search #time_serie').html("");
	$("#ts_news_container_search").dialog("option", "title", "Carregando Série Temporal...");
	
	$("#ts_news_container_search").dialog("open");
	call_data = "cnpj=" + cnpj;

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa_ts_by_cnpj.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("#loading_ts_search").hide();	
			show_highchart('ts_news_container_search', nome_pregao, nome_empresa, response);
			show_news([]);
		}
	});

	return false;
}


function create_time_serie_top(nome_empresa, isin){
	$("#loading_ts_top").show();
	
	// Clean the previous time-serie
	$('#ts_news_container_top #time_serie').html("");
	$("#ts_news_container_top").dialog("option", "title", "Carregando Série Temporal...");

	$("#ts_news_container_top").dialog("open");

	call_data = "isin=" + isin;
	console.log(call_data);
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa_ts_by_isin.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("#loading_ts_top").hide();
			show_highchart('ts_news_container_top', nome_empresa, nome_empresa, response);
			show_news([]);
		}
	});
	

	return false;
}

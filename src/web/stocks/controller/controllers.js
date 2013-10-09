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
            return window.typeahed_name_list;
    }});

	window.typeahed_name_list = [];
    
	/*
		TAB TOP
	*/
	$("#top_acoes_form").submit(function(e){
		// Avoid refreshing the page
		e.preventDefault(); 
		if(is_valid_date($('#start_date').val(), "inicial") && 
			is_valid_date($('#end_date').val(), "final") )
		{
			run_top_acoes();
		}
		
	});


	lower_bound = new Date(1993, 1, 1, 0, 0, 0, 0);
	now = new Date();
	upper_bound = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);

	create_date_wrappers(lower_bound, upper_bound);

	$("#start_date_wrapper").datepicker('setValue', new Date(2010, 0, 1, 0, 0, 0, 0));
	$("#start_date").click(function() {
		$("start_date_wrapper").datepicker('show');
	});

	$("#end_date_wrapper").datepicker('setValue', new Date(2010, 0, 10, 0, 0, 0, 0));
	$("#end_date").click(function() {
		$("end_date_wrapper").datepicker('show');
	});

	$("#start_date").keypress(function(){
		add_bar_date(this);
	});

	$("#end_date").keypress(function(){
		add_bar_date(this);
	});


	$( "#ts_news_container_search" ).dialog({
		autoOpen: false,
		height: 800,
		width: 1000,
		modal: true
	});
	$( "#ts_news_container_top" ).dialog({
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
			show_empresa_table(response.table);
		}
	});
	return false;
}

function fill_text_area_typeahed(search_type){
	if(!(search_type == "Setor" || search_type == "Sub-Setor" || search_type == "Segmento")){
		window.typeahed_name_list = [];
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
			window.typeahed_name_list = response.name_list;
		}
	});
	return false;
}

function run_top_acoes(){
	$("#go_top10").button('loading');
	var call_data = $('#top_acoes_form').serialize();

	$.ajax({
	 	type: 'GET',
	 	dataType: 'json',
	 	url: 'model/model_top_acoes.php',
	 	async: true,
	 	data: call_data,
	 	success: function(response) {
	 		$("#go_top10").button('reset');
			show_top_result(response);
	 	}
	 });
	 return false;
}

function create_time_serie_search(nome_empresa, nome_pregao, cnpj){
	$("#ts_news_container_search #loading_ts").show();
	
	// Clean the previous time-serie
	$('#ts_news_container_search #time_serie').html("");
	$("#ts_news_container_search").dialog("option", "title", "Carregando Série Temporal...");
	show_news('ts_news_container_search', [], "");

	// Open the dialog
	$("#ts_news_container_search").dialog("open");
	
	call_data = "cnpj=" + cnpj;
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa_ts_by_cnpj.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("#ts_news_container_search #loading_ts").hide();	

			show_highchart('ts_news_container_search', nome_pregao, nome_empresa, response[0], 
				$('#start_date').val(), $('#end_date').val(), cnpj);
			
			show_news_stock_correlation_pdf(cnpj, response[1]);
		}
	});

	return false;
}

function create_time_serie_top(nome_empresa, isin){
	$("#ts_news_container_top #loading_ts").show();
	
	// Clean the previous time-serie
	$('#ts_news_container_top #time_serie').html("");
	$("#ts_news_container_top").dialog("option", "title", "Carregando Série Temporal...");
	show_news('ts_news_container_top', [], "");
	
	// Open the dialog
	$("#ts_news_container_top").dialog("open");

	call_data = "isin=" + isin;
	
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa_ts_by_isin.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("#ts_news_container_top #loading_ts").hide();	

			show_highchart('ts_news_container_top', nome_empresa, nome_empresa, response, 
							$('#start_date').val(), $('#end_date').val(), isin);

			// show_news_stock_correlation_pdf('', isin);
		}
	});

	return false;
}

function create_timed_news(container_name, query_value, date){
		
	$('#' + container_name + ' #news #folha_sao_paulo').html("");
	$('#' + container_name + ' #news #estadao').html("");

	if(container_name == 'ts_news_container_search'){
		call_data = "query_type=cnpj";
	}else{
		call_data = "query_type=cod_isin";
	}
	
	call_data += "&query_value=" + query_value + "&date=" + date;

	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa_news_by_col_and_date.php',
		async: true,
		data: call_data,
		success: function(response) {
			show_news(container_name, date, response);
		}
	});

	return false;
}

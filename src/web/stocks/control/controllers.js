
var typeahed_name_list = Array();

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

	var lowerBound = new Date(1950, 1, 1, 0, 0, 0, 0);
	var now = new Date();
	var upperBound = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);

	var start_field = $("#start_date_wrapper").datepicker({
		onRender : function(date) {
			return date.valueOf() <= lowerBound.valueOf() || date.valueOf() > upperBound.valueOf() ? 'disabled' : '';
		}
		}).on('changeDate', function(ev) {
		if ( ev.date.valueOf() > end_field.date.valueOf() ){
			var newDate = new Date(ev.date);
			newDate.setDate(newDate.getDate());
			end_field.setValue(newDate);
		}
		//barra(ev);
		
	}).data('datepicker');

	$("#start_date_wrapper").datepicker('setValue', new Date(2010, 0, 1, 0, 0, 0, 0));
	$("#start_date").click(function() {
		$("start_date_wrapper").datepicker('show');
	});



	var end_field = $("#end_date_wrapper").datepicker({
		onRender : function(date) {
			return date.valueOf() < start_field.date.valueOf() || date.valueOf() > upperBound.valueOf() ? 'disabled' : '';
		}
	}).on('changeDate', function(ev) {
		if ( ev.date.valueOf() < start_field.date.valueOf() ){
			var newDate = new Date(ev.date);
			newDate.setDate(newDate.getDate());
			start_field.setValue(newDate);
		}
	}).data('datepicker');

	$("#end_date_wrapper").datepicker('setValue', new Date(2010, 0, 10, 0, 0, 0, 0));
	$("#end_date").click(function() {
		$("end_date_wrapper").datepicker('show');
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
	// console.log(call_data);

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
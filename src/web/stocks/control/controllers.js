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
// TODO: Improve this based on this site: http://tatiyants.com/how-to-use-json-objects-with-twitter-bootstrap-typeahead/
function fill_text_area_typeahed(search_type){
	var call_data = "search_type=" + search_type;
	// console.log(this_data);
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: call_data,
		success: function(response) {
			if (response.name_list.length <= 0){
				console.log("Nada foi encontrado.");
			}
			$('#text_area').typeahead({source: response.list})
		}
	});
	return false;
}

function run_search(){
	$("#go_search").button('loading');
	var call_data = $('#search_form').serialize();
	// console.log(serializedData);
	// show_empresa_table([]);
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: call_data,
		success: function(response) {
			$("go_search").button('reset');

			if (response.table.length > 0){
				show_empresa_table(response.table);
			}else{
				console.log("Nada foi encontrado.");
			}
		}
	});
	return false;
}
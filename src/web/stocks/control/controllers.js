function main_controller(){

	$("#search_form").submit(function(e){
		// Avoid refreshing the page
		e.preventDefault(); 
		run_search();
	})
}

function run_search(){
	$("#go_search").button('loading');
	var serializedData = $('#search_form').serialize();
	// console.log(serializedData);
	// show_empresa_table([]);
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: serializedData,
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
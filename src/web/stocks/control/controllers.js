function main_controller(){

	// TODO: Check if this realy works...
	$(window).keydown(function(event){
    	if(event.keyCode == 13) {
      		event.preventDefault();
      		return false;
    	}
  	});
  	// TODO: Check if this realy works...
	$("#textArea").keydown(function(e) {console.log(e.keyCode); enter_text_area(e)});
	
	// TODO: Check if this realy works...
	// $("#search_radio").buttonset();

	// TODO: Change this after adding the jQueryUI
	$("#go").click(function() {click_go_button()});
	$("#search_radio1").click(function(){fill_combo_options()})
	$("#search_radio2").click(function(){fill_combo_options()})
}

function enter_text_area(e){
	if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
		return click_go_button();
	}	
}
function click_go_button(){
	var serializedData = $('#table_form').serialize();
	
	$.ajax({
		type: 'GET',
		dataType: 'json',
		url: 'model/model_empresa.php',
		async: true,
		data: serializedData,
		success: function(response) {
			if (response.table.length > 0){
				// console.log(response)
				show_empresa_table(response.table);
			}
		}
	});
	return false;
}
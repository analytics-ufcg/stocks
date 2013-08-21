function control_table(){
	
	$('#go').click(function() {
		var serializedData = $('#table_form').serialize();
		console.log(serializedData);
		
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: 'model/model_empresa.php',
			async: true,
			data: serializedData,
			success: function(response) {
				if (response.table.length > 0){
					console.log(response)
					show_empresa_table(response.table);
				}
			}
		});
		return false;
	});
}
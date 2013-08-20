function show_main_table() {
	var divTable = document.getElementById("main_table");
	divTable.className = 'teste';

}
function removeOptions(selectbox) {
	var i;
	for (i = selectbox.options.length - 1; i >= 0; i--) {
		selectbox.remove(i);
	}
}

function fill_combo_options(nome1, nome2, nome3) {

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

/*
  SEARCH - VARIABLE
  */

// var typeahed_name_list = Array();

/*
  TOP - DATE TIME VARIABLES and FUNCTIONS
  */

function create_date_wrappers(lower_bound, upper_bound){
    
    // Start date wrapper
    window.start_field = $("#start_date_wrapper").datepicker({
        onRender : function(date) {
            return date.valueOf() <= lower_bound.valueOf() || date.valueOf() > upper_bound.valueOf() ? 'disabled' : '';
        }
    }).on('changeDate', function(ev) {
        if ( ev.date.valueOf() > end_field.date.valueOf() ){
            var newDate = new Date(ev.date);
            newDate.setDate(newDate.getDate());
            end_field.setValue(newDate);
        }
    }).data('datepicker');

    // End date wrapper
    window.end_field = $("#end_date_wrapper").datepicker({
        onRender : function(date) {
            return date.valueOf() < start_field.date.valueOf() || date.valueOf() > upper_bound.valueOf() ? 'disabled' : '';
        }
    }).on('changeDate', function(ev) {
        if ( ev.date.valueOf() < start_field.date.valueOf() ){
            var newDate = new Date(ev.date);
            newDate.setDate(newDate.getDate());
            start_field.setValue(newDate);
        }
    }).data('datepicker');
}

function add_barra_date(objeto){
    if (objeto.value.length == 2 || objeto.value.length == 5 ){
        objeto.value = objeto.value + "/";
    }
}

function is_valid_date(date_val, error_output) {
    var now = new Date();
    
    var bits = date_val.split('/');
    if(bits.length != 3) {
        alert("Data " + error_output + " invalida");
        return false;
    }
    if(bits[0].length != 2 || bits[1].length != 2 || bits[2].length != 4 || 
        bits[1] > now.getMonth() || bits[2] > now.getFullYear()){
        alert("Data " + error_output + " invalida");
        return false;
    }
    var d = new Date(bits[2], bits[1] - 1, bits[0]);
    ehValido = (d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]));
    if(!ehValido){
        alert("Data " + error_output + " invalida");
    }
    return ehValido;
} 

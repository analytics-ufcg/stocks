function fill_combo_options() {

    if($("#search_radio1")[0].checked){
        option_names = Array('CNPJ', 'CVM', 'ISIN');
    }else{
        option_names = Array('Setor', 'Sub-Setor', 'Segmento');
    }

    $("#combo").empty();
    
    for (var i = 0; i < option_names.length; i++) {
        var opt = document.createElement("option");
        opt.value = option_names[i];
        opt.text = option_names[i];
        $("#combo").append(opt);
    };
}

function fill_initial_options(){
    // $("#search_radio1")[0].checked = true;
    fill_combo_options();
}

function show_empresa_table(table_array){

    // Create the table result as html
    var table = "<table id='empresa_table' border='1'><tr><th>Logomarca</th><th>Dados Da Empresa</th><th>Contato</th><th>Qualificacao</th></tr><tbody><tr>";

    for (var i = 0; i < table_array.length; i++) {
        row = table_array[i];

        table += "<td><img src=" + row['icon_filename'] + "></td><td>Nome Empresa: "
            +row['nome_empresa']+"<br>Nome de Pregao: "+row['nome_pregao']
            +"<br>Codigos de Negociacao <br>"+"<br>Codigo CVM: "+row['cod_cvm']+"<br>CNPJ: "
            +row['cnpj']+"<br>Atividade Principal: "+row['atividade_principal']+"</td><td>"
            +"Site:"+row['site']+"<br>Rua: "+row['rua']+"<br>CEP: "+row['cep']+"<br>Cidade: "
            +row['cidade']+"<br>Telefones: "+row['telefone']+"<br>Fax: "+row['fax']+"<br>Nomes:"
            +row['nomes']+"<br>Emails: "+row['emails']
            +"</td><td>Setor: "+row['setor']+"<br>Subsetor: "+row['sub_setor']+"<br>Segmento: "
            +row['segmento']+"</td></tr>";
    };
    
    $("#main_table").html(table);
}

function show_empresa_table(table_array){

    // Create the table result as html
    var table = "<table id='empresa_table' class='table table-bordered table-hover table-condensed'><tr><th>Logomarca</th><th>Dados Da Empresa</th><th>Contato</th><th>Qualificação</th></tr><tbody><tr>";

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
    
    // Update the central bar with the new table
    $("#central_bar").html(table);
}

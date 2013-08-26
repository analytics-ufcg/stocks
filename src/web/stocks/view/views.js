function show_empresa_table(table_array){

    if (table_array.length <= 0){
        $("#central_bar").html("<em>Nenhuma empresa foi encontrada.</em>");
        return false;
    }

    // Create the table result as html
    var table = "<table id='empresa_table' class='table table-bordered table-condensed'>" 
                + "<thead><tr bgcolor='#f5f5f5'><th align='center'>Logomarca</th><th>Dados Gerais</th><th>Contatos</th><th>Classificação</th></tr><thead><tbody>";

    for (var i = 0; i < table_array.length; i++) {
        row = table_array[i];

        table += 
            // Logomarca only
            "<tr><td rowspan='6'><img src=" + row['icon_filename'] + "></td>" 
            // Dados gerais | Contatos | Classificação
            // Row 1
            + "<td>Empresa: <strong>" + row['nome_empresa'] + "</strong></td>"
            + "<td>Site: " + row['site'] + "</td>"
            + "<td>Setor: " + row['setor'] + "</td></tr>"
            // Row 2
            + "<tr><td>Nome de Pregão: " + row['nome_pregao'] + "</td>"
            + "<td>Endereço: "+row['rua'] + "</td>"
            + "<td>Subsetor: " + row['sub_setor'] + "</td></tr>"
            // Row 3
            + "<tr><td>Códigos<br> - Negociação: " + row['cod_negociacao']
            + "<br> - CVM: " + row['cod_cvm'] 
            + "<br> - CNPJ: " + row['cnpj'] + "</td>"
            + "<td>Cidade: " + row['cidade'] 
            + "<br>Estado: " + row['estado']
            + "<br>CEP: " + row['cep'] + "</td>"
            + "<td rowspan='4'>Segmento: " + row['segmento']+"</td></tr>"
            // Row 4
            + "<tr><td rowspan='3' style='max-width:500px;'>Atividade Principal: "+row['atividade_principal'] + "</td>"
            + "<td>Investidor: " + row['nomes'] + "</td></tr>" 
            // Row 5
            + "<tr><td>Telefones: " + row['telefone'] 
            + "<br>Fax: "+  row['fax'] + "</td></tr>"
            // Row 6
            + "<tr><td>Emails: " + row['emails'] + "</td></tr>";
            
    };
    table += "</tbody></table>";
    
    // Update the central bar with the new table
    $("#central_bar").html(table);
}

function set_search_typeahed_list(name_list){
    if (name_list.length <= 0){
        // console.log("Nada foi encontrado.");
    }

    $('#text_area').typeahead({
        source : name_list
    });
}
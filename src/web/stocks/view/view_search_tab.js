function show_empresa_table(table_array){

    function is_empty(value){
        return (value == "--");
    }

    if (table_array.length <= 0){
        $("#inner_central_bar").html("<em>Nenhuma empresa foi encontrada.</em>");
        return false;
    }

    // Create the table result as html
    var table = "<table id='empresa_table' class='table table-bordered table-condensed'>" 
                + "<thead><tr bgcolor='#f5f5f5'><th align='center'>Logomarca</th><th>Dados Gerais</th><th>Contatos</th><th>Classificação</th><th>Investidor</th></tr><thead><tbody>";

    for (var i = 0; i < table_array.length; i++) {
        row = table_array[i];

        table += 
            // Logomarca only
            "<tr><td rowspan='6'><img src=" + row['icon_filename'] + "></td>" 
            // Dados gerais | Contatos | Classificação
            // Row 1
             + "<td>Empresa: <button onclick=\"create_time_serie_search('"+ row['nome_empresa']
             + "', '" + row['nome_pregao'] + "', '" + row['cnpj'] + "')\">" + row['nome_empresa'] + "</a></td>";

        if (! is_empty(row['site'])){
            table += "<td>Site: <a href=https://" + row['site'] + ">" + row['site'] + "</a>";
        }else{
            table += "<td>Site: " + row['site'];
        }

        if (! is_empty(row['twitter_empresa'])){
            table += "<br>Twitter: <a href=" + row['twitter_empresa'] + ">" + row['twitter_empresa'] + "</a>";
        }else{
            table += "<br>Twitter: " + row['twitter_empresa'];
        }

        if (! is_empty(row['facebook_empresa'])){
            table += "<br>Facebook: <a href=" + row['facebook_empresa'] + ">" + row['facebook_empresa'] + "</a></td>";
        }else{
            table += "<br>Facebook: " + row['facebook_empresa'] + "</td>";
        }

        table += "<td>Setor: " + row['setor'] + "</td>"
            + "<td rowspan='6' style='max-width:150px; word-wrap:break-word;'>Nome: " + row['nome_contato'];

        if (! is_empty(row['twitter_contato'])){
            table += "<br>Twitter: <a href=" + row['twitter_contato'] + ">" + row['twitter_contato'] + "</a>";
        }else{
            table += "<br>Twitter: " + row['twitter_contato'];
        }

        if (! is_empty(row['facebook_contato'])){
            table += "<br>Facebook: <a href=" + row['facebook_contato'] + ">" + row['facebook_contato'] + "</a></td>";
        }else{
            table += "<br>Facebook: " + row['facebook_contato'] + "</td></tr>";
        }

            // Row 2
        table += "<tr><td>Nome de Pregão: " + row['nome_pregao'] + "</td>"
            + "<td>Endereço: "+row['endereco'] + "</td>"
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
            + "<tr><td rowspan='3' style='max-width:500px; word-wrap:break-word;'>Atividade Principal: "+row['atividade_principal'] + "</td>"
            + "</tr>" 
            // Row 5
            + "<tr><td>Telefones: " + row['telefone'] 
            + "<br>Fax: "+  row['fax'] + "</td></tr>"
            // Row 6
            + "<tr><td>Emails: " + row['emails'] + "</td></tr>";
    };
    table += "</tbody></table>";
    
    // Update the central bar with the new table
    $("#inner_central_bar").html(table);
}




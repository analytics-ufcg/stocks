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
            + "<td>Empresa: <strong>" + row['nome_empresa'] + "</strong></td>";

        if (! is_empty(row['site'])){
            table += "<td>Site: <a href=" + row['site'] + ">" + row['site'] + "</a>";
        }else{
            table += "<td>Site: " + row['site'];
        }

        if (! is_empty(row['twitter_empresa'])){
            table += "<br>Twitter: <a href=https://" + row['twitter_empresa'] + ">" + row['twitter_empresa'] + "</a>";
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
            table += "<br>Twitter: <a href=https://" + row['twitter_contato'] + ">" + row['twitter_contato'] + "</a>";
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


function show_top10_result(table_array){

    var metric_explanation_map = 
        {'Crescimento' : '', 
        'Queda' : '', 
        'Maior Liquidez' : '', 
        'Menor Liquidez' : '',
        'Oscilação' : ''};

    if (table_array.nomes.length <= 0){
        $("#central_bar_top").html("<em>Nada foi encontrado.</em>");
        return false;
    }

    // Create the table result as html
    grouping = $("#top10_grouping").val();
    metric = $("#top10_metric").val();

    var table = "<table id='empresa_table' class='table table-bordered table-condensed'>" 
                + "<thead><tr bgcolor='#f5f5f5'><td colspan='3' style='text-align:center;font-weight:bold'>Ranking de " + metric + " por " + metric + "</td></tr><tr bgcolor='#f5f5f5'><th>Ranking</th><th align='center'>" 
                + grouping + "</th><th id = 'top_metrica_col' data-placement='right' >" 
                + metric + "<i class='icon-info-sign' rel='popover'></i></th></tr><thead><tbody>";

    for (var i = 0; i < table_array.nomes.length; i++) {
        row = table_array[i];
        nome = table_array.nomes[i];
        valor = table_array.valores[i];
        pos_ranking = i + 1;
        table += 
            // Row 1
             "<tr><td>"+ pos_ranking + "</strong></td>";
        table += "<td>" + nome;
        table += "<td>" + valor;
    };
    table += "</tbody></table>";
    
    // Update the central bar with the new table
    $("#central_bar_top").html(table);

    // Set the popover in the metric column
    $("#top_metrica_col").popover({
        title: 'O que é ' + metric + '?',
        content: metric_explanation_map[metric]
    });  
}
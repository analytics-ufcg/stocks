
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


function show_top_result(table_array){

   if (table_array.nomes.length <= 0){
        $("#central_bar_top").html("<em>Nada foi encontrado.</em>");
        return false;
    }

    var metric_explanation_map = 
        {"Crescimento" : "O 'Crescimento' de uma ação é calculado através da diferença entre \
                        o último preço da 'Data Final' e preço de abertura da 'Data Inicial'. \
                        O 'Crescimento' de um grupo de ações é dado pela média dessas diferenças \
                        para todas as ações. Assim, as TOP ações/grupos são aqueles com maior \
                        diferença positiva.", 
        "Queda" : "A 'Queda' de uma ação é calculada através da diferença entre \
                    o último preço da 'Data Final' e preço de abertura da 'Data Inicial'. \
                    A 'Queda' de um grupo de ações é dado pela média dessas diferenças \
                    para todas as ações. Assim, as TOP ações/grupos são aqueles com maior \
                    diferença negativa.", 
        "Maior Liquidez" : "A Liquidez de uma ação/grupo é dada pela soma do volume de títulos \
                            negociados no intervalo entre e inclusive da 'Data Inicial' e \
                            'Data Final'. As ações/grupos com 'Maior Liquidez' são aquelas com \
                            maior volume de títulos negociados.", 
        "Menor Liquidez" : "A Liquidez de uma ação/grupo é dada pela soma do volume de títulos \
                            negociados no intervalo entre e inclusive da 'Data Inicial' e \
                            'Data Final'. As ações/grupos com 'Menor Liquidez' são aquelas com \
                            menor volume de títulos negociados.",
        "Oscilação" : "A 'Oscilação' de uma ação/grupo é dada pela soma das diferenças entre os \
                        preços dia-a-dia (i.e. o preço de hoje menos o de ontem). As TOP ações/grupos \
                        em 'Oscilação' são aquelas com maior soma."};

    // Create the table result as html
    grouping = $("#top_grouping").val();
    metric = $("#top_metric").val();

    var table = "<table id='empresa_table' class='table table-bordered table-condensed'>" 
                + "<thead><tr bgcolor='#f5f5f5'><td colspan='3' style='text-align:center;font-weight:bold'>Ranking de "
                 + metric + " por " + grouping + "</td></tr><tr bgcolor='#f5f5f5'><th style='text-align:center'>Ranking</th><th style='text-align:center'>" 
                + grouping + "</th><th style='text-align:center' id='top_metrica_col' data-placement='top' rel='popover'>" 
                + metric + "<i class='icon-info-sign'></i></th></tr><thead><tbody>";

    for (var i = 0; i < table_array.nomes.length; i++) {
        row = table_array[i];
        nome = table_array.nomes[i];
        valor = table_array.valores[i];
        pos_ranking = i + 1;

        if(grouping == "Ação")
        {
            array_nome = nome.split("(");
            isin_empresa = array_nome[1];
            isin_empresa = isin_empresa.replace(")","");
        }

        table += 
            // Row 1
             "<tr><td style='text-align:center'>"+ pos_ranking + "</strong></td>";
        
        if(grouping == "Ação"){
            table += "<td><button onclick=\"create_time_serie_top('"+ array_nome[0]
                     + "', '" + isin_empresa + "')\">" + nome + "</a>";
        }else {
            table += "<td>" + nome;
        }
        
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

function show_highchart(container_name, nome_pregao, nome_empresa, response, data_inicial, data_final, cnpj, isin){
    
    var seriesOptions = [];
     seriesOptions[0] = {
               name : nome_pregao,
        data: response[0]
    };
     seriesOptions[1] = {
         name : nome_pregao,
        color: '#BF0B23',
        data: response[1],
          enableMouseTracking: false
    };  
    
    function parse_date(data){
         array_data = data.split("/");
         return new Date(array_data[2] + "-" + array_data[1] + "-" + array_data[0]).getTime();
    }

    $("#" + container_name + "").dialog("option", "title", "Série Temporal - " + nome_empresa);
    
    if (response.length <= 0){
        // Show an error message
        $('#' + container_name + ' #time_serie').html("<em>Não existe cotação para essa empresa no intervalo 1994-2013.</em>");
    }else{
        // Create the chart
        $('#' + container_name + ' #time_serie').highcharts('StockChart', {

            chart: {
            events: {
                click: function(event) {
                    create_time_line_news(container_name, cnpj, isin, 
                        Highcharts.dateFormat('%Y-%m-%d', event.xAxis[0].value));
                    }
                }
            },

            rangeSelector : {
                selected : undefined
            },

            title : {
                text : nome_empresa
            },
            
            series : seriesOptions
        }); 

        if(container_name == "ts_news_container_top"){
            date1 = parse_date(data_inicial);
            date2 = parse_date(data_final);
             $('#' + container_name + ' #time_serie').highcharts().xAxis[0].setExtremes(date1, date2);    
        }    

    }
}

function show_news(container_name, news_list, date){
    
    if (date.length > 0){
        date = date.split("-");
        date.reverse();
        date = date.join("/");
    }
    var table1 = "<table id='empresa_table' class='table table-bordered table-condensed'>" + 
                    "<thead>" + 
                        "<tr bgcolor='#f5f5f5'>" +
                            "<th style='text-align:center'><img src='img/logo_estadao.jpg'>  Noticias do Estadão - (" + date + ")</th>" +
                        "</tr>";

    var table2 = "<table id='empresa_table' class='table table-bordered table-condensed'>" + 
                    "<thead>" + 
                        "<tr bgcolor='#f5f5f5'>" +
                            "<th style='text-align:center'><img src='img/logo_folha.jpg'>  Noticias da Folha de São Paulo - (" + date + ")</th>" + 
                        "</tr>";

    if (news_list.length > 0){
        for (var i = 0; i < news_list[0].length; i++){
            table1 += "<tr><td>" + "<a href=" + news_list[0][i][1] + ">" + news_list[0][i][0] +
                        "</a></td></tr>";
        }
    }

    table1 += "</thead></table>";
    table2 += "</thead></table>";
    $("#" + container_name + " #news #estadao").html(table1);
    $("#" + container_name + " #news #folha_sao_paulo").html(table2);
}

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

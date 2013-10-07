function show_highchart(container_name, nome_pregao, nome_empresa, response, data_inicial, data_final, query_news_value){
    
    var seriesOptions = [];
     seriesOptions[0] = {
               name : nome_pregao,
        data: response
    };
    var data_to_plot_index = 1;
    
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
        for (i = 1; i < response.length; i++){
            if(response[i][2] == true){
                seriesOptions[data_to_plot_index] = {
                        color: '#BF0B23',
                        data: [
                            {x: response[i-1][0], y: response[i-1][1]},
                            {x: response[i][0], y: response[i][1]}
                        ],
                         enableMouseTracking: false

                }
                data_to_plot_index++;
            }
        }
        $('#' + container_name + ' #time_serie').highcharts('StockChart', {

            chart: {
            events: {
                click: function(event) {
                    create_timed_news(container_name, query_news_value, 
                        Highcharts.dateFormat('%Y-%m-%d', event.xAxis[0].value));
                    }
                }
            },

            rangeSelector : {
                selected : undefined
            },
            
            exporting: {
                enabled: false
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

function show_news(container_name, date, news_by_fonte){
    
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

    folha_index = 0;
    estadao_index = 1;

    if (news_by_fonte.length >= 2){

        if (news_by_fonte[estadao_index].length > 0){
            for (var i = 0; i < news_by_fonte[estadao_index].length; i++){
                table1 += "<tr><td>" + "<a class='text-info' href=" + news_by_fonte[estadao_index][i][1] + ">" + news_by_fonte[estadao_index][i][0] +
                            "</a></td></tr>";
            }
        }else{
            table1 += "<tr><td><em>Não há notícias nesse dia.</em></td></tr>";
        }

        if (news_by_fonte[folha_index].length > 0){
            for (var i = 0; i < news_by_fonte[folha_index].length; i++){
                table2 += "<tr><td>" + "<a class='text-info' href=" + news_by_fonte[folha_index][i][1] + ">" + news_by_fonte[folha_index][i][0] +
                            "</a></td></tr>";
            }
        }else{
            table2 += "<tr><td><em>Não há notícias nesse dia.</em></td></tr>";
        }
    }

    table1 += "</thead></table>";
    table2 += "</thead></table>";
    $("#" + container_name + " #news #estadao").html(table1);
    $("#" + container_name + " #news #folha_sao_paulo").html(table2);
}

function show_news_stock_correlation_pdf(cnpj, isin){
    if (cnpj.length > 0){
        filename_pdf = "data/news_stock_correlation/news_stock_corr_" + cnpj + "_" + isin.toUpperCase() + ".pdf";

        // Container Search
        $("#ts_news_container_search #correlation_pdf").html("<a href='#' onclick=\"window.open('" 
            + filename_pdf + "', 'resizable,scrollbars');\" class='btn'> " 
            + "<i class='icon-download'></i> Baixe o PDF com: séries com solavancos e correlação das notícias e da cotação "
            + "<i class='icon-download'></i></a>");
    }else{
        // Container Top
        // $("#ts_news_container_top #correlation_pdf").html("<a href='#' onclick=\"window.open('" 
        //     + filename_pdf + "', 'resizable,scrollbars');\" class='btn'><i class='icon-download'></i></a>");
    }

}

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
                    create_timed_news(container_name, cnpj, isin, 
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

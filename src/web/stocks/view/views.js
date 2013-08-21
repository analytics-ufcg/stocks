function show_empresa_table(table_array){

    // Create the table result as html
    var table = "<table id='empresa_table' border='1'><tr><th>Logomarca</th><th>Dados Da Empresa</th><th>Contato</th><th>Qualificacao</th></tr><tbody><tr>";

    for (var i = table_array.length - 1; i >= 0; i--) {
        row = table_array[i];
        // icon_filename = "./images/logos/" + row['cnpj'] + ".jpg";

        // if (!file_exists(icon_filename)){
            // icon_filename = "./images/logos/sem_imagem.jpg";
        // }
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

// JQUERY dinamic table (http://www.trirand.com/blog/jqgrid/jqgrid.html)
// jQuery("#empresa_table").jqGrid({
//     url:'server.php?q=2',
//     datatype: "json",
//     colNames:['Inv No','Date', 'Client', 'Amount','Tax','Total','Notes'],
//     colModel:[
//         {name:'id',index:'id', width:55},
//         {name:'invdate',index:'invdate', width:90},
//         {name:'name',index:'name asc, invdate', width:100},
//         {name:'amount',index:'amount', width:80, align:"right"},
//         {name:'tax',index:'tax', width:80, align:"right"},      
//         {name:'total',index:'total', width:80,align:"right"},       
//         {name:'note',index:'note', width:150, sortable:false}       
//     ],
//     rowNum:10,
//     rowList:[10,20,30],
//     pager: '#pager2',
//     sortname: 'id',
//     viewrecords: true,
//     sortorder: "desc",
//     caption:"JSON Example"
// });
// jQuery("#empresa_table").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false});

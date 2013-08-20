function show_empresa_table(db_result){
	console.log(db_result);

    # Create the table result as html
    var table = "<table border='1'><tr><th>Logomarca</th><th>Dados Da Empresa</th><th>Contato</th><th>Qualificacao</th></tr><tbody><tr>";

  /* PHP code
  	while(row = db_result){
        $icon_filename = "./images/logos/" . $row['cnpj'] . ".jpg";

        if (!file_exists($icon_filename)){
            $icon_filename = "./images/logos/sem_imagem.jpg";
        }

        $table = $table."<td><img src=" . $icon_filename . "></td><td>Nome Empresa: "
        .$row['nome_empresa']."<br>Nome de Pregao: ".$row['nome_pregao']
        ."<br>Codigos de Negociacao <br>"."<br>Codigo CVM: ".$row['cod_cvm']."<br>CNPJ: "
        .$row['cnpj']."<br>Atividade Principal: ".$row['atividade_principal']."</td><td>"
        ."Site:".$row['site']."<br>Rua: ".$row['rua']."<br>CEP: ".$row['cep']."<br>Cidade: "
        .$row['cidade']."<br>Telefones: ".$row['telefone']."<br>Fax: ".$row['fax']."<br>Nomes:"
        .$row['nomes']."<br>Emails: ".$row['emails']
        ."</td><td>Setor: ".$row['setor']."<br>Subsetor: ".$row['sub_setor']."<br>Segmento: "
        .$row['segmento']."</td></tr>";
    }
    $table = $table."</tbody></table>";
    */
}
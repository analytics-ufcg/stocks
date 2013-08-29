<?php
/*
Autor: Danilo Gomes
Script para coletar o twitter das empresas e dos socios dela
O twitter deixa fazer apenas 180 requisicoes, com isso para funcionar
deve-se apagar as empresas que foram coletadas no csv e rodar novamente.
para executar basta:
        php pegarTwitter.php >> nomeArquivo
*/
ini_set('display_erros', 1);
//Include the class file
require_once('TwitterAPIExchange.php');

//Set access tokens
$settings = array(
    'oauth_access_token' => "1688957917-mvEZaCmkCFmcASNbPpjmCNbr1XfWH2wMEdCdj6S",
    'oauth_access_token_secret' => "S5u2qf7WdA9TZwXLzACkswJeEnAZCv5JbaQFyfTlU",
    'consumer_key' => "KnVPCehUNXd4cDc4IxchYw",
    'consumer_secret' => "6tYZYHIRn2eWHmF34BD7PAJ4qFRD7efcpsdFgLcyA"
);


//GET Request Example
$parametroCount = 20;//numero de resposta da requisicao
$url = 'https://api.twitter.com/1.1/users/search.json';
$pararFor = -1;//para realizar mini-trial. Numero de requisicoes, negativo quer dizer até o máximo possivel
$handle = fopen ("DadosEmpresas.csv","r");
$pegaEmpresa = false;//true para pegar os twitters das empresas, false para pegar os socios dela
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($pararFor == 0)//para realizar mini-trials
        {
                break;
        }
        $pararFor--;

        $companyNameColumn = 16;//coluna com os nomes dos socios
        if($pegaEmpresa){
                $companyNameColumn = 1;//coluna com o nome de pregao da empresa
        }


        $companyName = $data[$companyNameColumn];
        echo $companyName . ",";
        $companyName = str_replace(" ","%20",$companyName);
        $companyName = str_replace("S.A.","",$companyName);
        $companyName = str_replace("CIA ","",$companyName);


        $getfield = '?q=' . $companyName . '&page=1&count=' . $parametroCount;
        $requestMethod = 'GET';

        $twitter = new TwitterAPIExchange($settings);
        $response = $twitter->setGetfield($getfield)
                                 ->buildOauth($url, $requestMethod)
                                 ->performRequest();

        $menorValor = 20000;//valor que sempre serah o maior
        $resposta = "NA";
        $output = json_decode($response);
        for($i = 0; $i < count($output); $i++)//pegar todas as repostas e ver qual string do screen_name no twitter se assemelha ao nome da empresa
        {
                $diferencaString = levenshtein(strtoupper(str_replace("%20", "", $companyName)),strtoupper(str_replace("_"," ",$output[$i]->screen_name)));

                if( ($diferencaString < $menorValor)/* && ($diferencaString < strlen($companyName) / 2)*/ )//se tirar os comentarios, pelo menos metade da palavra, para ser aceita, tem que "bater"
                {
                        $resposta = "twitter.com/" . $output[$i]->screen_name;
                        $menorValor = $diferencaString;
                }

        }
        echo $resposta . "\n";

}

?>


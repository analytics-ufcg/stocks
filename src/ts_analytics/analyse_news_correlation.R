library(RODBC)

myconn <- odbcConnect("StocksDSN")

news.count.query <- 
"SELECT cnpj, data_noticia, fonte, count(*) AS num_noticias
FROM link_noticias_empresa
GROUP BY cnpj, fonte, data_noticia
ORDER BY data_noticia ASC"

news.count.df <- sqlQuery(myconn, news.count.query)

# Get all COTACAO from the CNPJ which there is at least one NOTICIA
emp.ts.query <- "SELECT emp.cnpj, emp_isin.cod_isin, acao.data_pregao, acao.preco_ultimo
                 FROM empresa AS emp INNER JOIN empresa_isin AS emp_isin ON emp.cnpj = emp_isin.cnpj
                     INNER JOIN cotacao AS acao ON emp_isin.cod_isin = acao.cod_isin 
                     LEFT JOIN (SELECT DISTINCT(cnpj) FROM link_noticias_empresa) 
                               AS news_table ON (emp.cnpj = news_table.cnpj)
                 WHERE acao.cod_bdi = 02"

emp.ts.df <- sqlQuery(myconn, emp.ts.query)

close(myconn)

# TODO:
# For each fonte:
#   Interpolate the NUM_NOTICIAS with zeros (0)
 
# For each CNPJ:
#   For each ISIN:
#     Interpolate the COTACAO with constant values

# For each CNPJ:
#   For each ISIN:
#     For each fonte:
#       Select the intersection between the series
#       Calculate the Pearson Correlation (for the hole series)
#       Calculate the Pearson Correlation (for the days with noticias only)
# Plot the correlations

# For each CNPJ:
#   For each ISIN:
#     Select the SOLAVANCOs dates
#     Filter the COTACAO to the SOLAVANCOs dates
#     For each fonte:
#       Filter the filled NUM_NOTICIAS to the SOLAVANCOs dates
#       Calculate the Pearson Correlation (for the restant series)
# Plot the correlations

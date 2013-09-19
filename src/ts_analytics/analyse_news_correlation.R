rm(list = ls())

library(RODBC)
library(plyr)
library(zoo)

myconn <- odbcConnect("StocksDSN")

news.count.query <- "SELECT cnpj, data_noticia, fonte, count(*) AS num_noticias
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


# For each (FONTE and CNPJ):
#   Interpolate the NUM_NOTICIAS with zeros (0)
news.count.filled <- ddply(news.count.df, .(fonte, cnpj), function(df){
  
  news.ts <- zoo(df$num_noticias, df$data_noticia)
  news.ts.complete <- merge.zoo(news.ts,
                               zoo(, seq(start(news.ts), end(news.ts), by="day")), all=TRUE)
  news.ts.complete[is.na(news.ts.complete)] <- 0
  return(data.frame(data_noticia = index(news.ts.complete), 
                    num_noticias = as.vector(news.ts.complete)))
}, .progress = "text")

# For each (CNPJ and ISIN):
#   Interpolate the COTACAO with constant values
emp.ts.filled <- ddply(emp.ts.df, .(cnpj, cod_isin), function(df){
  
  emp.ts <- zoo(df$preco_ultimo, df$data_pregao)
  emp.ts.complete <- merge.zoo(emp.ts,
                               zoo(, seq(start(emp.ts), end(emp.ts), by="day")), all=TRUE)
  
  # Fill the created gaps with the last non-NA value
  emp.ts.complete <- na.locf(emp.ts.complete)
  
  return(data.frame(data_pregao = index(emp.ts.complete), 
                    preco_ultimo = as.vector(emp.ts.complete)))
}, .progress = "text")




# TODO:
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

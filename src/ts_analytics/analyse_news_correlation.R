rm(list = ls())

library(RODBC)
library(plyr)
library(zoo)

cat("Connecting to the Stocks_DB to get the data directly!\n")
myconn <- odbcConnect("StocksDSN")

cat("Selecting the NEWS COUNT per (DAY and CNPJ)...\n")
news.count.query <- "SELECT cnpj, data_noticia, fonte, count(*) AS num_noticias
                     FROM link_noticias_empresa
                     GROUP BY cnpj, fonte, data_noticia
                     ORDER BY data_noticia ASC"

news.count.df <- sqlQuery(myconn, news.count.query)


cat("Selecting all COTACAO with the CNPJs that have at least one NOTICIA assigned to it...\n")
emp.ts.query <- "SELECT emp.cnpj, emp_isin.cod_isin, acao.data_pregao, acao.preco_ultimo
                 FROM empresa AS emp INNER JOIN empresa_isin AS emp_isin ON emp.cnpj = emp_isin.cnpj
                     INNER JOIN cotacao AS acao ON emp_isin.cod_isin = acao.cod_isin 
                     INNER JOIN (SELECT DISTINCT(cnpj) FROM link_noticias_empresa) 
                               AS news_table ON (emp.cnpj = news_table.cnpj)
                 WHERE acao.cod_bdi = 02"

emp.ts.df <- sqlQuery(myconn, emp.ts.query)
cat("Closing the connection.\n")
close(myconn)

cat("Interpolating with zeros the days without NEWS...\n")
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

cat("Interpolating with the last non-NA value all COTACAO...\n")
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

cat("Calculating the correlation between the (interpolated) NEWS COUNT and the (interpolated) COTACAO (per CNPJ and ISIN)...\n")
# For each Fonte:
#   For each (CNPJ and ISIN):
#       Select the intersection between the series
#       Calculate the Pearson Correlation (for the hole series)
#       Calculate the Pearson Correlation (for the days with noticias only AND filled Cotacao)
# TODO:
#       Calculate the Pearson Correlation (for the days with Cotacao only AND filled news)
#       Calculate the Pearson Correlation (for the days with Cotacao only AND news)
all.fontes <- as.character(unique(news.count.df$fonte))
emp.news.corr <- NULL

for (this.fonte in all.fontes){
  emp.news.corr.tmp <- ddply(emp.ts.filled, .(cnpj, cod_isin), function(df){
    
    ts.cnpj <- df$cnpj[1]
    ts.cod_isin <- as.character(df$cod_isin[1])
      
    # Get the Filled Stock ts by the CNPJ and ISIN
    price.ts.filled <- zoo(df$preco_ultimo, df$data_pregao)
    
    # FILLED NEWS TS CORRELATION
    news.df.filled <- subset(news.count.filled, fonte == this.fonte & cnpj == ts.cnpj)
    news.ts.filled <- zoo(news.df.filled$num_noticias, news.df.filled$data_noticia)
    
    # Find the intersection between the filled TSs and Filter them
    intersect.ts.filled <- merge.zoo(news.ts.filled, price.ts.filled, all = F)
    
    # Calculate the Filled Correlation
    ts.correlation.filled <- cor(as.vector(intersect.ts.filled[, "news.ts.filled"]), 
                                 as.vector(intersect.ts.filled[, "price.ts.filled"]), 
                                 method = "pearson")
    
    
    # DAYS WITH NON-ZERO NEWS TS CORRELATION
    news.df <- subset(news.count.df, fonte == this.fonte & cnpj == ts.cnpj)
    news.ts <- zoo(news.df$num_noticias, news.df$data_noticia)
    
    # Find the intersection between the 'days with news' TSs and Filter them
    intersect.ts.with.news <- merge.zoo(news.ts, price.ts.filled, all = F)
    
    # Calculate the 'days with news' Correlation
    ts.correlation.with.news <- cor(as.vector(intersect.ts.with.news[, "news.ts"]), 
                                    as.vector(intersect.ts.with.news[, "price.ts.filled"]), 
                                    method = "pearson")
    
    return(data.frame(fonte = this.fonte,
                      cnpj = ts.cnpj, 
                      cod_isin = ts.cod_isin,
                      all_filled_ts_corr = ts.correlation.filled, 
                      all_filled_ts_days_compared = nrow(intersect.ts.filled),
                      filled_cotacao_only_ts_corr = ts.correlation.with.news,
                      filled_cotacao_only_days_compared = nrow(intersect.ts.with.news),
                      filled_news_only_ts_corr = 1,
                      filled_news_only_days_compared = 1,
                      no_filling_ts_corr = 1,
                      no_filling_days_compared = 1))
  }, .progress = "text")
  
  emp.news.corr <- rbind(emp.news.corr, emp.news.corr.tmp)
}

# TODO:
# Plot/Print the correlations


# TODO:
# Select the SOLAVANCOs dates (previous and next day)
# Filter the COTACAO to these dates
# Filter the filled NUM_NOTICIAS to these dates
# Calculate the Pearson Correlation (separetely for each solavanco)


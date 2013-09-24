rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(RODBC)
library(plyr)
library(zoo)

source("src/ts_analytics/detect_ts_bursts_methods.R")

# =============================================================================
# FUNCTIONS
# =============================================================================

PlotTsWithSolavanco <- function(serie, solavanco, ylab, main.title){
  plot(serie, main = main.title, ylab = ylab, xlab = "Year")
  colour <- ifelse(solavanco, "red", "black")
  segments(x0=index(serie)[-c(length(serie))], y0=serie[-c(length(serie))], 
           x1=index(serie)[-1], y1=serie[-1], 
           col = colour)  
  legend("topright", legend=c("IS solavanco", "ISN'T solavanco"), 
         col=c("red", "black"), lty = 1)
}

# =============================================================================
# MAIN
# =============================================================================

cat("Connecting to the Stocks_DB to get the data directly!\n")
myconn <- odbcConnect("StocksDSN")

cat("Selecting the NEWS COUNT per (DAY and CNPJ)...\n")
news.count.query <- "SELECT cnpj, fonte, data_noticia, count(*) AS num_noticias
                     FROM link_noticias_empresa
                     GROUP BY cnpj, fonte, data_noticia
                     ORDER BY data_noticia ASC"

news.count.df <- sqlQuery(myconn, news.count.query)
news.count.df <- news.count.df[-c(1:2),]

cat("Selecting all COTACAO (already interpolated) with the CNPJs that have at least one NOTICIA assigned to it ...\n")
emp.ts.query <- "SELECT emp.cnpj, emp.nome_pregao, emp_isin.cod_isin, acao.data_pregao, acao.preco_ultimo
                FROM empresa AS emp INNER JOIN empresa_isin AS emp_isin ON emp.cnpj = emp_isin.cnpj
                     INNER JOIN (
                  		     SELECT slice_time as data_pregao, cod_isin, 
                				         TS_FIRST_VALUE(preco_ultimo IGNORE NULLS, 'const') as preco_ultimo
                				 FROM cotacao
                				 WHERE cod_bdi = 02
                				 TIMESERIES slice_time AS '1 day' OVER (PARTITION BY cod_isin ORDER BY data_pregao)
                			     ) AS acao ON emp_isin.cod_isin = acao.cod_isin 
                     INNER JOIN (
                     			SELECT DISTINCT(cnpj) 
                     			FROM link_noticias_empresa
                     			) AS news_table ON (emp.cnpj = news_table.cnpj)"

emp.ts.filled <- sqlQuery(myconn, emp.ts.query)
cat("Closing the connection.\n")
close(myconn)

cat("Cleaning the data:\n")
# Estadão.com.br: Removing the news before 2010-01-01
cat("  Estadão.com.br: Removing the news before 2010-01-01\n")
rows.to.remove <- which(news.count.df$fonte == 'Estadão.com.br' & news.count.df$data_noticia < as.POSIXct('2010-01-01', "%Y-%m-%d"))
news.count.df <- news.count.df[-rows.to.remove, ]

# For each (CNPJ and FONTE):
#   Interpolate the NUM_NOTICIAS with zeros (0)
cat("Interpolating with zeros the days without NEWS (per CNPJ and FONTE)...\n")
news.count.filled <- ddply(news.count.df, .(cnpj, fonte), function(df){
  
  news.ts <- zoo(df$num_noticias, df$data_noticia)
  news.ts.complete <- merge.zoo(news.ts,
                               zoo(, seq(start(news.ts), end(news.ts), by="day")), all=TRUE)
  news.ts.complete[is.na(news.ts.complete)] <- 0
  return(data.frame(data_noticia = index(news.ts.complete), 
                    num_noticias = as.vector(news.ts.complete)))
}, .progress = "text")

# For each (CNPJ and ISIN):
#     Select the intersection between the series
#     Calculate the Pearson Correlation (for the hole series)
#     Select the SOLAVANCOs
#     Calculate the Pearson Correlation for the Solavancos
#     Plot the Price and NewsCount Time-Series with the solavancos highlighted
cat("Calculating the correlation between the (interpolated) NEWS COUNT and the (interpolated) COTACAO (per CNPJ and ISIN)...\n")
cat("Plotting the Price and NewsCount Time-Series with the solavancos highlighted...\n")

window.size <- 15
emp.news.corr <- NULL
output.dir <- "data/time_series/news_correlation"
dir.create(output.dir, showWarnings=F)

pdf(paste(output.dir, "/news_correlation_ts.pdf", sep = ""), width = 25, height = 10)

d_ply(emp.ts.filled, .(cnpj, cod_isin), function(df){
  for (this.fonte in unique(news.count.df$fonte)){
    
      ts.cnpj <- df$cnpj[1]
      ts.nome.pregao <- df$nome_pregao[1]
      ts.cod_isin <- as.character(df$cod_isin[1])
        
      # Get BOTH, the Filled Price and the Price, TSs by the CNPJ and ISIN
      price.ts.tmp <- subset(emp.ts.filled, cnpj == ts.cnpj & cod_isin == ts.cod_isin)
      price.ts <- zoo(price.ts.tmp$preco_ultimo, price.ts.tmp$data_pregao)
  
      price.ts.filled <- zoo(df$preco_ultimo, df$data_pregao)
      
      # Get BOTH, the Filled News and the News, TSs by CNPJ
      news.df <- subset(news.count.df, fonte == this.fonte & cnpj == ts.cnpj)
      news.ts <- zoo(news.df$num_noticias, news.df$data_noticia)
      
      news.df.filled <- subset(news.count.filled, fonte == this.fonte & cnpj == ts.cnpj)
      news.ts.filled <- zoo(news.df.filled$num_noticias, news.df.filled$data_noticia)
  
      rm(price.ts.tmp, news.df, news.df.filled)
      
      # FILLED NEWS and FILLED PRICE (TS CORRELATION)
      # Find the intersection between the filled TSs and Filter them
      intersect.ts.filled <- merge.zoo(news.ts.filled, price.ts.filled, all = F)
      
      # Calculate the Filled Correlation
      ts.correlation.filled <- cor(as.vector(intersect.ts.filled[, "news.ts.filled"]), 
                                   as.vector(intersect.ts.filled[, "price.ts.filled"]), 
                                   method = "pearson")
  
      # Find the solavancos of the Filled Price TS
      intersect.news.ts <- intersect.ts.filled[, "news.ts.filled"]
      intersect.price.ts <- intersect.ts.filled[, "price.ts.filled"]
      
      solavancos <- LocalBaseline(intersect.price.ts, window.size)$is.burst
      
      # Calculate the Pearson Correlation to the non-Solavanco dates (only)
      
      solavanco.corr <- cor(as.vector(intersect.news.ts)[c(F, solavancos)], 
                            as.vector(intersect.price.ts)[c(F, solavancos)], 
                            method = "pearson")
  
      # Plot the Time-Series and Correlations
      par(mfrow = c(2, 1), mar=c(5.1, 4.1, 4.1, 2.1))
      
      PlotTsWithSolavanco(intersect.price.ts, solavancos, 
                          ylab = "Preco_Ultimo",
                          main.title = paste("Time-Series with Solavancos\n", ts.nome.pregao, 
                                             " (", ts.cod_isin,")   vs.  ", this.fonte, 
                                             "\nPearson Correlations: plain= ", 
                                             round(ts.correlation.filled, 3), 
                                             " / solavanco only= ", round(solavanco.corr, 3), 
                                             sep = ""))
      
      par(mar=c(5.1, 4.1, 0, 2.1)) # No title
      
      PlotTsWithSolavanco(intersect.news.ts, solavancos, 
                          ylab = "Number of News", main.title = "")
      
  }
}, .progress = "text")
dev.off()

# TODO:
# Analyse together and separately by fontes
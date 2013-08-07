library(plyr)
library(zoo)

rm(list = ls())
# -----------------------------------------------------------------------------
# FUNCTIONs
# -----------------------------------------------------------------------------

Method1 <- function(ts){
  is.burst <- rep(F, length(ts))
  
  # TODO: Add method
  is.burst <- sample(c(F, F, F, F, T), length(ts))
  
  return (is.burst)
}

# -----------------------------------------------------------------------------
# MAIN
# -----------------------------------------------------------------------------

# READ TS FROM BIG_COTACOES
cat("Reading data...")
ts.dir <- "data/time_series"
ts.data <- read.csv(paste(ts.dir, "ts_big_cotacoes.csv", sep = "/"))
ts.data$dataPregao <- as.Date(ts.data$dataPregao, format="%Y-%m-%d")

# DESCRIPTIVE ANALYSIS for all ts's
for (id in unique(ts.data$id)){
  serie.data <- ts.data[ts.data$id == id | is.na(ts.data$id), c("dataPregao", "premed")]
  
  # Adding the Dates without PREGAO (to create the GAPS in the TS)
  all.dates <- data.frame(dataPregao=seq(serie.data$dataPregao[1], 
                                         serie.data$dataPregao[nrow(serie.data)], 1))
  serie.data <- merge(serie.data, all.dates, all.y = T)

  # Generate the serie
  serie <- zoo(serie.data$premed, order.by=serie.data$dataPregao)
  
  # Plot it
  plot(serie, xlab = "Ano", ylab = "Preco Medio")
  
  # TODO
  b <- diff(serie, lag=1)
  plot(b, type = "l")
  hist(b, breaks = 100)
}

# APPLY AN ALGORITHM FOR EACH EMPRESA (use plyr)

# CREATE A LINE PLOT PER TS WITH THE BURST HIGHLIGHTED
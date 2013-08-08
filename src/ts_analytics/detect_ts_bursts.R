library(plyr)
library(zoo)

rm(list = ls())
# -----------------------------------------------------------------------------
# FUNCTIONs
# -----------------------------------------------------------------------------
DescribeTs <- function(ts.data){
  
  descriptive.ts.dir <- paste(ts.dir, "describe_ts", sep = "/")
  dir.create(descriptive.ts.dir, showWarnings=F)
  
  # DESCRIPTIVE ANALYSIS for all ts's
  cat("Describing the Time-Series per Id:\n")
  for (id in sort(unique(ts.data$id))){
    cat("  Id:", id, "\n")
    serie.data <- ts.data[ts.data$id == id | is.na(ts.data$id), c("dataPregao", "premed")]
    serie.name <- ts.data[ts.data$id == id | is.na(ts.data$id), c("nomres","codisi", "codbdi")][1,]
    
    # Adding the Dates without PREGAO (to create the GAPS in the TS)
    all.dates <- data.frame(dataPregao=seq(serie.data$dataPregao[1], 
                                           serie.data$dataPregao[nrow(serie.data)], 1))
    serie.data <- merge(serie.data, all.dates, all.y = T)
    
    # Generate the serie
    serie <- zoo(serie.data$premed, order.by=serie.data$dataPregao)
    
    # Plot it
    serie.main <- paste(sub(" +$", "", serie.name$nomres), # Replace trailer spaces
                        sub(" +$", "", serie.name$codisi), 
                        serie.name$codbdi, sep = " - ")
    
    png(paste(descriptive.ts.dir, "/serie_", id, ".png", sep = ""), width = 1200, height = 1000)
    par(mfrow=c(3,1))
    
    plot(serie, main = serie.main, xlab = "Ano", ylab = "Preco Medio")
    
    serie.diff <- diff(serie, lag=1)
    plot(serie.diff, xlab = "Ano", ylab = "Diff (lag 1) - Preco Medio")
    
    hist(serie.diff, breaks = 100, main = "", 
         xlab = "Ano", ylab = "Hist (Diff, lag 1) - Preco Medio")
    
    dev.off()
  }
  
}


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
cat("Reading data...\n")
ts.dir <- "data/time_series"
ts.data <- read.csv(paste(ts.dir, "ts_big_cotacoes.csv", sep = "/"))
ts.data$dataPregao <- as.Date(ts.data$dataPregao, format="%Y-%m-%d")

DescribeTs(ts.data)

# APPLY AN ALGORITHM FOR EACH TS (use plyr)
# CREATE A LINE PLOT PER TS WITH THE BURST HIGHLIGHTED
  
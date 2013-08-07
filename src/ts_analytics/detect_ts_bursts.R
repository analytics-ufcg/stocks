library(plyr)

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
ts.dir <- "data/time_series"
ts.data <- read.csv(paste(ts.dir, "ts_big_cotacoes.csv", sep = "/"))

# TODO: DESCRIPTIVE ANALYSIS for all ts's
for (id in unique(ts.data$id)){
  serie <- ts.data[ts.data$id == id,]
  plot.ts(serie$premed, type = "l")
  b <- diff(serie$premed, lag=1)
  plot(b, type = "l")
  hist(b, breaks = 100)
  
}

# APPLY AN ALGORITHM FOR EACH EMPRESA (use plyr)

# CREATE A LINE PLOT PER TS WITH THE BURST HIGHLIGHTED
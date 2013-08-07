library(plyr)

rm(list = ls())
# -----------------------------------------------------------------------------
# FUNCTIONs
# -----------------------------------------------------------------------------

Method1 <- function(ts){
  is.burst <- rep(F, length(ts))
  
  # TODO: Add method
  is.burst <- sample(c(T, T, T, T, F), length(ts))
  
  return (is.burst)
}

# -----------------------------------------------------------------------------
# MAIN
# -----------------------------------------------------------------------------

# READ TS FROM BIG_COTACOES
ts.data <- read.csv("data/time_series/ts_big_cotacoes.csv")

# TODO: DESCRIPTIVE ANALYSIS for all ts's
serie <- subset(ts.data, id == 1)
# plot(serie$premed, type = "l")
# b <- diff(serie$premed, lag=1)
# plot(b, type = "l")
# hist(b, breaks = 100)

# APPLY AN ALGORITHM FOR EACH EMPRESA (use plyr)

# CREATE A LINE PLOT PER TS WITH THE BURST HIGHLIGHTED
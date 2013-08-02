library(plyr)

rm(list = ls())
# -----------------------------------------------------------------------------
# FUNCTIONs
# -----------------------------------------------------------------------------

Method1 <- function(ts){
  is.burst <- rep(F, length(ts))
  
  return (is.burst)
}

# -----------------------------------------------------------------------------
# MAIN
# -----------------------------------------------------------------------------

# READ TS FROM BIG_EMPRESAS
ts.data <- read.csv("data/time_series/ts_big_cotacoes.csv")


# APPLY AN ALGORITHM FOR EACH EMPRESA (use plyr)

# CREATE A LINE PLOT PER EMPRESA WITH THE BURST HIGHLIGHTED



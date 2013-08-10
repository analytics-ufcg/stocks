rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(zoo)
source("src/ts_analytics/detect_ts_bursts_methods.R")

# =============================================================================
# FUNCTIONs
# =============================================================================
DescribeTs <- function(serie, serie.name, descriptive.ts.dir){
  # DESCRIPTIVE ANALYSIS
  
  # PNG file
  #   png(paste(descriptive.ts.dir, "/", serie.name, ".png", sep = ""), width = 1200, height = 1000)
  # PDF file
  pdf(paste(descriptive.ts.dir, "/", serie.name, ".pdf", sep = ""), width = 30, height = 24)
  par(mfrow=c(3,1), mar=c(0,4,0.5,0.5), oma = c(.75, .75, .75, .75))
  
  plot(serie, ylab = "Preco Medio", xaxt = "n", lwd = 2)
  
  # Change the margins
  par(mar=c(4,4,0.5,0.5))
  
  serie.diff <- diff(serie, lag=1)
  plot(serie.diff, xlab = "Ano", ylab = "Diff (lag 1) - Preco Medio")
  
  # Change the margins
  par(mar=c(4,4,3.5,0.5))
  
  hist(serie.diff, breaks = 100, main = "Histograma", 
       xlab = "Diff (lag 1) - Preco Medio", ylab = "Frequencia")
  
  dev.off()
  
}

ApplyBurstSelectionMethods <- function(serie, serie.name, methods, burst.ts.dir){
  
  # PNG file
  #   png(paste(burst.ts.dir, "/", serie.name, ".png", sep = ""), width = 1200, height = 1000)
  # PDF file
  pdf(paste(burst.ts.dir, "/", serie.name, ".pdf", sep = ""), 
      width = 30, height = (10 * length(methods)))
  
  par(mfrow = c(length(methods), 1), mar=c(0,4,0.5,0.5), oma = c(.75, .75, .75, .75))
  
  # Predicted Burst series
  for(i in 1:length(methods)){
    result <- methods[[i]](serie)
    
    # Plot the TS
    if (i >= length(methods)){
      # Change the margins and the add the x-axis and x-label
      par(mar=c(4,4,0.5,0.5))
      plot(serie, 
           xlab = "Ano", ylab = paste("Preco Medio (", result$method_name, ")", sep =""))
    }else{
      plot(serie, xaxt="n", type = "n", 
           xlab = "", ylab = paste("Preco Medio (", result$method_name, ")", sep =""))
    }
    
    # Highlight the bursts
    colour <- ifelse(result$is.burst, "red", "black")
    
    segments(x0=index(serie)[-c(length(serie))], y0=serie[-c(length(serie))], 
             x1=index(serie)[-1], y1=serie[-1], 
             col = colour, lwd = 2)
  }
  
  dev.off()
}

# =============================================================================
# MAIN
# =============================================================================

# READ TS FROM BIG_COTACOES
cat("Reading data...\n")
ts.dir <- "data/time_series"
ts.data <- read.csv(paste(ts.dir, "ts_big_cotacoes.csv", sep = "/"))
ts.data$dataPregao <- as.Date(ts.data$dataPregao, format="%Y-%m-%d")

# Create the output directories
cat("Creating output directories...\n")
descriptive.ts.dir <- paste(ts.dir, "describe_ts", sep = "/")
dir.create(descriptive.ts.dir, showWarnings=F)

burst.ts.dir <- paste(ts.dir, "burst_ts", sep = "/")
dir.create(burst.ts.dir, showWarnings=F)

# Define the Burst Selection methods
methods <- c(GlobalBaseline, LocalBaseline, LMMEBasedDetector)

cat("Iterating over series...\n")
for (id in sort(unique(ts.data$id))){
  cat("  Id:", id, "\n")
  
  # ----------------------------------------------------------------------------
  # Prepare the time serie
  # -----------------------------------------------------------------------------
  serie.data <- ts.data[ts.data$id == id | is.na(ts.data$id), c("dataPregao", "premed")]
  serie.name <- ts.data[ts.data$id == id | is.na(ts.data$id), c("nomres","codisi", "codbdi")][1,]
  serie.name <- paste(id, 
                      sub(" +$", "", serie.name$nomres), # Replace trailer spaces
                      sub(" +$", "", serie.name$codisi), # Replace trailer spaces
                      serie.name$codbdi, sep = "_")
  
  # Generate the serie
  serie <- zoo(serie.data$premed, order.by=serie.data$dataPregao)
  
  # Adding the Dates without PREGAO (to create the GAPS in the TS)
  serie <- merge.zoo(serie, zoo(, seq.Date(start(serie), end(serie), by="day")), all=TRUE)

  # ----------------------------------------------------------------------------
  # Describe it
  # -----------------------------------------------------------------------------
  cat("    Describing it...\n")
  DescribeTs(serie, serie.name, descriptive.ts.dir)

  # ----------------------------------------------------------------------------
  # Apply the burst selection method
  # -----------------------------------------------------------------------------
  cat("    Applying the Burst Detection Methods...\n")
  ApplyBurstSelectionMethods(serie, serie.name, methods, burst.ts.dir)
}


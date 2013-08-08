rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(zoo)

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
  pdf(paste(burst.ts.dir, "/", serie.name, ".pdf", sep = ""), width = 30, height = 24)
  par(mfrow = c(length(methods)+1, 1), mar=c(0,4,0.5,0.5), oma = c(.75, .75, .75, .75))
  
  # Predicted Burst series
  for(method in methods){
    result <- method(serie.data$premed)
    
    # Plot the TS
    plot(serie, xaxt="n", type = "n", 
         xlab = "Ano", ylab = paste("Preco Medio (", result$method_name, ")", sep =""))
    
    # Highlight the bursts
    colour <- ifelse(result$is.burst, "red", "black")
    
    segments(x0=index(serie)[-c(length(serie))], y0=serie[-c(length(serie))], 
             x1=index(serie)[-1], y1=serie[-1], 
             col = colour[-1], lwd = 2)
  }
  
  # Change the margins
  par(mar=c(4,4,0.5,0.5))
  # Plot Normal serie
  plot(serie, xlab = "Ano", ylab = "Preco Medio", lwd = 2)
  
  dev.off()
}


# -----------------------------------------------------------------------------
# BURST SELECTION - METHODS
# -----------------------------------------------------------------------------
Method1 <- function(ts){
  # REMEMBER: The method should return an array of logic (TRUE or FALSE) with 
  # length = length(ts) - 1
  is.burst <- rep(F, length(ts)-1)
  
  # Random Example
  is.burst <- sample(c(F, F, F, F, T), length(is.burst), replace = T)
 
  # REMEMBER: The method should return a list with these variables (is.burst and method_name)
  return (list(is.burst = is.burst, 
               method_name = "Method_1"))
}

Method2 <- function(ts){
  # REMEMBER: The method should return an array of logic (TRUE or FALSE) with 
  # length = length(ts) - 1
  is.burst <- rep(F, length(ts) - 1)
  
  # TODO: Add method
  is.burst <- sample(c(F, F, F, F, T), length(is.burst), replace = T)
  
  # REMEMBER: The method should return a list with these variables (is.burst and method_name)
  return (list(is.burst = is.burst, method_name = "Method_2"))
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
methods <- c(Method1, Method2)

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
  
  # Adding the Dates without PREGAO (to create the GAPS in the TS)
  all.dates <- data.frame(dataPregao=seq(serie.data$dataPregao[1], 
                                         serie.data$dataPregao[nrow(serie.data)], 1))
  serie.data <- merge(serie.data, all.dates, all.y = T)
  
  # Generate the serie
  serie <- zoo(serie.data$premed, order.by=serie.data$dataPregao)
  
  # ----------------------------------------------------------------------------
  # Describe it
  # -----------------------------------------------------------------------------
  cat("    Describing it...\n")
  DescribeTs(serie, serie.name, descriptive.ts.dir)
  
  # ----------------------------------------------------------------------------
  # Apply the burst selection method
  # -----------------------------------------------------------------------------
  cat("    Applying the Burst Selection Methods...\n")
  ApplyBurstSelectionMethods(serie, serie.name, methods, burst.ts.dir)  
}


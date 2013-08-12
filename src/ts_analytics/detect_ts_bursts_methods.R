library(zoo)

# -----------------------------------------------------------------------------
# BURST DETECTION METHODS
# -----------------------------------------------------------------------------
# These method return a list with: a logic (TRUE or FALSE) vector (is.burst) of 
# length = length(serie) - 1 and the name of the method (method.name) 

GlobalBaseline <- function(serie, limit.quantile = .95){

  diff.serie <- diff(serie, lag=1)
  abs.serie <- abs(diff.serie)
  
  is.burst <- (abs.serie > quantile(abs.serie, limit.quantile, na.rm=T))
  
  # Return the list
  return (list(is.burst = is.burst, method.name = "Global-Baseline Detector"))
}

LocalBaseline <- function(serie, window.size = 30, limit.quantile = .95){

  diff.serie <- diff(serie, lag=1)
  abs.serie <- abs(diff.serie)
  
  # Windowing... (rollapply applies the function at each window)
  is.burst <- rollapply(abs.serie, window.size, FUN=function(x){
    x[length(x)] > quantile(x, limit.quantile, na.rm=T)
  })
  
  # Fill in the initial values with FALSE (the window does not permit the evaluation
  # of the initial values)
  is.burst <- c(rep(F, length(abs.serie) - length(is.burst)), is.burst)
  
  # Return the list
  return (list(is.burst = is.burst, method.name = "Local-Baseline Detector"))
}

LongTermVisionDetector <- function(serie, horizon.size = 30, horizons.window.size = 10, 
                                   limit.quantile = .95){
  
  # This algorithm is based on the LMME algorithm
  # More information see: http://www.joics.com/publishedpapers/2013_10_9_2747_2756.pdf
  
  # Split the sequence in horizon.size chunks
  serie.split <- split(serie, ceiling(seq_along(serie)/horizon.size))
  
  # Define a sequence formed by the min and max points of every split
  min.max.serie <- NULL
  for(i in 1:length(serie.split)){
    split = serie.split[[i]]
    min.val <- min(split, na.rm=T)
    max.val <- max(split, na.rm=T)
    
    if (length(min.max.serie) > 0){
      min.max.serie <- c(min.max.serie, split[which(split == min.val | split == max.val)])
    }else{
      min.max.serie <- split[which(split == min.val | split == max.val)]
    }
  }

  # Select the EXTREME points (alternating MAX and MIN values) from min.max.serie
  is.extrema <- rep(F, length(min.max.serie))
  for (i in 2:(length(min.max.serie)-1)){
    point <- min.max.serie[i][[1]]
    prev.point <- min.max.serie[i-1][[1]]
    next.point <- min.max.serie[i+1][[1]]
    
    is.extrema[i] <- ((point > prev.point & point > next.point) |
                        (point < prev.point & point < next.point))
  }

  # The extrema.serie is formed by the extreme points from the min.max.serie AND
  # the initial and the last points of the original serie
  extrema.serie <- c(min.max.serie[is.extrema], serie[1], serie[length(serie)])
  
  # Define the bursts
  result <- LocalBaseline(extrema.serie, horizons.window.size, limit.quantile)
  is.burst <- as.vector(result$is.burst)
  is.burst <- c(is.burst, is.burst[length(is.burst)]) # Repeat the last value
  
  # We shift the interior days of the extreme.serie (because the interior burst 
  # intervals should start at the day after an extreme point, and finish at the 
  # next extreme point day)
  shifted.days <- index(extrema.serie)
  shifted.days[2:(length(shifted.days)-1)] <- shifted.days[2:(length(shifted.days)-1)]+1
  
  # ReCreate the extreme.serie now with TRUE or FALSE values, then MERGE it 
  # with the complete serie adding the intermediate days
  is.burst.complete <- merge.zoo(zoo(is.burst, order.by=shifted.days), 
                                 zoo(,seq(start(serie), end(serie), by="day")), all=TRUE)
  
  # Replace the NA values of the intermeadiate extreme point values with the last 
  # extreme point value (TRUE or FALSE), creating the long sight term
  is.burst.complete <- na.locf(is.burst.complete)
  
  # Remove the values unexistent in the original serie
  is.burst.complete[is.na(serie)] <- rep(NA, sum(is.na(serie)))
  
  # Remove the first value to agree with the function signature
  is.burst.complete <- is.burst.complete[-1]
  
  # Return the list
  return (list(is.burst = is.burst.complete, method.name = "Long-Term Vision Detector"))
}

# Function used to understand the LMME algorithm
LMMEStudy <- function(extrema.serie){
  plot(extrema.serie[1:80], xlab = "Ano", ylab = "Preco Medio")
  for(val in index(extrema.serie[1:80])){
    abline(v=val)
  }
}

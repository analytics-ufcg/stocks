# -----------------------------------------------------------------------------
# BURST DETECTION METHODS
# -----------------------------------------------------------------------------

GlobalBaseline <- function(serie, limit.quantile = .95){
  # This method should return an array of logic (TRUE or FALSE) with 
  # length = length(ts) - 1
  
  #   serie <- serie[!is.na(serie)]
  diff.serie <- diff(serie, lag=1)
  positive.serie <- sqrt(diff.serie^2)
  
  is.burst <- (positive.serie > quantile(positive.serie, limit.quantile))
  
  # This method return a list with two variables: is.burst and method_name
  return (list(is.burst = is.burst, method_name = "Global-Baseline"))
}

LocalBaseline <- function(serie, window.size = 30, limit.quantile = .95){
  
  #   serie <- serie[!is.na(serie)]
  diff.serie <- diff(serie, lag=1)
  positive.serie <- sqrt(diff.serie^2)
  
  # Windowing... (rollapply applies the function at each window)
  is.burst <- rollapply(positive.serie, window.size, FUN=function(x){
    x[length(x)] > quantile(x, limit.quantile)
  })
  
  # Fill in the initial values with FALSE (the window does not permit the evaluation
  # of the initial values)
  is.burst <- c(rep(F, length(positive.serie) - length(is.burst)), is.burst)
  
  # This method return a list with two variables: is.burst and method_name
  return (list(is.burst = is.burst, method_name = "Local-Baseline"))
}

LMMEBasedDetector <- function(serie, window.size = 30, limit.quantile = .95){
  
  #   serie <- serie[!is.na(serie)]
  diff.serie <- diff(serie, lag=1)
  positive.serie <- sqrt(diff.serie^2)
  
  # Windowing... (rollapply applies the function at each window)
  is.burst <- rollapply(positive.serie, window.size, FUN=function(x){
    x[length(x)] > quantile(x, limit.quantile)
  })
  
  # Fill in the initial values with FALSE (the window does not permit the evaluation
  # of the initial values)
  is.burst <- c(rep(F, length(positive.serie) - length(is.burst)), is.burst)
  
  # This method return a list with two variables: is.burst and method_name
  return (list(is.burst = is.burst, method_name = "LMME-Based Detector"))
}


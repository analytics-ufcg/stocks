library(lubridate)
library(plyr)

rm(list = ls())

# ======================================================================
# FUNCTIONS
# ======================================================================
SelectBigCotacoes <- function(num.cotacoes){
	# Put data in memory
	cat("Reading data...\n")
	ts.data <- NULL

	years <- 1986:2013
	for (year in years){
		cat ("  ", year, "\n")
		data <- read.csv(paste("data/Historico_Cotacoes_CSV/cotacoes_", year,".csv", sep = ""), 
		                 # Define the column names
		                 col.names = c("dataPregao", "codbdi", "codneg", "tpmerc", 
		                               "nomres", "especi", "prazot", "modref", "preabe", "premax", 
		                               "premin", "premed", "preult", "preofc", "preofv", "totneg", 
		                               "quatot", "voltot", "preexe", "indopc", "datven", "fatcot",
		                               "ptoexe", "codisi", "dismes"), 
		                 # Define the column classes (improve time performance)
		                 colClasses = c(rep("character", 3), "numeric", rep("character", 4),
                                    rep("numeric", 7), rep("numeric", 2), rep("numeric", 2),
                                    "numeric", "character", "numeric", "numeric", 
                                    rep("character", 2)), 
		                 # Define the file encoding
                     fileEncoding="latin1",
                     # The strings are characters not factors (improve time performance)
		                 stringsAsFactors = F)

		# Select the the ts data (average price in stock)
		ts.data <- rbind(ts.data, data[,c("dataPregao", "nomres", "codisi", "premed")])
	}
  rm(data, year)
  
	# Cast dataPregao to date object
	cat("Cast dataPregao to Date...\n")
	ts.data$dataPregao <- as.Date(ts.data$dataPregao, "%Y%m%d")

	# Get the days and sort them
	days <- sort(unique(ts.data$dataPregao))

	# Select the empresas
	cat("Select the cotacoes by ISIN in (1986 AND 2013)...\n")
	cotacoes.1986 <- unique(ts.data[year(ts.data$dataPregao) == 1986, "codisi"])
	cotacoes.2013 <- unique(ts.data[year(ts.data$dataPregao) == 2013, "codisi"])
  
  # TODO: PROBLEM, no cotacao!
  big.cotacoes.isin <- cotacoes.1986[cotacoes.1986 %in% cotacoes.2013]
  
  cat("Count the quantity of cotacoes per ISIN...")
	cotacao.size <- ddply(subset(ts.data, codisi %in% big.cotacoes.isin), "codisi", function(df){
	  size <- nrow(df)
    return(data.frame(codisi = df$codisi[1], size = size))
	}, .progress = "text")
  
  # Order the empresas
	cotacao.size <- cotacao.size[order(cotacao.size$size, decreasing = T),]
  
	# Return the desired number of empresas
	return(subset(ts.data, codisi %in% cotacao.size$codisi[1:num.cotacoes]))
}

# MAIN
num.cotacoes <- 10
ts.data.big.cotacoes <- SelectBigCotacoes(num.cotacoes)
cat("Selected cotacoes (codisi):\n")
cat(unique(ts.data.big.cotacoes$codisi))

cat("\nPersist the time-series for the", num.cotacoes, "empresas...\n")

ts.dir <- "data/time_series"
dir.create(ts.dir, showWarnings=F)

write.csv(ts.data.big.cotacoes, paste(ts.dir, "/ts_big_cotacoes.csv", sep = ""),
          row.names = F)

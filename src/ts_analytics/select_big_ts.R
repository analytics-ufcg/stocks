library(lubridate)
library(plyr)

rm(list = ls())

# ======================================================================
# FUNCTIONS
# ======================================================================
SelectBigCotacoes <- function(num.cotacoes, pk.cols){
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
		ts.data <- rbind(ts.data, data)
	}
  rm(data, year)
  
	# Cast dataPregao to date object
	cat("Cast dataPregao to Date...\n")
	ts.data$dataPregao <- as.Date(ts.data$dataPregao, "%Y%m%d")

	# Get the days and sort them
	days <- sort(unique(ts.data$dataPregao))

	# Select the cotacoes
	cat("Select the ISINs in 2013...\n")
	isin.2013 <- unique(ts.data[year(ts.data$dataPregao) == 2013, "codisi"])
  
  cat("Count the quantity of cotacoes...\n")
	cotacao.size <- ddply(subset(ts.data, codisi %in% isin.2013), 
	                      pk.cols, function(df){
	                        size <- nrow(df)
	                        return(data.frame(codisi = df$codisi[1], 
	                                          codbdi = df$codbdi[1], 
	                                          tpmerc = df$tpmerc[1], 
	                                          codneg = df$codneg[1], 
                                            size = size))
	                      }, .progress = "text")
  
  cat("Return the largest cotacoes...\n")
  
  # Order the ISINs by cotacoes
	cotacao.size <- cotacao.size[order(cotacao.size$size, decreasing = T),]
  
  # Select the cotacoes and merge with the complete ts.data
  selected.cotacoes <- cotacao.size[1:num.cotacoes, pk.cols]
	selected.cotacoes$id <- 1:num.cotacoes
	final.data <- merge(ts.data, selected.cotacoes, all.x = F, all.y = T,
	                    by = pk.cols)
  
  # Organize the data columns and order by date
	final.data <- final.data[order(final.data$dataPregao),]
  initial.cols <- c("dataPregao", pk.cols, "nomres")
	final.data <- final.data[,c(initial.cols, 
	                            colnames(final.data)[!colnames(final.data) %in% initial.cols])]
  
	# Return the desired number of empresas
	return(final.data)
}

# MAIN
num.cotacoes <- 10
pk.cols <- c("codisi", "codbdi", "tpmerc", "codneg", "prazot")

ts.data.big.cotacoes <- SelectBigCotacoes(num.cotacoes, pk.cols)

cat("Selected cotacoes:\n")
print(ts.data.big.cotacoes[!duplicated(ts.data.big.cotacoes[,pk.cols]), pk.cols])

cat("\nPersist the time-series...\n")

ts.dir <- "data/time_series"
dir.create(ts.dir, showWarnings=F)

write.csv(ts.data.big.cotacoes, paste(ts.dir, "/ts_big_cotacoes.csv", sep = ""),
          row.names = F)

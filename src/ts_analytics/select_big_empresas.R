library(lubridate)
library(plyr)

rm(list = ls())

# ======================================================================
# FUNCTIONS
# ======================================================================
SelectBigEmpresas <- function(number.empresas){
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
		ts.data <- rbind(ts.data, data[,c("dataPregao", "nomres", "premed")])
	}
  rm(data, year)
  
	# Cast dataPregao to date object
	cat("Cast dataPregao to Date...\n")
	ts.data$dataPregao <- as.Date(ts.data$dataPregao, "%Y%m%d")

	# Get the days and sort them
	days <- sort(unique(ts.data$dataPregao))

	# Select the empresas
	cat("Select the empresas with cotacoes in (1986 AND 2013)...\n")
	big.empresas <- unique(ts.data[year(ts.data$dataPregao) == 1986 |
	                                 year(ts.data$dataPregao) == 2013, "nomres"])
  
  cat("Count the quantity of cotacoes per empresa...")
	emp.size <- ddply(subset(ts.data, nomres %in% big.empresas), "nomres", function(df){
	  size <- nrow(df)
    return(data.frame(nomres = df$nomres[1], size = size))
	}, .progress = "text")
  
  # Order the empresas
	emp.size <- emp.size[order(emp.size$size, decreasing = T),]
  
	# Return the desired number of empresas
	return(subset(ts.data, nomres %in% emp.size$nomres[1:number.empresas]))
}

# MAIN
number.empresas <- 10
ts.data.big.empresas <- SelectBigEmpresas(number.empresas)
cat("Selected empresas (nome_pregao):\n")
cat(unique(ts.data.big.empresas$nomres))

cat("\nPersist the time-series for the", number.empresas, "empresas...\n")

ts.dir <- "data/time_series"
dir.create(ts.dir, showWarnings=F)

write.csv(ts.data.big.empresas, paste(ts.dir, "/ts_big_empresas.csv", sep = ""),
          row.names = F)

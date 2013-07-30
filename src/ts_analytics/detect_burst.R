rm(list = ls())

# ======================================================================
# FUNCTIONS
# ======================================================================
SelectBigEmpresas <- function(){
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
					 "ptoexe", "codisi", "dismes"), fileEncoding="latin1") # test:  

		# Select the the ts data (average price in stock)
		ts.data <- rbind(ts.data, data[,c("dataPregao", "nomres", "premed")])
	}

	# Cast dataPregao to date object
	cat("Cast dataPregao to Date and sort it...\n")
	ts.data$dataPregao <- as.Date(as.character(ts.data$dataPregao), "%Y%m%d")

	# Sort the dates
	days <- sort(unique(ts.data$dataPregao))

	# Select the empresas
	# X = empresas that have cotacao in the first day of 1986
	#	For each day:
	# 		Get all empresas with cotacoes
	#		Y = all empresas of this day
	# 		Remove the empresas that haven't cotacoes all day
	#		X = X in Y (update X)
	cat("Select the empresas with cotacoes every day from 1986 to 2013...\n")
	big.empresas <- unique(ts.data[ts.data$dataPregao == ts.data$dataPregao[1], "nomres"])

	for (day in days[-1]){
		empresas.today <- unique(ts.data[ts.data$dataPregao == day, "nomres"])
		big.empresas <- big.empresas[big.empresas %in% empresas.today]
	}
	
	return(subset(ts.data, nomres %in% big.empresas))
}

# MAIN

ts.data <- SelectBigEmpresas()


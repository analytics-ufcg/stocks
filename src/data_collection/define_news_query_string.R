rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(lubridate)
library(plyr)
library(zoo)
library(reshape2)

# =============================================================================
# FUNCTIONS
# =============================================================================

SelectStartEndDatePerIsin <- function(df){
  start.date <- NA
  end.date <- NA

  for (values in unique(df$cod_isin)){
    dates <- subset(emp.ts.joined, cod_isin == values, "dataPregao")[,1]
    dates <- dates[order(dates)]
    
    if (!is.na(dates[1])){
      if (is.na(start.date) | dates[1] < start.date){
        start.date <- dates[1]
      }
      
      if (is.na(end.date) | dates[length(dates)] > end.date){
        end.date <- dates[length(dates)]
      }
    }
  }
  
  return(cbind(df[1,c("setor", "sub_setor", "segmento", "nome_empresa", "nome_pregao", "cnpj")], 
               data.frame(query_start_date = start.date, query_end_date = end.date)))
}


SelectStartEndDateFromEmp <- function(df){
  return(data.frame(query_start_date = min(df$query_start_date), 
                    query_end_date = max(df$query_end_date)))
}

# =============================================================================
# MAIN
# =============================================================================

cat("============== QUERY STRING DEFINITION ==============\n")

# -----------------------------------------------------------------------------
# READ and CAST data
# -----------------------------------------------------------------------------
cat("Reading data...\n")

cotacoes.dir <- "data/Historico_Cotacoes_CSV"
cotacoes.csvs <- list.files(cotacoes.dir)
ts.data <- NULL
for (csv.file in cotacoes.csvs){
  cat ("  ", csv.file, "\n")
  data <- read.csv(paste(cotacoes.dir, csv.file, sep = "/"), 
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
rm(data)

cat("Cast dataPregao to Date AND codisi to Character...\n")
ts.data$dataPregao <- as.Date(ts.data$dataPregao, "%Y%m%d")
ts.data$codisi <- as.character(ts.data$codisi)

cat("Select the Data with COD_BDI == 02 (Lote Padrão)...\n")
ts.data <- subset(ts.data, codbdi == "02")

cat("Read the Empresas and the ISINs per Empresa...\n")
emp.cols <- c("nome_empresa", "nome_pregao", "cod_negociacao", 
              "cod_cvm", "cnpj", "atividade_principal",  
              "setor", "sub_setor", "segmento", "site", "endereco", "cidade", "cep", 
              "estado", "telefone", "fax", "emails", "twitter_link", "facebook_link")
emp <- read.csv("data/DadosEmpresas.csv", header = F, 
                colClasses = rep("character", length(emp.cols)))
colnames(emp) <- emp.cols

emp.isin <- read.csv("data/DadosEmpresasISINs.csv", header = F, 
                     colClasses = rep("character", 2))
colnames(emp.isin) <- c("cnpj", "cod_isin")
emp.isin$cod_isin <- as.character(emp.isin$cod_isin)

# -----------------------------------------------------------------------------
# INNER JOIN the tables: EMPRESA - EMPRESA_ISIN - COTACAO
# -----------------------------------------------------------------------------
cat("Joining the tables (INNER JOIN!): EMPRESA - EMPRESA_ISIN - COTACAO...\n")

# First JOIN
emp.isin.full <- merge(emp, emp.isin, by="cnpj")
emp.isin.full <- emp.isin.full[,c("setor", "sub_setor", "segmento", "nome_empresa", 
                                  "nome_pregao", "cnpj", "cod_isin")]

# Second JOIN (COMPLETE DATABASE!)
emp.ts.joined <- merge(emp.isin.full, ts.data, by.x="cod_isin", by.y="codisi")
emp.ts.joined <- emp.ts.joined[,c("setor", "sub_setor", "segmento", "nome_empresa", 
                                  "nome_pregao", "cnpj", "cod_isin", "dataPregao", "premed")]

rm(emp, emp.isin, ts.data)

# -----------------------------------------------------------------------------
# Select the START and END DATE
# -----------------------------------------------------------------------------

# EMPRESA
cat("QUERY DATA: Empresa\n")
cat("  Selecting the Start and End Date of the queries per empresa...\n")
start.end.emp <- ddply(emp.ts.joined, .(cnpj), SelectStartEndDatePerIsin, .progress = "text")
start.end.emp <- start.end.emp[order(start.end.emp$nome_empresa, start.end.emp$query_start_date),]

cat(" Defining the query_string column...\n")
start.end.emp$query_string <- gsub(" e ", " ", 
                                   gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                        tolower(start.end.emp$nome_empresa)))

start.end.emp <- start.end.emp[,c("query_string", "query_start_date", "query_end_date",
                                    "nome_empresa", "nome_pregao", "cnpj", "setor", "sub_setor", "segmento")]

# SETOR
cat("QUERY DATA: Setor\n")
cat("  Selecting the Start and End Date of the queries per setor...\n")
group.col <- "setor"
start.end.setor <- ddply(start.end.emp, group.col, SelectStartEndDateFromEmp, .progress = "text")

cat(" Defining the query_string column...\n")
start.end.setor$query_string <- gsub(" e ", " ", 
                                     gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                          tolower(start.end.setor$setor)))

start.end.setor <- start.end.setor[,c("query_string", "query_start_date", "query_end_date", group.col)]

# SUB-SETOR
cat("QUERY DATA: Sub-Setor\n")
cat("  Selecting the Start and End Date of the queries per sub-setor...\n")
group.col <- "sub_setor"
start.end.subsetor <- ddply(start.end.emp, group.col, SelectStartEndDateFromEmp, .progress = "text")

cat(" Defining the query_string column...\n")
start.end.subsetor$query_string <- gsub(" e ", " ", 
                                        gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                             tolower(start.end.subsetor$sub_setor)))

start.end.subsetor <- start.end.subsetor[,c("query_string", "query_start_date", "query_end_date", group.col)]

# SEGMENTO
cat("QUERY DATA: Segmento\n")
cat("  Selecting the Start and End Date of the queries per segmento...\n")
group.col <- "segmento"
start.end.segmento <- ddply(start.end.emp, group.col, SelectStartEndDateFromEmp, .progress = "text")

cat(" Defining the query_string column...\n")
start.end.segmento$query_string <- gsub(" e ", " ", 
                                        gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                             tolower(start.end.segmento$segmento)))

start.end.segmento <- start.end.segmento[,c("query_string", "query_start_date", "query_end_date", group.col)]

# Remove the setor, sub_setor and segmento columns from start.end.emp
start.end.emp <- start.end.emp[, 1:(length(start.end.emp)-3)]

# -----------------------------------------------------------------------------
# Persisting Query data
# -----------------------------------------------------------------------------
cat("Persisting all Query data...\n")

news.dir <- "data/news"
dir.create(news.dir, showWarnings=F)
query.metadata.dir <- "data/news/query_metadata"
dir.create(query.metadata.dir, showWarnings=F)

write.table(start.end.emp, paste(query.metadata.dir, "/NewsQueryDataPerEmpresa.csv", sep = ""), sep=",", row.names = F, col.names = F)
write.table(start.end.setor, paste(query.metadata.dir, "/NewsQueryDataPerSetor.csv", sep = ""), sep=",", row.names = F, col.names = F)
write.table(start.end.subsetor, paste(query.metadata.dir, "/NewsQueryDataPerSubSetor.csv", sep = ""), sep=",", row.names = F, col.names = F)
write.table(start.end.segmento, paste(query.metadata.dir, "/NewsQueryDataPerSegmento.csv", sep = ""), sep=",", row.names = F, col.names = F)

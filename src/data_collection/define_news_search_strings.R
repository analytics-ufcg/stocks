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
for (csv.file in cotacoes.csvs[-c(1:18)]){
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

cat("QUERY DATA: Empresa\n")
cat("  Selecting the Start and End Date of the queries per empresa...\n")
start.end.all <- ddply(emp.ts.joined, .(cnpj), SelectStartEndDatePerIsin, .progress = "text")
start.end.emp <- start.end.all[order(start.end.all$nome_empresa, start.end.all$query_start_date),]

cat(" Defining the query_string column...\n")
start.end.emp$query_string <- gsub(" e ", " ", 
                                   gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                        tolower(start.end.emp$nome_empresa)))

start.end.emp <- start.end.emp[,c("query_start_date", "query_end_date", "query_string",
                                    "nome_empresa", "nome_pregao", "setor", "sub_setor", "segmento")]


cat("QUERY DATA: Setor\n")
cat("  Selecting the Start and End Date of the queries per setor...\n")
start.end.all <- ddply(emp.ts.joined, .(setor), SelectStartEndDatePerIsin, .progress = "text")
start.end.setor <- start.end.all[order(start.end.all$nome_empresa, start.end.all$query_start_date),]

cat(" Defining the query_string column...\n")
start.end.setor$query_string <- gsub(" e ", " ", 
                                     gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                          tolower(start.end.setor$setor)))


start.end.setor <- start.end.setor[,c("query_start_date", "query_end_date", "query_string", "setor")]



cat("QUERY DATA: Sub-Setor\n")
cat("  Selecting the Start and End Date of the queries per sub-setor...\n")
start.end.all <- ddply(emp.ts.joined, .(sub_setor), SelectStartEndDatePerIsin, .progress = "text")
start.end.subsetor <- start.end.all[order(start.end.all$nome_empresa, start.end.all$query_start_date),]

cat(" Defining the query_string column...\n")
start.end.subsetor$query_string <- gsub(" e ", " ", 
                                        gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                             tolower(start.end.subsetor$sub_setor)))

start.end.subsetor <- start.end.subsetor[,c("query_start_date", "query_end_date", "query_string", "sub_setor")]



cat("QUERY DATA: Segmento\n")
cat("  Selecting the Start and End Date of the queries per segmento...\n")
start.end.all <- ddply(emp.ts.joined, .(segmento), SelectStartEndDatePerIsin, .progress = "text")
start.end.segmento <- start.end.all[order(start.end.all$nome_empresa, start.end.all$query_start_date),]

cat(" Defining the query_string column...\n")
start.end.segmento$query_string <- gsub(" e ", " ", 
                                        gsub(" ltd.| s.a.| s/a|\\.|,", "", 
                                             tolower(start.end.segmento$segmento)))

start.end.segmento <- start.end.segmento[,c("query_start_date", "query_end_date", "query_string", "segmento")]

# -----------------------------------------------------------------------------
# Persisting Query data
# -----------------------------------------------------------------------------
cat("Persisting all Query data...\n")

news.dir <- "data/news_query"
dir.create(news.dir, showWarnings=F)

write.csv(start.end.emp, paste(news.dir, "/NewsQueryDataPerEmpresa.csv", sep = ""), row.names = F)
write.csv(start.end.setor, paste(news.dir, "/NewsQueryDataPerSetor.csv", sep = ""), row.names = F)
write.csv(start.end.subsetor, paste(news.dir, "/NewsQueryDataPerSubSetor.csv", sep = ""), row.names = F)
write.csv(start.end.segmento, paste(news.dir, "/NewsQueryDataPerSegmento.csv", sep = ""), row.names = F)

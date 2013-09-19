rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(lubridate)
library(plyr)
library(zoo)

# =============================================================================
# MAIN
# =============================================================================
cat("Reading data...\n")
ts.data <- NULL

cotacoes.dir <- "../../data/Historico_Cotacoes_CSV"
cotacoes.csvs <- list.files(cotacoes.dir)

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

# Cast dataPregao to date object
cat("Cast dataPregao to Date...\n")
ts.data$dataPregao <- as.Date(ts.data$dataPregao, "%Y%m%d")

# Select the cotacoes
cat("Select the Data with COD_BDI == 02 (Lote PadrÃ£o)...\n")
ts.data <- subset(ts.data, codbdi == "02")




cat("Read the Empresas and the ISINs per Empresa...\n")
emp <- read.csv("../../data/DadosEmpresas.csv", header = F)
colnames(emp) <- c("nome_empresa", "nome_pregao", "cod_negociacao", 
                   "cod_cvm", "cnpj", "atividade_principal",  
                   "setor", "sub_setor", "segmento", "site", "endereco", "cidade", "cep", 
                   "estado", "telefone", "fax", "emails", "twitter_link", "facebook_link")
emp.isin <- read.csv("../../data/DadosEmpresasISINs.csv", header = F)
colnames(emp.isin) <- c("cnpj", "cod_isin")




cat("Select the Empresa per Nome_pregao, their ISINs and the Preco-Medio Time-Series...\n")
emp.selecionadas.nome.segmento <- as.character(unique(emp$segmento))

emp.segmento <- subset(emp, segmento %in% emp.selecionadas.nome.segmento, c("segmento", "cnpj"))

segmentos.freq = count(emp.segmento,'segmento')

top.segmentos = arrange(segmentos.freq,desc(freq))[1:15,]

emp.isin.aux = data.frame(cnpj = as.character(emp.isin$cnpj),cod_isin = as.character(emp.isin$cod_isin))
emp.segmento.aux = data.frame(segmento =as.character(emp.segmento$segmento),cnpj = emp.segmento$cnpj)
emp.isin.selected <- merge(emp.isin.aux, emp.segmento.aux, by="cnpj", all.y = T)


final.ts <- merge(ts.data, emp.isin.selected, by.x="codisi", by.y="cod_isin", all.y=T)

final.ts <- final.ts[,c("nome_pregao", "cnpj", "codisi", "dataPregao", "premed")]
final.ts <- final.ts[order(final.ts$cnpj, final.ts$codisi, final.ts$dataPregao),]

selected.isins <- unique(emp.isin.selected$cod_isin)

cat("Selecting the largest time-series per empresa...\n")
emp.isin.selected <- ddply(emp.isin.selected, .(nome_pregao), function(df){
  largest.isin <- NA
  largest.size <- -1
  for (isin in df$cod_isin){
    tmp <- subset(final.ts, codisi == isin, "dataPregao")
    
    if (!is.na(tmp[1,])){
      diff.days <- tmp[nrow(tmp),] - tmp[1,]
      
      if (diff.days[[1]] > largest.size){
        largest.size <- diff.days[[1]]
        largest.isin <- isin
      }
    }
  }
  return(subset(emp.isin.selected, cod_isin == largest.isin))
})


cat ("Select the time-series intersection...\n")
final.interval <- subset(final.ts, codisi == selected.isins[1], c("dataPregao", "premed"))
final.interval <- zoo(final.interval$premed, final.interval$dataPregao)
final.interval <- zoo(,seq(start(final.interval), end(final.interval), by="day"))

print(emp.isin.selected[emp.isin.selected$cod_isin == selected.isins[1],])
cat("Interval size:", length(index(final.interval)), "\n\n")

for (one.isin in selected.isins[-1]){
  print(emp.isin.selected[emp.isin.selected$cod_isin == one.isin,])
  one.ts <- subset(final.ts, codisi == one.isin, c("dataPregao", "premed"))
  one.ts <- zoo(one.ts$premed, one.ts$dataPregao)
  one.ts.complete <- merge.zoo(one.ts, zoo(,seq(start(one.ts), end(one.ts), by="day")), all=TRUE)
  one.ts.complete <- na.locf(one.ts.complete)
  
  cat("Interval size:", length(index(final.interval)), "\n\n")
  if (length(index(final.interval)) == 0){
    cat("ERROR: Empty interval!\n")
    break;
  }
  intersect.ts <- merge(one.ts.complete, final.interval, all = F)
  final.interval <- zoo(,seq(start(intersect.ts), end(intersect.ts), by="day"))
}

ts.final.to.save <- NULL
for (isin in selected.isins){  
  empresa.ts <- subset(final.ts, 
                       codisi == isin & dataPregao %in% index(final.interval), 
                       c("nome_pregao", "premed", "dataPregao"))
  zoo.empresa.ts <- zoo(empresa.ts$premed, empresa.ts$dataPregao)
  empresa.ts.complete <- merge.zoo(zoo.empresa.ts, 
                                   zoo(,seq(start(final.interval), end(final.interval), by="day")), 
                                   all=TRUE)
  empresa.ts.complete <- na.locf(empresa.ts.complete)
  ts.final.to.save <- rbind(ts.final.to.save, 
                            data.frame(nome_pregao = rep(as.character(empresa.ts[1,"nome_pregao"]),
                                                         length(empresa.ts.complete)),
                                       preco_medio = empresa.ts.complete))
}

write.csv(ts.final.to.save, file = "data/Select10Empresas.csv", row.names = F)

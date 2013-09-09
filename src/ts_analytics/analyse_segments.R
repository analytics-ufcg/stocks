rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(lubridate)
library(plyr)
library(zoo)
library(dtw)

# =============================================================================
# MAIN
# =============================================================================
cat("Reading data...\n")
ts.data <- NULL

cotacoes.dir <- "data/Historico_Cotacoes_CSV"
cotacoes.csvs <- list.files(cotacoes.dir)


# -----------------------------------------------------------------------------
# READ and CAST data
# -----------------------------------------------------------------------------
# ATTENTION HERE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! REMOVE THE FILTER...
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
# Select the Empresas of the 15 biggest segmentos
# -----------------------------------------------------------------------------
cat("Select the Empresas of the following Segmentos:\n")
segmento.count <- count(emp, "segmento")
segmento.count <- segmento.count[order(segmento.count$freq, decreasing=T),]
print(segmento.count[1:15,])

# ATTENTION HERE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! REMOVE THE FILTER...
emp.small <- subset(emp, segmento %in% segmento.count$segmento[1:2], c("segmento", "nome_empresa", "cnpj"))



# -----------------------------------------------------------------------------
# INNER JOIN the tables: EMPRESA - EMPRESA_ISIN - COTACAO
# -----------------------------------------------------------------------------
cat("Joining the tables (INNER JOIN!): EMPRESA - EMPRESA_ISIN - COTACAO...\n")
emp.isin.selected <- merge(emp.isin, emp.small, by="cnpj", all.y = T)

selected.isins <- unique(emp.isin.selected$cod_isin)

joined.data <- merge(emp.isin.selected, ts.data, by.x="cod_isin", by.y="codisi")

joined.data <- joined.data[,c("segmento", "nome_empresa", "cnpj", "cod_isin", "dataPregao", "premed")]
joined.data <- joined.data[order(joined.data$cnpj, joined.data$cod_isi, joined.data$dataPregao),]


# -----------------------------------------------------------------------------
# Select the largest time-series per Empresa
# Analytic Assumption: The time-series of the same Empresas are high correlated
# -----------------------------------------------------------------------------

cat("Selecting the largest time-series per empresa...\n")
# Select the ISINs
emp.isin.selected <- ddply(emp.isin.selected, .(cnpj), function(df){
  largest.isin <- NA
  largest.size <- -1
  for (isin in df$cod_isin){
    tmp <- subset(joined.data, cod_isin == isin, "dataPregao")
    
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

# Select the TSs per ISIN
emp.data <- subset(joined.data, cod_isin %in% emp.isin.selected$cod_isin)

# -----------------------------------------------------------------------------
# Fill in the gaps of the time-series in a constant manner (repeating the last 
# non NA value)
# And ZNormalize them: Serie = (Serie - Mean(Serie))/StandDev(Serie)
# -----------------------------------------------------------------------------
cat("Fill the gaps of each time-series, keeping the last value (constant interpolation)\n")
cat("And Normalize the time-series...\n")

emp.ts.list <- dlply(emp.data, "cod_isin", function(df){
  one.isin <- df$cod_isin[1]
  
  # Merge the "gapped" TS with a complete TS by days
  one.ts <- zoo(df$premed, df$dataPregao)
  one.ts.complete <- merge.zoo(one.ts, 
                               zoo(, seq(start(one.ts), end(one.ts), by="day")), all=TRUE)
  
  # Fill the created gaps
  one.ts.complete <- na.locf(one.ts.complete)
  
  # Normalize the TS
  mean.ts <- mean(one.ts.complete, na.rm = T)
  sd.ts <- sd(one.ts.complete, na.rm = T)
  norm.one.ts.complete <- (one.ts.complete - mean.ts)/sd.ts
  
  return(list(segmento = df$segmento[1], 
              nome_empresa = df$nome_empresa[1],
              cnpj = df$cnpj[1], 
              cod_isin = df$cod_isin[1], 
              mean_ts = mean.ts, # Preserve the previous mean and sd of the time-series
              sd_ts = sd.ts,
#               preco_medio_ts = one.ts.complete,
              norm_preco_medio_ts = norm.one.ts.complete))
})

# -----------------------------------------------------------------------------
# Calculate the similarity metrics
# -----------------------------------------------------------------------------

# Observations:
# * The time-serie name is the empresa's name
# * Each time-serie is compared with the others in pairs, considering only the time
# both exist

emp.ts.distances <- ldply(emp.ts.list[1], function(emp.list, all.emps){
  result <- NULL
  emp.ts <- emp.list$norm_preco_medio_ts
  for(i in 1:length(all.emps)){
    other.ts <- emp.ts.list[[i]]$norm_preco_medio_ts
    
    other.ts.intersect <- merge(other.ts, zoo(,seq(start(emp.ts), end(emp.ts), by="day")), all = F)
    emp.ts.intersect <- window(emp.ts, start = start(other.ts.intersect), end = end(other.ts.intersect))

    emp.data <- as.vector(emp.ts.intersect)
    other.data <- as.vector(other.ts.intersect)
    
    ts.distance <- proxy::dist(rbind(emp.data, other.data), method="Euclidean")
    ts.similarity <- proxy::simil(rbind(emp.data, other.data), method="cosine")
    
    result <- rbind(result, data.frame(nome_empresa_A = emp.list$nome_empresa,
                                       nome_empresa_B = all.emps[[i]]$nome_empresa, 
                                       dist_euclidean = ts.distance[1],
                                       simil_cosine = ts.similarity[1]))
  }
  return(result)
}, .progress = "text", emp.ts.list)


# DTW - Dynamic Time Warping - Distance Metric
# library(dtw)
# dtw.align <- dtw(emp.list$norm_preco_medio_ts, all.emps[[i]]$norm_preco_medio_ts, 
#                  step.pattern=asymmetric, open.end=T,open.begin=T, distance.only=T)
# dtwPlotTwoWay(align)

# -----------------------------------------------------------------------------
# Plot the similarity visualizations 
# -----------------------------------------------------------------------------

# HEATMAP of SIMILARTY METRICs between TIME-SERIES
# CONFIDENCE INTERVAL of SIMILARTY METRIC between TIME-SERIES per SEGMENTO

# PLOT THE ALIGNMENT between the MOST SIMILAR time-series (excluding itself) and 
# the MOST DISTANT ones

# dtwPlotTwoWay(align)

# =============================================================================
# EXTRA! 
# Apenas depois que terminarmos acima (tirando todos os filtros) e tivermos 
# analisado e escrito no documento final
# =============================================================================

# -----------------------------------------------------------------------------
# Generate the KMeans and Hierarchical Clustering of the TSs
# -----------------------------------------------------------------------------
# Função: hclust (ver help)
# Função: kmeans (ver help)

# -----------------------------------------------------------------------------
# Plot the Clusters
# -----------------------------------------------------------------------------

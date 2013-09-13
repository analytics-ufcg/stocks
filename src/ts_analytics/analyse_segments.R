rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(lubridate)
library(plyr)
library(zoo)
library(ggplot2)
library(reshape2)

# =============================================================================
# FUNCTIONS
# =============================================================================
SelectLargestCotacaoPerCNPJ <- function(df){
  largest.isin <- NA
  largest.size <- -1
  for (isin in unique(df$cod_isin)){
    tmp <- subset(emp.ts.joined, cod_isin == isin, "dataPregao")
    
    if (!is.na(tmp[1,])){
      diff.days <- tmp[nrow(tmp),] - tmp[1,]
      
      if (diff.days[[1]] > largest.size){
        largest.size <- diff.days[[1]]
        largest.isin <- isin
      }
    }
  }
  return(subset(emp.isin.full, cod_isin == largest.isin))
}

FillSmoothReturnTheCotacao <- function(df, days.to.smooth){
  
  PriceToReturn <- function(serie){
    # Based on http://www.portfolioprobe.com/2010/10/04/a-tale-of-two-returns/
    diff(serie)/serie[-length(serie)]
  }
  
  one.isin <- df$cod_isin[1]
  
  # Merge the "gapped" TS with a complete TS by days
  one.ts <- zoo(df$premed, df$dataPregao)
  one.ts.complete <- merge.zoo(one.ts, 
                               zoo(, seq(start(one.ts), end(one.ts), by="day")), all=TRUE)
  
  # Fill the created gaps
  one.ts.complete <- na.locf(one.ts.complete)
  
  # Apply the Moving Average of 7 days to smooth the time-serie
  complete.ts <- one.ts.complete
  if (length(one.ts.complete) > days.to.smooth + 2){
    one.ts.complete <- rollmean(one.ts.complete, days.to.smooth)
  }
  
  # Price to Return 
  return.ts <- PriceToReturn(one.ts.complete)
  
  return(list(segmento = df$segmento[1], 
              nome_empresa = df$nome_empresa[1],
              nome_pregao = df$nome_pregao[1],
              cnpj = df$cnpj[1], 
              cod_isin = df$cod_isin[1], 
              preco_medio_ts = complete.ts,
              # smoothed_preco_medio_ts = one.ts.complete,
              return_ts = return.ts))
  
}

CalcReturnCorrelations <- function(emp.ts.list){
  emp.ts.distances <- NULL
  
  for (j in 1:length(emp.ts.list)){
    cat("    ", emp.ts.list[[j]]$nome_pregao, "\n")
    
    emp.ts <- emp.ts.list[[j]]$return_ts
    if (length(emp.ts) > 1){
      for(i in j:length(emp.ts.list)){
        other.ts <- emp.ts.list[[i]]$return_ts
        
        # The intersection can be NULL (so the distance cannot be defined = NA)
        other.ts.intersect <- merge(other.ts, zoo(,seq(start(emp.ts), end(emp.ts), by="day")), all = F)
        emp.ts.intersect <- window(emp.ts, start = start(other.ts.intersect), end = end(other.ts.intersect))
        
        # Pearson Correlation
        if (length(other.ts.intersect) == length(emp.ts.intersect)){
          ts.correlation <- cor(emp.ts.intersect, other.ts.intersect, method="pearson")
        }else{
          ts.correlation <- NA
        }
        
        emp.ts.distances <- rbind(emp.ts.distances, data.frame(nome_pregao_A = emp.ts.list[[j]]$nome_pregao,
                                                               nome_pregao_B = emp.ts.list[[i]]$nome_pregao, 
                                                               correlation = ts.correlation))
      }
    }
  }
  return(emp.ts.distances)
}

CalcCorrelationsPerSegment <- function(seg){
  cat("\nSegmento:", seg, "\n")
  
  is.segment <- sapply(emp.ts.list, function(x){
    return(x$segmento == seg)
  })
  return.correlations <- CalcReturnCorrelations(emp.ts.list[is.segment])
  return(cbind(data.frame(segmento = rep(seg, nrow(return.correlations))), return.correlations))
}

# =============================================================================
# MAIN
# =============================================================================
# ATTENTION: THIS SCRIPT RAISES SOME WARNINGS, PLEASE DISCONSIDER IT.

cat("============== CORRELATION ANALYSIS of COTACOES per SEGMENTO ==============\n")

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
emp.isin.full <- emp.isin.full[,c("segmento", "nome_empresa", "nome_pregao", "cnpj", "cod_isin")]

# Second JOIN (COMPLETE DATABASE!)
emp.ts.joined <- merge(emp.isin.full, ts.data, by.x="cod_isin", by.y="codisi")
emp.ts.joined <- emp.ts.joined[,c("segmento", "nome_empresa", "nome_pregao", "cnpj", "cod_isin", "dataPregao", "premed")]

rm(emp, emp.isin, ts.data)

# -----------------------------------------------------------------------------
# Select the Empresas of the 15 biggest segmentos
# -----------------------------------------------------------------------------
cat("Select the Empresas of the following Segmentos:\n")

emp.segmento <- emp.ts.joined[,c("segmento", "cnpj")]
emp.segmento <- emp.segmento[! duplicated(emp.segmento),]
segmento.count <- count(emp.segmento, "segmento")
segmento.count <- segmento.count[order(segmento.count$freq, decreasing=T),]
print(segmento.count[1:15,])

# Select the joined data
emp.isin.full <- subset(emp.isin.full, segmento %in% segmento.count$segmento[1:15])
emp.ts.joined <- subset(emp.ts.joined, segmento %in% segmento.count$segmento[1:15])

# Order the data
emp.isin.full <- emp.isin.full[order(emp.isin.full$segmento, emp.isin.full$nome_pregao),]
emp.ts.joined <- emp.ts.joined[order(emp.ts.joined$cnpj, emp.ts.joined$cod_isi, emp.ts.joined$dataPregao),]

rm(emp.segmento)

# -----------------------------------------------------------------------------
# Select the largest time-series per Empresa
# Analytic Assumption: The time-series of the same Empresas are high correlated
# -----------------------------------------------------------------------------

cat("Selecting the largest time-series per empresa...\n")
# Select the ISINs
emp.isin.full <- ddply(emp.isin.full, .(cnpj), SelectLargestCotacaoPerCNPJ, 
                       .progress = "text")

# Select the TS data
emp.ts.joined <- subset(emp.ts.joined, cod_isin %in% emp.isin.full$cod_isin)

# -----------------------------------------------------------------------------
# Fill in the gaps of the time-series in a constant manner (repeating the last 
# non NA value)
# Smooth it with a moving average of 7 days
# Calculate the Return
# -----------------------------------------------------------------------------
cat("Fill the gaps of each time-series, keeping the last value (constant interpolation)...\n")
cat("Smooth the time-series with Moving Average of 7 days...\n")
cat("Calculate the Return...\n")
days.to.smooth <- 7
emp.ts.list <- dlply(emp.ts.joined, "cod_isin", FillSmoothReturnTheCotacao, days.to.smooth)

# -----------------------------------------------------------------------------
# Calculate the similarity metrics
# -----------------------------------------------------------------------------
cat("Calculate the Correlation of the Time-Series per Segment...\n")
# Observations:
# * The time-serie name is the nome_pregao
# * Each time-serie is compared with the others in pairs, considering only the time
# both exist

seg.dists <- adply(unique(emp.ts.joined$segmento), 1, CalcCorrelationsPerSegment, 
                   .progress = "text")

# -----------------------------------------------------------------------------
# Plot the similarity visualizations 
# -----------------------------------------------------------------------------
output.dir <- "data/time_series/segment_analysis"
dir.create(output.dir, showWarnings=F)

theme_set(theme_bw())

# BOXPLOT of CORRELATION between SEGMENTOs
cat("Plotting the BOXPLOT of the CORRELATION between SEGMENTOs...\n")
seg.dists.filtered <- seg.dists[!is.na(seg.dists$correlation) & 
                 as.character(seg.dists$nome_pregao_A) != as.character(seg.dists$nome_pregao_B),]

pdf(paste(output.dir, 
          "/US 16 - Figura 1 - Boxplot da Correlação entre Pares de Cotações (Retorno) por Segmento.pdf", sep =""), 
    width = 15, height = 9)

print(ggplot(seg.dists, aes(x = segmento, y = correlation)) + 
  geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
  ylab("pearson correlation") + theme(legend.position="none") +
  theme(axis.text.x = element_text(angle = 25, hjust = 1)))

print(ggplot(seg.dists.filtered, aes(x = segmento, y = correlation)) + 
  geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
  ylab("cosine similarity (filtered)") + theme(legend.position="none") +
  theme(axis.text.x = element_text(angle = 25, hjust = 1)))

print(ggplot(seg.dists.filtered, aes(x = segmento, y = abs(correlation))) + 
        geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
        ylab("abs(cosine similarity) (filtered)") + theme(legend.position="none") +
        theme(axis.text.x = element_text(angle = 25, hjust = 1)))

dev.off()

# HEATMAP of SIMILARITY between the TIME-SERIES per SEGMENT
cat("Plotting the HEATMAP of the PEARSON CORRELATION between the RETURN TIME-SERIES per SEGMENT...\n")
pdf(paste(output.dir, 
          "/US 16 - Figura 2 - Heatmap da Correlação entre Cotações (Retorno) por Segmento.pdf", sep =""), 
    width = 20, height = 21)

d_ply(seg.dists, .(segmento), function(seg.dist){
  seg <- seg.dist$segmento[1]
  print(ggplot(seg.dist, aes(x = nome_pregao_A, y = nome_pregao_B)) + 
          geom_tile(aes(fill = abs(correlation)), color = gray) + 
          geom_text(aes(fill = correlation, label = round(correlation, 2)), 
                    colour = "grey25", size = 3) + 
          scale_fill_gradient(low = "white", high = "red") + 
          labs(title = seg) + 
          theme(axis.ticks = element_blank(), legend.position="none",
                axis.text.x = element_text(angle = 45, hjust = 1)))
  
}, .progress = "text")

dev.off()

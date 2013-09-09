rm(list = ls())

# =============================================================================
# SOURCE() and LIBRARY()
# =============================================================================
library(lubridate)
library(plyr)
library(zoo)
# library(dtw)
library(ggplot2)
library(reshape2)

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
# Select the Empresas of the 15 biggest segmentos
# -----------------------------------------------------------------------------
cat("Select the Empresas of the following Segmentos:\n")
segmento.count <- count(emp, "segmento")
segmento.count <- segmento.count[order(segmento.count$freq, decreasing=T),]
print(segmento.count[1:15,])

emp.small <- subset(emp, segmento %in% segmento.count$segmento[1:15], 
                    c("segmento", "nome_empresa", "nome_pregao", "cnpj"))

# -----------------------------------------------------------------------------
# INNER JOIN the tables: EMPRESA - EMPRESA_ISIN - COTACAO
# -----------------------------------------------------------------------------
cat("Joining the tables (INNER JOIN!): EMPRESA - EMPRESA_ISIN - COTACAO...\n")
emp.isin.selected <- merge(emp.isin, emp.small, by="cnpj", all.y = T)

selected.isins <- unique(emp.isin.selected$cod_isin)

joined.data <- merge(emp.isin.selected, ts.data, by.x="cod_isin", by.y="codisi")

joined.data <- joined.data[,c("segmento", "nome_empresa", "nome_pregao", "cnpj", "cod_isin", "dataPregao", "premed")]
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

# Print the final count of empresas per segmento
cat("Segmento Count after table joins:\n")
tmp <- emp.data[, 1:3]
tmp <- tmp[!duplicated(tmp),]
tmp.count <- count(tmp, "segmento")
tmp.count <- tmp.count[order(tmp.count$freq, decreasing = T),]
print(tmp.count)

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
              nome_pregao = df$nome_pregao[1],
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
cat("Calculate the Distance/Similarity of the Time-Series per Segment...\n")
# Observations:
# * The time-serie name is the empresa's name
# * Each time-serie is compared with the others in pairs, considering only the time
# both exist

GetTsDistances <- function(emp.ts.list){
  emp.ts.distances <- NULL
  
  for (j in 1:length(emp.ts.list)){
    cat("    ", emp.ts.list[[j]]$nome_pregao, "\n")
    
    emp.ts <- emp.ts.list[[j]]$norm_preco_medio_ts
    for(i in j:length(emp.ts.list)){
      other.ts <- emp.ts.list[[i]]$norm_preco_medio_ts
      
      # The intersection can be NULL (so the distance cannot be defined = NA)
      other.ts.intersect <- merge(other.ts, zoo(,seq(start(emp.ts), end(emp.ts), by="day")), all = F)
      emp.ts.intersect <- window(emp.ts, start = start(other.ts.intersect), end = end(other.ts.intersect))
      
      emp.data <- as.vector(emp.ts.intersect)
      other.data <- as.vector(other.ts.intersect)
      
      ts.distance <- proxy::dist(rbind(emp.data, other.data), method="Euclidean")
      ts.similarity <- proxy::simil(rbind(emp.data, other.data), method="cosine")
      
      emp.ts.distances <- rbind(emp.ts.distances, data.frame(nome_pregao_A = emp.ts.list[[j]]$nome_pregao,
                                                             nome_pregao_B = emp.ts.list[[i]]$nome_pregao, 
                                                             # nome_empresa_A = emp.ts.list[[j]]$nome_empresa,
                                                             # nome_empresa_B = emp.ts.list[[i]]$nome_empresa, 
                                                             dist_euclidean = ts.distance[1],
                                                             simil_cosine = ts.similarity[1]))
    }
  }
  return(emp.ts.distances)
}

segments <- unique(emp.data$segmento)
seg.dists <- adply(segments, 1, function(seg){
  cat("\nSegmento:", seg, "\n")
  
  is.segment <- sapply(emp.ts.list, function(x){
    return(x$segmento == seg)
  })
  distances <- GetTsDistances(emp.ts.list[is.segment])
  return(cbind(data.frame(segmento = rep(seg, nrow(distances))), distances))
}, .progress = "text")


# seg.dists <- merge(seg.dists, segmento.count[1:15,], by = "segmento")
# seg.dists <- seg.dists[order(seg.dists$freq, decreasing=T),]
# seg.dists <- subset(seg.dists, select=-c(X1,freq))
# 
# seg.dists$segmento <- factor(seg.dists$segmento, levels=segmento.count[1:15,"segmento"])

# -----------------------------------------------------------------------------
# Plot the similarity visualizations 
# -----------------------------------------------------------------------------
output.dir <- "data/time_series/segment_analysis"
dir.create(output.dir, showWarnings=F)

theme_set(theme_bw())

# HEATMAP of SIMILARITY between the TIME-SERIES per SEGMENT
cat("Plotting the HEATMAP of SIMILARITY between the TIME-SERIES per SEGMENT...\n")
pdf(paste(output.dir, "/heatmap_segments_ts_similarity.pdf", sep =""), width = 20, height = 21)

d_ply(seg.dists, .(segmento), function(seg.dist){
  seg <- seg.dist$segmento[1]
  print(ggplot(seg.dist, aes(x = nome_pregao_A, y = nome_pregao_B)) + 
          geom_tile(aes(fill = abs(simil_cosine)), color = gray) + 
          geom_text(aes(fill = simil_cosine, label = round(simil_cosine, 2)), 
                    colour = "grey25", size = 3) + 
          scale_fill_gradient(low = "white", high = "red") + 
          labs(title = seg) + 
          theme(axis.ticks = element_blank(), legend.position="none",
                axis.text.x = element_text(angle = 45, hjust = 1)))
  
}, .progress = "text")

dev.off()


# HEATMAP of DISTANCE between the TIME-SERIES per SEGMENT
cat("Plotting the HEATMAP of DISTANCE between the TIME-SERIES per SEGMENT...\n")
pdf(paste(output.dir, "/heatmap_segments_ts_distances.pdf", sep =""), width = 20, height = 21)

d_ply(seg.dists, .(segmento), function(seg.dist){
  seg <- seg.dist$segmento[1]
  print(ggplot(seg.dist, aes(x = nome_pregao_A, y = nome_pregao_B)) + 
          geom_tile(aes(fill = dist_euclidean), color = gray) + 
          geom_text(aes(fill = dist_euclidean, label = round(dist_euclidean, 2)), 
                    colour = "grey25", size = 3) + 
          scale_fill_gradient(low = "red", high = "white") + 
          labs(title = seg) + 
          theme(axis.ticks = element_blank(), legend.position="none",
                axis.text.x = element_text(angle = 45, hjust = 1)))
  
}, .progress = "text")

dev.off()


# BOXPLOT of SIMILARTY METRIC between TIME-SERIES per SEGMENTO
cat("Plotting the BOXPLOT of SIMILARTY METRIC between TIME-SERIES per SEGMENTO..\n")
pdf(paste(output.dir, "/boxplot_segments.pdf", sep =""), width = 15, height = 9)
print(ggplot(seg.dists, aes(x = segmento, y = simil_cosine)) + 
 geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
  ylab("cosine similarity") + theme(legend.position="none") +
  theme(axis.text.x = element_text(angle = 25, hjust = 1)))
print(ggplot(seg.dists, aes(x = segmento, y = dist_euclidean)) + 
  geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
  ylab("euclidean distance") + theme(legend.position="none")+
  theme(axis.text.x = element_text(angle = 25, hjust = 1)))
dev.off()

# BOXPLOT of SIMILARTY METRIC between TIME-SERIES per SEGMENTO
cat("Plotting the BOXPLOT of DISTANCE METRIC between TIME-SERIES per SEGMENTO..\n")
# Remove the NAs and the 1 values
seg.dists.filtered <- seg.dists[!is.na(seg.dists$dist_euclidean) & seg.dists$dist_euclidean != 0,]

pdf(paste(output.dir, "/boxplot_segments_filtered.pdf", sep =""), width = 15, height = 9)
print(ggplot(seg.dists.filtered, aes(x = segmento, y = simil_cosine)) + 
  geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
  ylab("cosine similarity") + theme(legend.position="none") +
  theme(axis.text.x = element_text(angle = 25, hjust = 1)))
print(ggplot(seg.dists.filtered, aes(x = segmento, y = dist_euclidean)) + 
  geom_boxplot() + geom_jitter(aes(col = segmento), alpha = .4) + 
  ylab("euclidean distance") + theme(legend.position="none")+
  theme(axis.text.x = element_text(angle = 25, hjust = 1)))
dev.off()


# -----------------------------------------------------------------------------
# Generate the KMeans and Hierarchical Clustering of the TSs
# -----------------------------------------------------------------------------
# seg.dists.no.na <- seg.dists
# seg.dists.no.na[is.na(seg.dists.no.na)] <- 1000
# 
# seg.dist.matrix <- acast(seg.dists.no.na, nome_pregao_A~nome_pregao_B, value.var="dist_euclidean")
# seg.dist.matrix[is.na(seg.dist.matrix)] <- 0
# 
# seg.dist.matrix.full <- seg.dist.matrix + t(seg.dist.matrix)
# 
# # Função: hclust
# # hc <- hclust(seg.dist.matrix.full, method = "average")
# 
# # Função: kmeans (ver help)
# km <- kmeans(seg.dist.matrix.full, centers=10)


# -----------------------------------------------------------------------------
# Plot the Clusters
# -----------------------------------------------------------------------------



# =============================================================================
# EXTRA! 
# Apenas depois que terminarmos acima (tirando todos os filtros) e tivermos 
# analisado e escrito no documento final
# =============================================================================
# PLOT THE ALIGNMENT between the MOST SIMILAR time-series (excluding itself) and 
# the MOST DISTANT ones
# DTW - Dynamic Time Warping - Distance Metric
# library(dtw)
# dtw.align <- dtw(emp.ts.list$norm_preco_medio_ts, emp.ts.list[[i]]$norm_preco_medio_ts, 
#                  step.pattern=asymmetric, open.end=T,open.begin=T, distance.only=T)
# dtwPlotTwoWay(align)
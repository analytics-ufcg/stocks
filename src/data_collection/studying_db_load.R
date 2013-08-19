rm(list = ls())

cols <- c("nome_empresa", "nome_pregao", "cod_negociacao", 
          "cod_cvm", "cnpj", "atividade_principal",  
          "setor", "sub_setor", "segmento", "site", "rua", "cidade", "cep", 
          "estado", "telefone", "fax", "nomes", "emails")

emp <- read.csv("data/DadosEmpresas.csv", header = F, col.names=cols, 
                colClasses = rep("character", length(cols)))

# MAX COL SIZES
cat("MAX sizes per column:\n")
for(col in cols){
  cat(" ", col, ":", max(nchar(emp[,col])), "\n")
}
cat("\n")

# PRINT CLASSIFICACAOs
all.setor <- unique(emp$setor)
all.subsetor <- unique(emp$sub_setor)
all.segmento <- unique(emp$segmento)

cat("All Setores:\n")
print(all.setor)
cat("\n")

cat("All Sub-Setores:\n")
print(all.subsetor)
cat("\n")

cat("All Segmentos:\n")
print(all.segmento)
cat("\n")
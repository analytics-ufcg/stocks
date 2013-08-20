path = "."
data = read.table("../DadosEmpresas.csv",sep = ",",header = FALSE)
#read the images file of the given path
file.names = list.files(path)

for(i in file.names){
  
  maneger.code = unlist(strsplit(i,"[.]"))[1]
  print(maneger.code)
  for(j in 1:length(data$V4)){
    
    maneger.code.list = unlist(strsplit(as.character(data$V4[j]),","))
    if(maneger.code %in% maneger.code.list){
      new.name = paste(data$V6[j],".jpg",sep="")
      #rewrite the files, saving at same directory
      file.rename(i,new.name)
      
    }
  }
}
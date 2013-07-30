import os.path
import zipfile
import glob
import csv

'''
    Classe que faz a conversao dos tipos de cada coluna e retorna a lista de 
    colunas na ordem esperada.
'''

class CotacaoDiaria():
    def __init__(self, dataPregao, codbdi, codneg, tpmerc, nomres, especi, prazot,
                 modref, preabe, premax, premin, premed, preult, preofc, preofv,
                 totneg, quatot, voltot, preexe, indopc, datven, fatcot, ptoexe,
                 codisi, dismes):
        self.dataPregao = dataPregao 
        self.codbdi = codbdi
        self.codneg = codneg
        self.tpmerc = int(tpmerc)
        self.nomres = nomres 
        self.especi = especi
        self.prazot = prazot 
        self.modref = modref 
        self.preabe = float(preabe) / 100
        self.premax = float(premax) / 100 
        self.premin = float(premin) / 100 
        self.premed = float(premed) / 100 
        self.preult = float(preult) / 100 
        self.preofc = float(preofc) / 100 
        self.preofv = float(preofv) / 100 
        self.totneg = int(totneg) 
        self.quatot = int(quatot) 
        self.voltot = float(voltot) / 100
        self.preexe = float(preexe) / 100 
        self.indopc = int(indopc)
        self.datven = datven
        self.fatcot = int(fatcot) 
        self.ptoexe = float(ptoexe) / 1000000
        self.codisi = codisi
        self.dismes = dismes
        
    def getList(self):
        return [self.dataPregao, self.codbdi, self.codneg, self.tpmerc, self.nomres, self.especi, self.prazot,
                self.modref, self.preabe, self.premax, self.premin, self.premed, self.preult, self.preofc, self.preofv,
                self.totneg, self.quatot, self.voltot, self.preexe, self.indopc, self.datven, self.fatcot, self.ptoexe,
                self.codisi, self.dismes]

def parseCotacaoDiaria(row):
    cotacaoDiaria = CotacaoDiaria(row[2:10], row[10:12], row[12:24], row[24:27], row[27:39],
            row[39:49], row[49:52], row[52:56], row[56:69], row[69:82], row[82:95],
            row[95:108], row[108:121], row[121:134], row[134:147], row[147:152],
            row[152:170], row[170:188], row[188:201], row[201:202], row[202:210],
            row[210:217], row[217:230], row[230:242], row[242:245])

    return cotacaoDiaria.getList()

'''
    Main que leh os arquivos em UTF-8, realiza a conversao de tipos e gera os 
    arquivos CSV prontos para serem carregados para o banco de dados
'''
if __name__ == "__main__":
    
    myPath = os.path.dirname(os.path.abspath(__file__))
    dataDir = myPath + "/../../data"
    rawStockDataDir = dataDir + "/Historico_Cotacoes_UTF8"
    csvStockDataDir = dataDir + "/Historico_Cotacoes_CSV"
    
    stockFiles = glob.glob(rawStockDataDir + "/COTAHIST*")
    print "================== Parser do Historico de Cotacoes para CSV =================="
    print "Existem " + str(len(stockFiles)) + " anos de dados..."
     
    for stockFile in stockFiles:
         
        year = stockFile[-8:-4]
        print "  " + year
        
        if not os.path.exists(csvStockDataDir):
            os.makedirs(csvStockDataDir)
        
        stockYearCsv = csvStockDataDir + "/cotacoes_" + year + ".csv"
        
        with open(stockYearCsv, 'w') as csvfile:
            stockWriter = csv.writer(csvfile, delimiter=',', quotechar='"', quoting=csv.QUOTE_NONNUMERIC)
        
            with open(stockFile, "rb") as file:
                file.readline()
                for row in file:
                    rowType = row[0:2]
                    if (rowType == "00"):
                        pass
                    elif (rowType == "01"):
                        stockWriter.writerow(parseCotacaoDiaria(row))
                    elif (rowType == "99"):
                        pass
                    else:
                        print "Erro: linha de tipo indefinido(" + row + ")!"
 

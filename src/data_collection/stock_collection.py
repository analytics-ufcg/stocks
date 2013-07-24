import os.path
import zipfile
import glob
import csv

class Header():
    def __init__(self, filename, sourceCode, fileCreationDate, booking):
        self.filename = filename
        self.sourceCode = sourceCode
        self.fileCreationDate = fileCreationDate
        self.booking = booking

class DailyStock():
    def __init__(self, tradeDate, codbdi, codneg, tpmerc, nomres, especi, prazot,
                 modref, preabe, premax, premin, premed, preult, preofc, preofv,
                 totneg, quatot, voltot, preexe, indopc, datven, fatcot, ptoexe,
                 codisi, dismes):
        self.tradeDate = tradeDate 
        self.codbdi = codbdi 
        self.codneg = codneg
        self.tpmerc = tpmerc 
        self.nomerc = nomres 
        self.especi = especi
        self.prazot = prazot 
        self.modref = modref 
        self.preabe = preabe
        self.premax = premax 
        self.premin = premin 
        self.premed = premed 
        self.preult = preult 
        self.preofc = preofc 
        self.preofv = preofv 
        self.totneg = totneg 
        self.quatot = quatot 
        self.voltot = voltot 
        self.preexe = preexe 
        self.indopc = indopc 
        self.datven = datven 
        self.fatcot = fatcot 
        self.ptoexe = ptoexe  
        self.codisi = codisi 
        self.dismes = dismes
        self.listAll = [tradeDate, codbdi, codneg, tpmerc, nomres, especi, prazot,
                 modref, preabe, premax, premin, premed, preult, preofc, preofv,
                 totneg, quatot, voltot, preexe, indopc, datven, fatcot, ptoexe,
                 codisi, dismes]

class Trailer():
    def __init__(self, filename, sourceCode, fileCreationDate, totalRegisters, booking):
        self.filename = filename
        self.sourceCode = sourceCode 
        self.fileCreationDate = fileCreationDate
        self.totalRegisters = totalRegisters
        self.booking = booking
    

def parseHeader(row):
    return [row[2:15], row[15:23], row[23:31], row[31:245]]

def parseDailyStock(row):
    return [row[2:10], row[10:12], row[12:24], row[24:27], row[27:39],
            row[39:49], row[49:52], row[52:56], row[56:69], row[69:82], row[82:95],
            row[95:108], row[108:121], row[121:134], row[134:147], row[147:152],
            row[152:170], row[170:188], row[188:201], row[201:202], row[202:210],
            row[210:217], row[217:230], row[230:242], row[242:245]]

def parseTrailer(row):
    return [row[2:15], row[15:23], row[23:31], row[31:42], row[42:245]]

if __name__ == "__main__":
    
    headerList = []
    dailyStockList = []
    trailerList = []
    
    myPath = os.path.dirname(os.path.abspath(__file__))
    dataDir = myPath + "/../../data"
    rawStockDataDir = dataDir + "/Historico_Cotacoes_UTF8"
    csvStockDataDir = dataDir + "/Stock_History_CSV"
    
    stockFiles = glob.glob(rawStockDataDir + "/COTAHIST*")
    print "================== STOCK History Parser to CSV =================="
    print "There are " + len(stockFiles) + " years to parse..."
     
    for stockFile in stockFiles:
         
        year = stockFile[-8:-4]
        print "  " + year
        
        if not os.path.exists(csvStockDataDir):
            os.makedirs(csvStockDataDir)
        
        stockYearCsv = csvStockDataDir + "/stock_" + year + ".csv"
        
        with open(stockYearCsv, 'w') as csvfile:
            stockWriter = csv.writer(csvfile, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
        
            with open(stockFile, "rb") as file:
                file.readline()
                for row in file:
                    rowType = row[0:2]
                    if (rowType == "00"):
                        pass
                    elif (rowType == "01"):
                        stockWriter.writerow(parseDailyStock(row))
                    elif (rowType == "99"):
                        pass
                    else:
                        print "Error: nonexistent row type(" + row + ")!"
 

class Header():
    def __init__(self, filename, sourceCode, fileCreationDate, booking):
        self.filename = filename
        self.sourceCode = sourceCode
        self.fileCreationDate = fileCreationDate
        self.booking = booking

class DailyStock():
    def __init__(self, tradeDate, codbdi, codneg, tpmerc, nomerc, especi, prazot, modref, preabe, premax, premin, premed, preult, preofc, preofv, totneg, quatot, voltot, preexe, indopc, datven, fatcot, ptoexe, codisi, dismes):
        self.tradeDate = tradeDate 
        self.codbdi = codbdi 
        self.codneg = codneg
        self.tpmerc = tpmerc 
        self.nomerc = nomerc 
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

class Trailer():
    def __init__(self, filename, sourceCode, fileCreationDate, totalRegisters, booking):
        self.filename = filename
        self.sourceCode = sourceCode 
        self.fileCreationDate = fileCreationDate
        self.totalRegisters = totalRegisters
        self.booking = booking
    

def parseHeader(row):
    return Header(row[2,14], row[15,22], row[23, 30], row[31, 244])

def parseDailyStock(row):
    return DailyStock(row[2:9], row[10:11], row[12:23], row[24:26], row[27:38], 
                      row[39:48], row[49:51], row[52:55], row[56:68], row[69:81], row[82:94], 
                      row[95:107], row[108:120], row[121:133], row[134:146], row[147:151], 
                      row[152:169], row[170:187], row[188:200], row[201:201], row[202:209], 
                      row[210:216], row[217:229], row[230:241], row[242:245])

def parseTrailer(row):
    return Trailer(row[2:14], row[15:22], row[23:30], row[31:41], row[42:244])

if __name__ == "__main__":
    
    headerList = []
    dailyStockList = []
    trailerList = []
    
    # READ YEAR STOCK FILE
    # SAVE THE YEAR

    # FOR EACH ROW
    row = ""
    rowType = row[0:1]
    if (rowType == "00"):
        headerList.append(parseHeader(row))
    elif (rowType == "01"):
        dailyStockList.append(parseDailyStock(row))
    elif (rowType == "99"):
        trailerList.append(parseTrailer(row))
    else:
        print "Error: nonexistent row type!"
        # ERROR
        
    
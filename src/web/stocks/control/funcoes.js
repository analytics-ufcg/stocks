
function barra(objeto){
	if (objeto.value.length == 2 || objeto.value.length == 5 ){
		objeto.value = objeto.value+"/";
	}
}

function isValidDate(s, saida) {
  
  var bits = s.split('/');
  if(bits[0].length != 2 || bits[1].length != 2 || bits[2].length != 4)
  {
  	alert("Data " + saida + " invalida");
  	return false;
  }
  var d = new Date(bits[2], bits[1] - 1, bits[0]);
  ehValido = (d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]) );
  if(!ehValido){alert("Data " + saida + " invalida");}
  return ehValido;
} 

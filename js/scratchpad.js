/*
 * Ceci est une ardoise JavaScript.
 *
 * Saisissez du code JavaScript, puis faites un clic droit ou sélectionnez à partir du menu Exécuter :
 * 1. Exécuter pour évaluer le texte sélectionné (Ctrl+R),
 * 2. Examiner pour mettre en place un objet Inspector sur le résultat (Ctrl+I), ou,
 * 3. Afficher pour insérer le résultat dans un commentaire après la sélection. (Ctrl+L)
 */

var text = "Salut l/*e/* monde!"
var stack = []

var res = ''
for(var i = 0; i < text.length; ++i) {
  var char = text[i]
  char.index = i
  
  if(char == '/' || char == '*') {
    state = stack.pop()
    
    if(state == char) {
      if(char == '/')
        res += '</em>'
        
      else if(char == '*')
        res += '</strong>'
    } else {
      stack.push(char)
    }
  } else
    res += char
}
  
stack
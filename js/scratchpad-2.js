/*
 * Ceci est une ardoise JavaScript.
 *
 * Saisissez du code JavaScript, puis faites un clic droit ou sélectionnez à partir du menu Exécuter :
 * 1. Exécuter pour évaluer le texte sélectionné (Ctrl+R),
 * 2. Examiner pour mettre en place un objet Inspector sur le résultat (Ctrl+I), ou,
 * 3. Afficher pour insérer le résultat dans un commentaire après la sélection. (Ctrl+L)
 */

var State = {
  NONE : '',
  BOLD : '*',
  ITALIC : '/'
}

var text = 'B*on*j/our* *to*ut/ /le* /mo*nde !'
var tree = {
  state : State.NONE,
  parent : null,
  text : ''
}

function parser(text, offset = 0, tree = [], opentag = null) {
  var state = ''
  var from = -1
  var length = 0
  for(var i = 0; i < text.length; ++i, ++length) {
    var c = text[i]
    if(c == '/' || c == '*') {
      if(!state || c == state) {
        if(length > 0)
          tree.push({state : state, text : parser(text.substr(from+1, length))})
        from = i
        length = -1

        state = (!state) ? c : ''
      }
    }
  }
  
  if(length > 0) {
    if(!state)
      tree.push({state : state, text : text.substr(from+1, length)})
    else
      tree[tree.length-1].text += state + text.substr(from+1, length)
  }
  
  if(tree.length == 1)
    tree = tree[0].text
  
  return tree
}

parser(text)
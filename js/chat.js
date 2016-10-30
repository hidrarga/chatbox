WEB_SOCKET_SWF_LOCATION = "bower_components/web-socket-js/WebSocketMain.swf"
WEB_SOCKET_DEBUG = true

var HOSTNAME = 'localhost'
var PORT = '8080'

String.prototype.pad = function(pad) {
  return pad.substring(this.length) + this
}

var DateUtils = {
  'time' : function(date) {
    var time = [date.getHours(), date.getMinutes(), date.getSeconds()]
    
    for(var i = 0; i < 3; ++i)
      time[i] = time[i].toString().pad('00')
            
    return time.join(':')
  },
  'date' : function(date) {
    var date = [date.getDate(), date.getMonth()+1, date.getYear()%100]
    
    for(var i = 0; i < 3; ++i)
      date[i] = date[i].toString().pad('00')
        
    return date.join('/')
  }
}


function onOpen() {
  $('#cb_messages').prepend('<div class="system info">'+_('connection-opened')+'</div>')
}

var old_date = null
function printDate(date) {
  if(!old_date || date != old_date) {
    if(old_date)
      $('#cb_messages').prepend('<hr /><date class="date">' + old_date + '</date>')
    
    old_date = date
  }
}

function markup(text) {

}

function render(data) {
  var datetime = new Date(data.time * 1000)
  var time = DateUtils.time(datetime)
  var date = DateUtils.date(datetime)
  
  printDate(date)
  
  $('#cb_messages').prepend('<p class="user"><span class="name" style="color: #'+ data.color + ';">' + data.name + '&nbsp</span><span class="message"><date>&nbsp' + time + '</date>' + data.message + '</span></p>')
}

function logs(data) {
  for(var i = 0; i < data.message.length; ++i)
    Service[data.message[i].type](data.message[i]) 
    
  printDate(render.date)
    
  $('#cb_messages').prepend('<p class="system '+ data.type +'">'+ _('archive') +'</p>')
}

function system(data) {
  var time = ''
  if(data.time) {
    var datetime = new Date(data.time * 1000)
    time = DateUtils.time(datetime)
  }
  
  $('#cb_messages').prepend('<p class="system ' + data.type + '">' + _(data.message, data) + '<date>&nbsp' + time + '</date></p>')  
}

function modName(data) {
  system(data)
  
  $('#cb_user_' + data.id + ' > span').text(data.name)
}

function addUser(data) {
  if($('#cb_users span').length == 0)
    $('#cb_users').toggleClass('hidden')
    
  var user = $('<div class="user" id="cb_user_' + data.id + '" data-cb-user="' + data.id + '"><span class="name" style="color: #' + data.color + '">' + data.name + '</span></div>').click(selectUser)
  
  $('#cb_users').append(user)
}

function delUser(data) {
  $('#cb_user_' + data.id).remove()  
  
  delete cb_data.to[data.id]
  
  if($('#cb_users span').length == 0)
    $('#cb_users').toggleClass('hidden')
}

function selectUser() {
  var id = parseInt($(this).attr('data-cb-user'))

  $(this).toggleClass('selected')

  if($(this).hasClass('selected'))
    cb_data.to[id] = 0
  else
    delete cb_data.to[id]
}

function welcomeInfo(data) {
  cb_data.id = data.id
  cb_data.color = data.color
}

var Service = {
  chat: render,
  log: logs,
  info: system,
  addUser: addUser,
  delUser: delUser,
  welcome: welcomeInfo,
  modName: modName,
  error: system
}

function onMessage(e) {
  var data = JSON.parse(e.data)
  Service[data.type](data)
}

function onClose(e) {
  $('#cb_messages').prepend('<p class="system info">'+_('connection-closed')+'</p>')
}

function onError(e) {
  $('#cb_messages').prepend('<p class="system error">' + _('connection-error') + "</p>")
}


window.addEventListener('localized', function() {
  document.documentElement.lang = document.webL10n.getLanguage()
  document.documentElement.dir = document.webL10n.getDirection()
})

var cb_data = { 
    to : {}
}

document.webL10n.ready(function() {
  var websocket = new WebSocket('ws://' + HOSTNAME + ':' + PORT)

  websocket.onopen = onOpen
  websocket.onmessage = onMessage
  websocket.onclose = onClose
  websocket.onerror = onError
  
  $('#cb_form').submit(function() {
    var message = $('#cb_message').val()
    cb_data.name = $('#cb_name').val()
    
    var data = {
      message : message,
      name : cb_data.name,
      to : cb_data.to
    }
    
    websocket.send(JSON.stringify(data))
    
    $('#cb_name').css('color', '#' + cb_data.color)
    $('#cb_message').val('')
    
    return false
  })
  
  $('#reset-btn').click(function() {
    $('#cb_messages').empty()
    return false
  })
  
  document.websocket = websocket
})

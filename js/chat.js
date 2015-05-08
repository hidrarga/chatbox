WEB_SOCKET_SWF_LOCATION = "bower_components/web-socket-js/WebSocketMain.swf";
WEB_SOCKET_DEBUG = true;

var HOSTNAME = 'localhost';
var PORT = '8080';

var DateUtils = {
  'time' : function(date) {
    var time = [date.getHours(), date.getMinutes(), date.getSeconds()];
    
    time[0] = time[0] || 12;
    
    for(var i = 0; i < 3; ++i)
        if(time[i] < 10)
        time[i] = '0' + time[i];
        
    return time.join(':');
  },
  'date' : function(date) {
    var date = [date.getDate(), date.getMonth()+1, date.getYear()%100];
    
    for(var i = 0; i < 3; ++i)
        if(date[i] < 10)
        date[i] = '0' + date[i];
        
    return date.join('/');
  }
};


function onOpen() {
  $('#cb_messages').prepend('<div class="system info">Connexion établie !</div>');
}

var old_date = null;
function displayDate(date) {
  if(!old_date || date != old_date) {
    if(old_date)
      $('#cb_messages').prepend('<date class="date">' + old_date + '</date>');
    
    old_date = date;
  }
}

function render(data) {
  var datetime = new Date(data.time * 1000);
  var time = DateUtils.time(datetime);
  var date = DateUtils.date(datetime);
  
  displayDate(date);
  
  $('#cb_messages').prepend('<div class="user"><span class="name" style="color: #'+ data.color + ';">' + data.name + '&nbsp;</span><span class="message"><date>&nbsp;' + time + '</date>' + data.message + '</span></div>');
}

function onMessage(e) {
  var data = JSON.parse(e.data);
  
  if(data.name)
    render(data);
  else if(data.type == 'log') {
    for(var i = 0; i < data.message.length; ++i)
      render(data.message[i]); 
    
    displayDate(render.date);
    
    $('#cb_messages').prepend('<div class="system log">Archives</div>');
  }
  else 
    $('#cb_messages').prepend('<div class="system ' + data.type + '">' + data.message + '</div>');
}

function onClose() {
  $('#cb_messages').prepend('<div class="system info">Connexion interrompue</div>');
}

function onError(e) {
  $('#cb_messages').prepend('<div class="system error">' + e.data + "</div>");
}


$(document).ready(function() {
  var websocket = new WebSocket('ws://' + HOSTNAME + ':' + PORT);

  websocket.onopen = onOpen;
  websocket.onmessage = onMessage;
  websocket.onclose = onClose;
  websocket.onerror = onError;
  
  $('#cb_form').submit(function() {
    var message = $('#cb_message').val();
    var name = $('#cb_name').val();
    console.log(message);
    var data = { 
      message : message,
      name : name
    };
        
    websocket.send(JSON.stringify(data));
    
    $('#cb_message').val('');
    
    return false;
  });
  
  $('#reset-btn').click(function() {
      $('#cb_messages').empty();
      return false;
  });
});
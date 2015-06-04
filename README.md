## Introduction

ChatBox is a small chat service using websockets. 

The php server use [Ratchet](http://socketo.me/).

The javascript client use [jQuery](http://jquery.com/), [Bootstrap](http://getbootstrap.com/), [web-socket-js](https://github.com/gimite/web-socket-js) (for compatibility with old browsers and smartphones) and [webL10n](https://github.com/fabi1cazenave/webL10n)

## Installation

All you need to do is running bower and composer.

```bash
bower update
```

```bash
composer update
```

If you don't have bower installed, run:

```bash
npm install -g bower
```

If you don't have composer installed, run:

```bash
curl -sS https://getcomposer.org/installer | php
```

## Usage

Run the server:

```php
php bin/server.php
```

## Configuration

### Server

You can decide to disable logs by setting `LOGGING` off in `src/ChatBox/Chat.php`.

You can change the number of message stored in the log file by editing `LENGTH` in `src/ChatBox/Logger.php`.

### Client

Don't forget to change the hostname and the location of WebSocketMain.swf in `js/chat.js`

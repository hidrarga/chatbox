== Introduction == 

ChatBox is a small chat service using websockets. 

The php server use Ratchet.

The javascript client use jQuery, Bootstrap and web-socket-js (for compatibility with old browsers and smartphones)

== Installation ==

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

=== Usage ===

Run the server:

```php
php bin/server.php
```

Allow flash policy (for compatibility with old browsers and smartphones):
```php
php bin/flash-server.php
```

=== Configuration ===

You can decide to disable logs by setting `LOGGING` off in `src/ChatBox/Chat.php`.

You can change the number of message stored in the log file by editing `LENGTH` in `src/ChatBox/Logger.php`.
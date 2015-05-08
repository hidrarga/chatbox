<?php
    use Ratchet\Server\FlashPolicy;
    use Ratchet\Server\IoServer;

    require dirname(__DIR__).'/vendor/autoload.php';
    
    $flash = new FlashPolicy;
    $flash->addAllowedAccess('*', 8080); // Allow all Flash Sockets from any domain to connect on port 8080

    $server = IoServer::factory($flash, 843);
    $server->run();
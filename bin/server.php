<?php
    use Ratchet\Server\IoServer;
    use Ratchet\Server\FlashPolicy;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    
    use React\EventLoop\Factory;
    use React\Socket\Server as Reactor;
    
    use ChatBox\Chat;
    
    $directory = (count($_SERVER['argv']) > 1) ? $_SERVER['argv'][1].'/' : '';
    
    require $directory.'vendor/autoload.php';
    
    $loop = Factory::create();
 
    // Our existing server socket
    $webSocket = new Reactor($loop);
    $webSocket->listen(8080, '0.0.0.0');    
    $server = new IoServer(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ), $webSocket
    );
 
    // for allowing flash policy
    $flashSocket = new Reactor($loop);
    $flashSocket->listen(843, '0.0.0.0');   //The Flash player always tries port 843 first;
    $policy = new FlashPolicy;
    $policy->addAllowedAccess('*', 80);
    $policy->addAllowedAccess('*', 8080);
 
    $server = new IoServer(
        $policy,                    
        $flashSocket
    );
 
    $loop->run();
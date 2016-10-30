<?php 
    namespace ChatBox;
    
    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;
    
    class Data {
        public $name;
        public $color;
        
        public function __construct($color, $name = null) {
            $this->name = $name;
            $this->color = $color;
        }
    }
    
    class ChatBoxException extends \Exception { 
        protected $closing;
        
        public function __construct($message, $closing = false) {
            parent::__construct($message);
            
            $this->closing = $closing;
        }
        
        public function isClosing() {
            return $this->closing;
        }
    }
    
    class Chat implements MessageComponentInterface {
        protected $clients;
        protected $logger;
        protected $services;
        
        const LOG_FILE = "chat.log";
        const LOGGING = true; 
        
        public function __construct() {
            $this->clients = new \SplObjectStorage;
            
            $directory = (count($_SERVER['argv']) > 1) ? $_SERVER['argv'][1].'/' : '';
            $this->logger = new Logger($directory.Chat::LOG_FILE);
            
            $this->services = array(
                'chat' => 'chatService',
                'upload' => 'uploadService'
            );
        }
        
        public function generateColor() {
            $color = array();
            
            for($i = 0; $i < 3; ++$i)
                $color[] = str_pad(dechex(mt_rand(0, 200)), 2, '0', STR_PAD_LEFT);
            
            
            return implode('', $color);
        }
    
        public function chatService($data, $from) {
            if(!isset($data->name) or !isset($data->message))
                throw new ChatBoxException('error-invalid', true);
            
            $client = $this->clients[$from];
            $response = new \stdclass();
            
            $response->name = htmlentities(trim($data->name));
            if(empty($response->name))
                throw new ChatBoxException('name-empty');
                    
            if(empty($client->name))
            {
                $message = json_encode(array(
                    'name' => $response->name,
                    'color' => $client->color,
                    'id' => $from->resourceId,
                    'type' => 'addUser'
                ));
                
                foreach($this->clients as $c)
                    $c->send($message);
            } elseif($client->name != $response->name) 
            {
                $message = json_encode(array(
                    'message' => 'name-changed',
                    'color' => $client->color, 
                    'from' => $client->name, 
                    'name' => $response->name,
                    'id' => $from->resourceId,
                    'type' => 'modName'
                ));
                
                foreach($this->clients as $c)
                    $c->send($message);
            }
            
            $client->name = $response->name;
            
            $response->message = htmlentities(trim($data->message));
            if(empty($response->message))
                return;
            
            $response->color = $this->clients[$from]->color;
            $response->time = time();
            $response->type = 'chat';
            
            $json_data = json_encode($response);
            foreach($this->clients as $c)
                $c->send($json_data);
                    
            if(Chat::LOGGING)
                $this->logger->write($json_data);
        }
        
        public function uploadService($data, $from) {
            if(!isset($data->url) or !isset($data->name) or !isset($data->size))
                throw new ChatBoxException('error-invalid', true);
                
            $response = new \stdclass();
            
            $response->url = htmlentities(trim($data->url));
            if(empty($response->url))
                throw new ChatBoxException('url-empty', true);
                
            $response->name = htmlentities(trim($data->name));
            if(empty($response->name))
                throw new ChatBoxException('filename-empty', true);
            
            $response->message = 'file-uploaded';
            $response->size = htmlentities(trim($data->size));
            $response->time = time();
            $response->type = 'info';
            
            $json_data = json_encode($response);
            foreach($this->clients as $c)
                $c->send($json_data);
                
            if(Chat::LOGGING)
                $this->logger->write($json_data);
        }
        
        public function onOpen(ConnectionInterface $client) {
            $data = new Data($this->generateColor());
        
            $this->clients->attach($client, $data);
            
            echo "Connexion établie! ({$client->resourceId})".PHP_EOL;
            
            foreach($this->clients as $c)
                if($c != $client)
                {
                    $d = $this->clients[$c];
                    if(!empty($d->name))
                        $client->send(json_encode(array(
                            'name' => $d->name,
                            'color' => $d->color,
                            'id' => $c->resourceId,
                            'type' => 'addUser'
                        )));
                }
            
            if(!Chat::LOGGING)
                return;
            
            if(!$this->logger->exists() 
                or !$this->logger->messages())
                return;
            
            $client->send(json_encode(array(
                'message' => array_map('json_decode', $this->logger->messages()),
                'type' => 'log'
            )));
        }
        
        public function onMessage(ConnectionInterface $from, $json_data) {
            $data = json_decode($json_data);
            
            try {
                if(empty($data))
                    throw new ChatBoxException('error-invalid', true);
                
                if(!isset($data->type))
                    $data->type = 'chat';
                    
                if(!isset($this->services[$data->type]))
                    throw new ChatBoxException('error-service-unknown', true);
                
                call_user_func(array($this, $this->services[$data->type]), $data, $from);
            } catch(ChatBoxException $e) {
                $from->send(json_encode(array(
                    'message' => $e->getMessage(),
                    'type' => 'error',
                    'time' => time()
                )));
                
                echo "Erreur: ({$from->resourceId}) ". $e->getMessage().PHP_EOL;
                
                if($e->isClosing())
                    $from->close();
            }
        }
        
        public function onClose(ConnectionInterface $from) {
            $client = $this->clients[$from];
            
            if(!empty($client->name))
            {
                $json_data = json_encode(array(
                    'name' => $client->name,
                    'color' => $client->color,
                    'id' => $from->resourceId,
                    'type' => 'delUser'
                ));
                
                foreach($this->clients as $c)
                    if($c != $from)
                        $c->send($json_data);
            }
            
            $this->clients->detach($from);
            
            echo "Connexion interrompue. ({$from->resourceId})".PHP_EOL;
        }
        
        public function onError(ConnectionInterface $client, \Exception $e) {
            echo "Erreur: ({$client->resourceId}) ". $e->getMessage().PHP_EOL;
            
            $client->close();
        }
    }

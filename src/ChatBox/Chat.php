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
    
    class Chat implements MessageComponentInterface {
        protected $clients;
        protected $logger;
        
        const LOG_FILE = "chat.log";
        const LOGGING = true; 
        
        public function __construct() {
            $this->clients = new \SplObjectStorage;
            $this->logger = new Logger(Chat::LOG_FILE);
        }
        
        public function generateColor() {
            $color = array();
            
            for($i = 0; $i < 3; ++$i)
                $color[] = str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
            
            
            return implode('', $color);
        }
        
        public function markup($message) {
            $message = preg_replace('#/.+/#U', '<em>$0</em>', $message);
            $message = preg_replace('#\*.+\*#U', '<strong>$0</strong>', $message);
                
            return $message;
        }
    
        public function onOpen(ConnectionInterface $client) {
            $data = new Data($this->generateColor());
        
            $this->clients->attach($client, $data);
            
            echo "Connexion établie! ({$client->resourceId})".PHP_EOL;
            
            if(!Chat::LOGGING)
                return;
            
            if(!$this->logger->exists() 
                or empty($this->logger->messages()))
                return;
            
            $client->send(json_encode(array(
                'message' => array_map('json_decode', $this->logger->messages()),
                'type' => 'log'
            )));
        }
        
        public function onMessage(ConnectionInterface $from, $json_data) {
            $data = json_decode($json_data);
            
            if(!empty($data) && !is_null($data->name) && !is_null($data->message)) {
                $client = $this->clients[$from];
                
                $data->name = htmlentities(trim($data->name));
                if(empty($data->name)) {
                    $from->send(json_encode(array(
                        'message' => 'name-empty',
                        'type' => 'error'
                    )));
                    return;
                }
                
                $data->message = htmlentities(trim($data->message));
                $data->message = $this->markup($data->message);
                if(empty($data->message)) {
                    $from->send(json_encode(array(
                        'message' => 'message-empty',
                        'type' => 'error'
                    )));
                    return;
                }
                
                $data->color = $this->clients[$from]->color;
                $data->time = time();
                
                if(!empty($client->name) and $client->name != $data->name) {
                    $message = json_encode(array(
                        'message' => 'name-changed',
                        'color' => $client->color, 
                        'from' => $client->name, 
                        'to' => $data->name, 
                        'type' => 'info'
                    ));
                    
                    foreach($this->clients as $c)
                        if($c != $from)
                            $c->send($message);
                }
                $client->name = $data->name;
                
                $json_data = json_encode($data);
                foreach($this->clients as $client)
                    $client->send($json_data);
                
                if(Chat::LOGGING)
                    $this->logger->write($json_data);
            } else
                $from->close();
        }
        
        public function onClose(ConnectionInterface $client) {
            $this->clients->detach($client);
            
            echo "Connexion interrompue. ({$client->resourceId})".PHP_EOL;
        }
        
        public function onError(ConnectionInterface $client, \Exception $e) {
            echo "Erreur: ".$e->getMessage().PHP_EOL;
            
            $client->close();
        }
    }
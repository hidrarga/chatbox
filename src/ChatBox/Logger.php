<?php
    namespace ChatBox;
    
    class Logger {
        private $log_file, $fp;
        
        private $messages;
        const LENGTH = 10;
        
        public function __construct($path) {
            $this->messages = array();
        
            $this->file($path);
            $this->init();
        }
        
        public function file($path) {
            $this->log_file = $path;
        }
        
        public function exists() {
            return file_exists($this->log_file);
        }
        
        private function init() {
            if($this->exists()) {
                $this->fp = fopen($this->log_file, 'r') 
                    or exit("Can't open {$this->log_file}!");
                    
                while($line = fgets($this->fp))
                    $this->messages[] = trim($line);
                    
                fclose($this->fp);
            }   
        }
        
        public function write($message) {
            $this->messages[] = $message;
            
            $this->open();
            
            if(count($this->messages) > Logger::LENGTH) {
                array_shift($this->messages);
            
                foreach($this->messages as $message)
                    fwrite($this->fp, $message.PHP_EOL);
            } else 
                fwrite($this->fp, $message.PHP_EOL);
            
            $this->close();
        }
        
        public function close() {
            fclose($this->fp);
        }
        
        private function open() {
            $mode = (count($this->messages) > Logger::LENGTH) ? 'w' : 'a';
            
            $this->fp = fopen($this->log_file, $mode) 
                or exit("Can't open {$this->log_file}!");
        }
        
        public function messages() {
            return $this->messages;
        }
    }
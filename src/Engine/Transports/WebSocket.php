<?php
namespace PHPSocketIO\Engine\Transports;
use \PHPSocketIO\Engine\Transport;
use \PHPSocketIO\Engine\Parser;
class WebSocket extends Transport
{
    public $writable = true;
    public $supportsFraming = true;
    public $supportsBinary = true;
    public $name = 'websocket';
    public function __construct($req)
    {
        $this->socket = $req->connection;
        $this->socket->onMessage = array($this, 'onData2');
        $this->socket->onClose = array($this, 'onClose');
        $this->socket->onError = array($this, 'onError2');
    }
    
    public function onData2($connection, $data) 
    {
        call_user_func(array($this, 'parent::onData'), $data);
    }
    
    public function onError2($conection, $code, $msg)
    {
        call_user_func(array($this, 'parent::onClose'), $code, $msg);
    }
    
    public function send($packets)
    {
        foreach($packets as $packet)
        {
            $data = Parser::encodePacket($packet, $this->supportsBinary);
            $this->socket->send($data);
            $this->emit('drain');
        }
    }
    
    public function doClose($fn = null) 
    {
        $this->socket->close();
        $this->socket = null;
        if(!empty($fn))
        {
            call_user_func($fn);
        }
    }
}

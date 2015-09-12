<?php
class DefaultAdapter
{

    public $nsp = null;
    public $rooms = array();
    public $sids = array();
    public $encoder = null;
    public function __construct($nsp)
    {
         $this->nsp = $nsp;
         $this->encoder = new Parser\Encoder();
    }

    public function add($id, $room, $fn = null)
    {
        $this->sids[$id][$room] = true;
        $this->rooms[$room][$id] = true;
        // todo next tick
        if ($fn) 
        {
            //call_user_func($fn, null, null);
            echo new \Exception('fn');
        }
    }
     
    public function del($id, $room, $fn)
    {
        unset($this->sids[$id][$room]);
        unset($this->rooms[$room][$id]);
        if(empty($this->rooms[$room]))
        {
            unset($this->rooms[$room]);
        }
        // todo next tick
        if ($fn) 
        {
           echo new \Exception('fn'); 
        }
    }

    public function delAll($id, $fn = null)
    {
        $rooms = isset($this->sids[$id]) ? $this->sids[$id] : array();
        if($rooms) 
        {
            foreach($rooms as $room)
            {
                if(isset($this->rooms[$room][$id]))
                {
                    unset($this->rooms[$room][$id]);
                }
            }
        }
        if(empty($this->rooms[$room]))
        {
            unset($this->rooms[$room]);
        }
        unset($this->sids[$id]);
    }

    public function broadcast($packet, $opts)
    {
        $rooms = isset($opts['rooms']) ? $opts['rooms'] : array();
        $except = isset($opts['except']) ? $opts['except'] : array();
        $flags = isset($opts['flags']) ? $opts['flags'] : array();
        $packetOpts = array(
            'preEncoded' => true,
            'volatile' => isset($flags['volatile']) ?  $flags['volatile'] : null,
            'compress' => isset($flags['compress']) ? $flags['compress'] : null
        );
        $packet['nsp'] = $this->nsp->name;
        $encodedPackets = $this->encoder->encode($packet);
        if($rooms) 
        {
             $ids = array();
             foreach($rooms as $i=>$room) 
             {
                 if(!isset($this->rooms[$room]))
                 {
                     continue;
                 }
                   
                 $room = $this->rooms[$room];
                 foreach($room as $id=>$item)
                 {
                    if(isset($ids[$id]) || isset($except[$id]))
                    {
                         continue;
                    }
                    if(isset($this->nsp->connected[$id]))
                    {
                         $ids[$id] = true;
                         $this->nsp->connected[$id]->packet($encodedPackets, $packetOpts);
                     }
                 }
             }
         } else {
             foreach($this->sids as $id=>$sid)
             {
                  if(isset($except[$id])) continue;
                  if(isset($this->nsp->connected[$id]))
                  {
                      $socket = $this->nsp->connected[$id];
                      $volatile = isset($flags['volatile']) ? $flags['volatile'] : null;
                      $socket->packet($encodedPackets, true, $volatile);
                  }
              }
         }
    }

}

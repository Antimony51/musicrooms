<?php

namespace App;

use Exception;

class Pipe
{

    private $handle;
    private $pipes;
    private $exitcode;

    public function open($cmd)
    {
        $desctriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $this->handle = proc_open($cmd, $desctriptorspec, $this->pipes);
        if ($this->handle === false){
            throw new Exception('Failed to open pipe.');
        }
    }

    public function write($string, $length=null){
        return fwrite($this->pipes[0], $string, $length);
    }

    public function read($bytes)
    {
        return fread($this->pipes[1], $bytes);
    }

    public function readErr($bytes)
    {
        return fread($this->pipes[2], $bytes);
    }

    public function getStatus() {
        $status = proc_get_status($this->handle);
        if (!$status['running'] && !isset($this->exitcode)){
            $this->exitcode = $status['exitcode'];
        }
        return $status;
    }

    public function isRunning()
    {
        $status = $this->getStatus();
        return $status['running'];
    }

    public function getExitCode() {
        return $this->exitcode;
    }

    public function close()
    {
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        return proc_close($this->handle);
    }
}

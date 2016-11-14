<?php

namespace App\AudioStream;

use Captbaritone\TranscodeToMp3Stream\Streamer as BaseStreamer;
use App\Pipe;
use Exception;

class Streamer extends BaseStreamer
{

    protected $pipe;

    public function __construct($pipe = false)
    {
        $this->pipe = $pipe ?: new Pipe();
    }

    public function outputStream($cmd, $byteGoal)
    {
        $this->pipe->open($cmd);

        // Initilize our count of bytes sent
        $outputSize = 0;

        while ($this->pipe->isRunning()) {

            $content = $this->pipe->read(1);

            echo $content;

            $outputSize += strlen($content);

            // check to make sure we have't reached our goal
            if($outputSize >= $byteGoal)
            {
                break;
            }
        }

        echo $this->getPadding($outputSize, $byteGoal);

        $this->pipe->close();

        $exitcode = $this->pipe->getExitCode();
        if ($exitcode && $exitcode !== 0){
            throw new Exception('Command failed with exit code ' . $exitcode);
        }
    }
}

<?php

namespace App\AudioStream;

use App\AudioStream\Streamer;
use App\AudioStream\HeaderBuilder;
use App\AudioStream\Transcoder;
use App\AudioStream\AudioInspector;
use Captbaritone\TranscodeToMp3Stream\TranscodedSizeEstimator;

class Mp3Stream
{

    protected $transcoder;
    protected $streamer;
    protected $headerBuilder;
    protected $audioInspector;
    protected $transcodedSizeEstimator;

    public function __construct($transcoder = false, $streamer = false, $headerBuilder = false, $audioInspector = false, $transcodedSizeEstimator = false)
    {
        $this->transcoder = $transcoder ?: new Transcoder();
        $this->streamer = $streamer ?: new Streamer();
        $this->headerBuilder = $headerBuilder ?: new HeaderBuilder();
        $this->audioInspector = $audioInspector ?: new AudioInspector();
        $this->transcodedSizeEstimator = $transcodedSizeEstimator ?: new TranscodedSizeEstimator();
    }

    public function output($sourceMedia, $callback, $outputFilename = 'stream.mp3', $kbps = 128, $start = 0, $end = 0)
    {

        $endTime = $end ?: $this->audioInspector->getLength($sourceMedia);
        $length = $endTime - $start;

        $cmd = $this->transcoder->command($sourceMedia, $kbps, $start, $length);

        $byteGoal = $this->transcodedSizeEstimator->estimatedBytes($length, $kbps);

        $headers = $this->headerBuilder->getHeader($outputFilename, $byteGoal);

        $callback(function() use ($cmd, $byteGoal){
            $this->streamer->outputStream($cmd, $byteGoal);
        }, $headers);
    }
}

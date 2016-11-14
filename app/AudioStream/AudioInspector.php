<?php

namespace App\AudioStream;

/**
 * Audio Inspector
 *
 * Inspect audio files using the ffprobe command line tool. Currently is only
 * used for getting the length of a media file
 *
 * @author Jordan Eldredge <jordaneldredge@gmail.com>
 **/
class AudioInspector
{

    /**
     * Get Length
     *
     * REQUIRES ffmpeg 0.9.
     *
     * Get the length of an audio or video media file
     *
     * @param string $file path to the media file we are querying
     * @return float decimal length of $file in seconds
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public static function getLength($file)
    {
        $json = $this->probe($file);
        return $json->streams[0]->duration;
    }

    /**
     * Probe
     *
     * REQUIRES ffmpeg 0.9.
     *
     * Get ffprobe's media information for a file using it's json output
     *
     * @param string $file path to the media file we are querying
     * @return obj ffprobe's media information
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public static function probe($file)
    {
        $file = escapeshellarg($file);
        $cmd = config('cmd.ffprobe') . " -v quiet -print_format json -show_format -show_streams '{$file}'";
        exec($cmd, $outputLines);
        $json = implode("\n", $outputLines);

        return json_decode($json);
    }
}

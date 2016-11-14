<?php

namespace App\AudioStream;

class HeaderBuilder
{

    public function getHeaders($filename, $size)
    {
        return [
            'Cache-Control' => 'public, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $size,
            'Content-type' => 'audio/mpeg',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Content-Transfer-Encoding' => 'binary'
        ];
    }
}

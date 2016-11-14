<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Track;
use Cache;
use App\AudioStream\HeaderBuilder;
use App\AudioStream\Mp3Stream;

class StreamController extends Controller
{
    public getTrackStream(Track $track){
        if ($track->type === 'file'){
            if (Cache::has('streaminfo_'.$track->uri)){
                $streamInfo = Cache::get('streaminfo_'.$track->uri);
                if ($streamInfo->transcoding){
                    return response()->stream(function(){

                    }, 200, )
                }else{
                    $data = Cache::get('stream_'.$track->uri);
                    $headers = (new HeaderBuilder())->getHeaders($track->uri.'.mp3', $streamInfo->size);
                    return response($data)->withHeaders($headers);
                }
            }else if(){

            }
        }
    }
}

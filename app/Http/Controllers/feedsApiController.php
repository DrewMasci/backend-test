<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\cachedFeedData;
use App\error;

class feedsApiController extends Controller
{
    public function addFeed(Request $request)
    {
        $feedUrl = $request->input('feed-url');

        $feed = cachedFeedData::where('url', $feedUrl)->
            where('deleted_at', null)->first();

        if(!empty($feed)) {
            $error = new error;

            $message = [
                'operation' => 'failed',
                'message' => 'record already exists with provided URL',
                'data' => json_encode(['url' => $feedUrl]),
            ];

            $error->response_message = $message['message'];
            $error->data = $message['data'];
            $error->save();

            return response()->json($message);
        }

        $feed = new cachedFeedData;

        $feed->url = $feedUrl;
        $feed->raw_json = file_get_contents($feedUrl);

        $feed->save();

        $r = ['operation' => 'successful'];

        return response()->json($r);
    }
}

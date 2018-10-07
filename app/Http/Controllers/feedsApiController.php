<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\cachedFeedData;

class feedsApiController extends Controller
{
    public function addFeed(Request $request)
    {
        $feedUrl = $request->input('feed-url');

        $feed = cachedFeedData::where('url', $feedUrl)->
            where('deleted_at', null)->first();

        if(!empty($feed)) {
            return response()->json([
                'operation' => 'failed',
                'message' => 'record already exists with provided URL']
            );
        }

        $feed = new cachedFeedData;

        $feed->url = $feedUrl;
        $feed->raw_json = file_get_contents($feedUrl);

        $feed->save();

        $r = ['operation' => 'successful'];

        return response()->json($r);
    }
}

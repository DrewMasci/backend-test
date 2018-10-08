<?php

namespace App\Http\Controllers;

use App\cachedFeedData;
use App\error;
use Illuminate\Http\Request;

class feedsApiController extends Controller
{
    private $cities = [
        'London' => ['London'],
        'New_York' => ['New York', 'NY'],
    ];

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

    public function mergeFeeds(Request $request)
    {
        $ids = [];

        if(!empty($request->input('feed-ids'))) {
            if(preg_match('/[\'^£$%&*()}{@#~?><>|=_+¬-]/', $request->input('feed-ids'))) {
                throw new \Exception('feed-ids contains special characters other than ,');
            }

            $ids = explode(',', $request->input('feed-ids'));
        }

        $feeds = cachedFeedData::allArray($ids);

        return response()->json($feeds, $feeds['meta']['code']);
    }

    public function listFeeds()
    {
        $feeds = cachedFeedData::where('deleted_at', null)->get();

        foreach($feeds as $index => $feed) {
            $feeds[$index]['raw_json'] = json_decode($feeds[$index]['raw_json'], true);
        }

        return response()->json($feeds);
    }
}

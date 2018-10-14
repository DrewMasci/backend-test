<?php

namespace App\Http\Controllers;

use App\cachedFeedData;
use App\error;
use Illuminate\Http\Request;

class feedsApiController extends Controller
{
    /**
     * Takes the feed-url parameter provided in the HTTP request, checks to
     * see if it already exists in the cache. If it doesn't then it adds it to
     * the cache to use later.
     *
     * @param Request $request [description]
     * @return JSON
     */
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

    /**
     * If a value is given to the feed-ids parameter for the HTTP Request
     * then the value is decoded checking for a , to make sure it is separated
     * correctly. If it contains any other symbol then it returns an error
     * to the end point stating the issue.
     *
     * The method then proceeds to turn the feed-ids into an array and hand it
     * to the cachedFeedData class to take the feeds held in the database and
     * turn them into an array. Finally it returns a json object with all
     * the merged data.
     *
     * @param  Request $request
     * @return JSON
     */
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

    /**
     * Returns a JSON object with all the feeds currently held in the cache.
     * @return JSON
     */
    public function listFeeds()
    {
        $feeds = cachedFeedData::where('deleted_at', null)->get();

        foreach($feeds as $index => $feed) {
            $feeds[$index]['raw_json'] = json_decode($feeds[$index]['raw_json'], true);
        }

        return response()->json($feeds);
    }
}

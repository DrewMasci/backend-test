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

        if(empty($feeds)) {
            $response = response()->json(['operation' => 'failed', 'message' => 'no feeds found'], 204);
            return $response;
        }

        $output = [];
        foreach($feeds as $index => $feed) {
            $temp = json_decode($feed['raw_json'], true);

            foreach($this->cities as $key => $city) {
                foreach($city as $label) {
                    if(stripos($temp['data']['location']['display_name'], $label) !== false) {
                        if(isset($output[$key])) {
                            $locations = array_merge(array_values($output[$key]['locations']), array_values($temp['data']['locations']));

                            $output[$key] = $this->array_glue($output[$key], $temp['data'], true);
                            $output[$key]['locations'] = $locations;
                        } else {
                            $output[$key] = $temp['data'];
                        }

                        break;
                    }
                }
            }
        }

        return response()->json($output);
    }

    private function array_glue($destination, $addition, $inDepth = false) {
        foreach($destination as $key => $value) {
            //dd($addition);
            if(!isset($addition[$key])) {
                continue;
            }

            if(is_array($value) && !is_array($addition[$key])
                && !in_array($addition[$key], $destination[$key])) {
                $destination[$key][] = $addition[$key];
            } else if(is_array($value) && $inDepth) {
                $destination[$key] = $this->array_glue($destination[$key], $addition[$key]);
            } else if ($value != $addition[$key]) {
                $destination[$key] = [$value, $addition[$key]];
            }
        }

        return $destination;
    }

    public function listFeeds()
    {
        $feeds = cachedFeedData::allArray();

        foreach($feeds as $index => $feed) {
            $feeds[$index]['raw_json'] = json_decode($feeds[$index]['raw_json'], true);
        }

        dd($feeds);

        return response()->json($feeds);
    }
}

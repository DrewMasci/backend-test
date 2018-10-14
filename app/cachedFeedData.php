<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cachedFeedData extends Model
{
    private static $cities = [
        'London' => ['London'],
        'New_York' => ['New York', 'NY'],
    ];

    /**
     * returns a merged array of all the cached records provided from the
     * database. If the there are no ids provided with the call, the function
     * gets all the records held by in the database.
     *
     * Upon getting the records it proceeds to merge all the data and returns
     * an array.
     *
     * @param  array $ids
     * @return array
     */
    public static function allArray($ids = null)
    {
        if($ids == null) {
            $feeds = cachedFeedData::where('deleted_at', null)->get();
        } else {
            $feeds = cachedFeedData::where('deleted_at', null)->whereIn('id', $ids)->get();
        }

        $temp = [];

        foreach($feeds as $feed) {
            $temp[] = $feed->attributes;
        }

        $feeds = $temp;

        if(empty($feeds)) {
            return ['meta' => ['code' => 204, 'status' => 'failed', 'message' => 'no feeds found'], 'data' => '',];
        }

        $meta = [
            'code' => 200,
            'status' => 'success',
            'provider' => [
                'service' => [],
                'version' => [],
            ],
            'response' => [
                'format' => [],
                'version' => [],
            ]
        ];
        $output = [];
        foreach($feeds as $index => $feed) {
            $temp = json_decode($feed['raw_json'], true);
            $raw_meta = $temp['meta'];

            if(!in_array($raw_meta['provider']['service'], $meta['provider']['service'])) {
                $meta['provider']['service'][] = $raw_meta['provider']['service'];
            }

            if(!in_array($raw_meta['provider']['version'], $meta['provider']['version'])) {
                $meta['provider']['version'][] = $raw_meta['provider']['version'];
            }

            if(!in_array($raw_meta['response']['format'], $meta['response']['format'])) {
                $meta['response']['format'][] = $raw_meta['response']['format'];
            }

            if(!in_array($raw_meta['response']['version'], $meta['response']['version'])) {
                $meta['response']['version'][] = $raw_meta['response']['version'];
            }

            foreach(self::$cities as $key => $city) {
                foreach($city as $label) {
                    if(stripos($temp['data']['location']['display_name'], $label) !== false) {
                        if(isset($output[$key])) {
                            $locations = array_merge(array_values($output[$key]['locations']), array_values($temp['data']['locations']));

                            $output[$key] = self::arrayGlue($output[$key], $temp['data']);
                            $output[$key]['locations'] = $locations;
                        } else {
                            $output[$key] = $temp['data'];
                        }

                        break;
                    }
                }
            }
        }

        return ['meta' => $meta, 'data' => $output,];
    }

    /**
     * arrayGlue is a static function that is called recursively to merge all
     * the data points in the $destination and $addition arrays to be returned
     * as an array to the calling point. The conditions of how the data is
     * merged is dependant on that data types at the indicies for $destination
     * and $addition.
     *
     * If a index exists in $destination but not in $addition or if the index
     * is a numerical value the process is skipped at that specific index.
     *
     * @param  array $destination
     * @param  array $addition
     * @return array
     */
    public static function arrayGlue($destination, $addition) {
        foreach($destination as $key => $value) {
            if(!isset($addition[$key]) || is_numeric($key)) {
                continue;
            }

            if(is_array($value) && !is_array($addition[$key])
                && !in_array($addition[$key], $destination[$key])) {
                $destination[$key][] = $addition[$key];
            } else if(is_array($value)) {
                $destination[$key] = self::arrayGlue($value, $addition[$key]);
            } else if ($value != $addition[$key]) {
                $destination[$key] = [$value, $addition[$key]];
            }
        }

        return $destination;
    }
}

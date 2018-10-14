<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cachedFeedData extends Model
{
    /**
     * Used to determine the different cities within the feeds data, combining
     * them where able dependant on the values held in the cities individual
     * arrays.
     *
     * @var array
     */
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

        foreach($feeds as $index => $feed) {
            $feeds[$index] = $feed->attributes;
        }

        $output = [];

        if(empty($feeds)) {
            $meta = self::createMetaData(204, 'failed', 'no feeds found');
            return ['meta' => $meta, 'data' => $output,];
        }

        $meta = self::createMetaData(200, 'success');

        foreach($feeds as $index => $feed) {
            $temp = json_decode($feed['raw_json'], true);
            $meta = self::insertMetaData($temp, $meta);

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

        $output = self::sanitiseArray($output);

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

    /**
     * sanitiseArray is a static function that is used to remove all null valued
     * indicies from the array $data.
     *
     * @param  array $data
     * @return array
     */
    public static function sanitiseArray($data)
    {
        $r = $data;

        foreach($r as $index => $value) {
            if(is_array($value)){
                $r[$index] = self::sanitiseArray($value);
            }

            if($value == null) {
                unset($r[$index]);
            }
        }

        return $r;
    }

    /**
     * creates the meta header for the merged data.
     *
     * @param  int $statusCode
     * @param  string $statusValue
     * @param  string $errorMessage optional
     * @return array
     */
    public static function createMetaData($statusCode, $statusValue, $errorMessage = null)
    {
        $r = [
            'code' => $statusCode,
            'status' => $statusValue,
            'provider' => [
                'service' => [],
                'version' => [],
            ],
            'response' => [
                'format' => [],
                'version' => [],
            ],
        ];

        if($statusCode != 200) {
            unset($r['provider']);
            unset($r['response']);

            $r['message'] = $errorMessage;
        }

        return $r;
    }

    /**
     * function used to take the crucial components for the meta header.
     *
     * @param  array $feed
     * @param  array $metaHeader
     * @return array
     */
    public static function insertMetaData($feed, $metaHeader)
    {
        $raw_meta = $feed['meta'];

        if(!in_array($raw_meta['provider']['service'], $metaHeader['provider']['service'])) {
            $metaHeader['provider']['service'][] = $raw_meta['provider']['service'];
        }

        if(!in_array($raw_meta['provider']['version'], $metaHeader['provider']['version'])) {
            $metaHeader['provider']['version'][] = $raw_meta['provider']['version'];
        }

        if(!in_array($raw_meta['response']['format'], $metaHeader['response']['format'])) {
            $metaHeader['response']['format'][] = $raw_meta['response']['format'];
        }

        if(!in_array($raw_meta['response']['version'], $metaHeader['response']['version'])) {
            $metaHeader['response']['version'][] = $raw_meta['response']['version'];
        }

        return $metaHeader;
    }
}

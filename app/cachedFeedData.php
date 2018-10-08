<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cachedFeedData extends Model
{
    public static function allArray($ids = null)
    {
        if($ids == null) {
            $feeds = cachedFeedData::where('deleted_at', null)->get();
        } else {
            $feeds = cachedFeedData::where('deleted_at', null)->whereIn('id', $ids)->get();
        }

        $temp = [];

        foreach($feeds as $feed) {
            $r[] = $feed->attributes;
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

        return ['meta' => $meta, 'data' => $output,];
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
}

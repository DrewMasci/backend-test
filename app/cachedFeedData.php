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

        $r = [];

        foreach($feeds as $feed) {
            $r[] = $feed->attributes;
        }

        return $r;
    }
}

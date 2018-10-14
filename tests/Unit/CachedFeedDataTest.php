<?php

namespace Tests\Unit;

use App\cachedFeedData;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllArray extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAllArray()
    {
        $feeds = cachedFeedData::allArray();

        if(!is_array($feeds)) {
            $this->assertTrue(false);
        }

        if(!isset($feeds['meta']['code'])) {
            $this->assertTrue(false);
        }

        if($feeds['meta']['code'] != 200) {
            $this->assertTrue(false);
        }

        $this->assertTrue(true);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testArrayGlue()
    {
        $array1 = [
            'city' => [
                'location' => [
                    'provider' => 'test provider 1',
                    'version' => 1,
                ],
                'locations' => [
                    'name' => 'City 1',
                    'country' => 'USA',
                ]
            ]
        ];

        $array2 = [
            'city' => [
                'location' => [
                    'provider' => 'test provider 2',
                    'version' => 1,
                ],
                'locations' => [
                    'name' => 'City One',
                    'country' => 'USA',
                ]
            ]
        ];

        $arrayR = [
            'city' => [
                'location' => [
                    'provider' => [
                            'test provider 1',
                            'test provider 2',
                        ],
                    'version' => 1
                ],
                'locations' => [
                    'name' => [
                        'City 1',
                        'City One',
                    ],
                    'country' => 'USA',
                ]
            ]
        ];

        $r = cachedFeedData::arrayGlue($array1, $array2);

        if($r != $arrayR) {
            $this->assertTrue(false);
        }

        $this->assertTrue(true);
    }
}

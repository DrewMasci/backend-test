<?php

namespace Tests\Unit;

use App\cachedFeedData;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllArray extends TestCase
{
    /**
     * tests the returns of the allArray function from cachedFeedData is
     * returning an array, that the status code is correctly set, and that it is
     * returning 200.
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
     * tests the arrayGlue function of cachedFeedData to make sure that the
     * arrays are combined correctly.
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

    /**
     * Tests the sanitiseArray function from cachedFeedData to make sure all
     * null values are removed from the given array.
     *
     * @return void
     */
    public function testSanitiseArray()
    {
        $array1 = [
            'city' => [
                'location' => [
                    'provider' => 'test provider 1',
                    'version' => null,
                ],
                'locations' => [
                    'name' => 'City 1',
                    'country' => null,
                ]
            ]
        ];

        $arrayR = [
            'city' => [
                'location' => [
                    'provider' => 'test provider 1',
                ],
                'locations' => [
                    'name' => 'City 1',
                ]
            ]
        ];

        $r = cachedFeedData::sanitiseArray($array1);

        if($r != $arrayR) {
            $this->assertTrue(false);
        }

        $this->assertTrue(true);
    }
}

## Installation Instructions

- create a clone of the repo using the button above.
- run `composer install`.
- create a copy of `.env.example` and rename it `.env`.
- set the database details in your `.env` file.
- run `php artisan key:generate`.
- run `php artisan migrate`.

## Using the API

Once you've completed the above you can turn on your local php server by using
`php artisan serve` and this will allow you access to the code using:

`http://localhost:8000`

However if you have set your server up differently this may not be needed.

After you have completed the above you can use the following API calls, you may
need to adapt them to suit your server however:

- `http://localhost/api/add-feed` as either a POST or GET request giving it the
parameter `feed-url` with the URL of the feed you wish to cache in the system.
- `http://localhost/api/list-feeds` to receive a JSON list of all the feeds
currently held in the system.
- `http://localhost/api/merge-feeds` to receive a JSON object of the combination
of all the feeds specified in the `feed-ids` parameter. Ids can be separated
with a `,` for more than one feed to be merged. Additionally if `feed-ids` is
not provided then all the existing cached feed data is merged. Feed data is
separated by city. The data for the locations can also be ordered using the
`sort-field` and `sort-direction` parameters, if `sort-direction` isn't set then
it is defaulted to `asc`.

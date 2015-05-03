Google Music Search
===================
This is a PHP class to search the Google Play Music store.

Installation
------------
Install with [Composer](https://getcomposer.org/).  A sample `composer.json`:
```json
{
	"require": {
		"cookieguru/googlemusicsearch": "dev-master"
	}
}
```
If you're not using Composer, just include `API.php` and `GoogleMusicTrack.php`

Usage
-----
```php
$api = new \cookieguru\googlemusicsearch\API();
$api->setUserAgent('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0');
$api->verifyPeer(false); //This line may not be needed in your situation
$results = $api->search('Wezz Devall feat. Alana Aldea - On The Rise (Original Mix)');
print_r($results[0]);
```
Outputs:
```
cookieguru\googlemusicsearch\GoogleMusicTrack Object
(
    [url] => https://play.google.com/store/music/album?id=Bhtcvtbwf7q532tc7xk4j3j4hia&tid=song-Turpkonwsebw6hzstiith765abu
    [artist] => Wezz Devall feat. Alana Aldea
    [title] => On The Rise (Original Mix)
    [price] => $1.99
)
```

Copyright and License
---------------------
Copyright (c) 2014 [Cookie Guru](http://github.com/cookieguru)

This class is licensed under the The MIT License.

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
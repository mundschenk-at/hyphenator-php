hyphenator-php
--------
A simple PHP class to hyphenate English sentences mixed with HTML. It originated from http://phphyphenator.yellowgreen.de but it was
not easily configurable and encapsulated. For example it is configured via $GLOBALS variable and pattern matching code looked complex
and ineffective. Actually it was based on a JavaScript implementation of the same algorithm [mnater/Hyphenator](https://github.com/mnater/Hyphenator),
which I have also explained at [my blog](https://nikonyrh.github.io/phphyphenation.html). There also exists a
[heiglandreas/Org_Heigl_Hyphenator](https://github.com/heiglandreas/Org_Heigl_Hyphenator) library which supports many languages
and is a lot more flexible to configure but it does not preserve [HTML tags](http://andreas.heigl.org/2009/04/20/hyphenate-texts-with-php/comment-page-1/#comment-282).

Usage example (after being added to composer.json and installed):

```php
// The used hyphen defaults to HTML '&shy;', any other string is possible as well
$hyphenator = new \NikoNyrh\Hyphenator\Hyphenator('-');

$result = $hyphenator->hyphenate(
    '<hyphenated hyphenated style="hyphenated">hyphenated <hyphenated/> but not &hyphenated;'
)
// '<hyphenated hyphenated style="hyphenated">hy-phen-ated <hyphenated/> but not &hyphenated;'
```

TODO
--------
 - More tests with special characters in UTF-8
 - More languages(?)
 - More optional configurations


License
-------
Copyright 2015 Niko Nyrhilä

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

#General Info

Name: Tea-Fueled Last.fm PHP Class

Author: Matthew Loberg

URL: http://mloberg.com/blog/lastfmclass/

Author URL: http://mloberg.com

Licence: Copyright (c) 2010 Matthew Loberg under the MIT Licence (licence.txt)

Understandable Licence: http://creativecommons.org/licenses/MIT/

Requires: At least PHP 5.1

***

##About

This was a class I created for last.fm's API. Currently only three calls are supported.

1. Get Loved Tracks
2. Get Recent Tracks
3. Get Banned Tracks

***

##Changelog:

###0.1

* Created class
* Added user.getLovedTracks
* Added user.getRecentTracks
* Added user.getBannedTracks

###0.2

* Added limit to getLoved(), getRecent(), and getBanned() methods
* Added getRecentDays() and getRecentMonths() methods

***

##To Do:

* In getRecent() function, see if the track is currently playing
* Add more API calls
* Allow for options to be passed
   * Such as return as list instead of paragraph
* Write better documentation
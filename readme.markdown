# General Info

Name: Tea-Fueled Last.fm PHP Class

Version 0.2

Author: Matthew Loberg

URL: http://mloberg.com/blog/lastfmclass/

Author URL: http://mloberg.com

Licence: Copyright (c) 2010 Matthew Loberg under the MIT Licence (licence.txt)

Understandable Licence: http://creativecommons.org/licenses/MIT/

***

## About

This was a class I created for last.fm's API. Currently only three calls are supported.

1. Get Loved Tracks
2. Get Recent Tracks
3. Get Banned Tracks

**More documentation will be coming.** I just created this, and I'm still working on the details and stuff.

***

## Changelog:

### 0.1

* Created class
* Added user.getLovedTracks
* Added user.getRecentTracks
* Added user.getBannedTracks

### 0.2

* More in file documentation
* Added limit as optional parameter to all existing methods
* Added time back parameter to getRecent()

***

##To Do:

* In getRecent() function, see if the track is currently playing
* Add more API calls
* Allow for options to be passed
   * Such as return as list or array instead of paragraph
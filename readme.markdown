# General Info

Name: Tea-Fueled Last.fm PHP Class

Version 0.3

Author: Matthew Loberg

URL: http://mloberg.com/blog/lastfmclass/

Author URL: http://mloberg.com

Licence: Copyright (c) 2010 Matthew Loberg under the MIT Licence (licence.txt)

Understandable Licence: http://creativecommons.org/licenses/MIT/

***

## About

This was a class I created for last.fm's API. Not all of Last.fm's API calls are supported at this time. Only these are supported.

1. Get Loved Tracks
2. Get Recent Tracks
3. Get Banned Tracks
4. Get Library Tracks
5. Get Library Artists
6. Get Library Albums
7. Get User Events
8. Get Geo Events

***

##To Do:

* In getUserRecent() function, see if the track is currently playing
* Add more API calls
* Allow for options to be passed
   * Such as return as list or array instead of paragraph
* Add styling to returned paragraphs
* Add width and height to all images
* Styling for Get User Events is messy
* Documentation outdated, update it

***

## Changelog:

### 0.3

* Changed current method names
* Added library calls
* Added user.getEvents
* Added geo.getEvents
* Added __construct function

### 0.2

* More in file documentation
* Added limit as optional parameter to all existing methods
* Added time back parameter to getRecent()

### 0.1

* Created class
* Added user.getLovedTracks
* Added user.getRecentTracks
* Added user.getBannedTracks
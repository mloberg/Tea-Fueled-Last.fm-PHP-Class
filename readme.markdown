# General Info

Name: Tea-Fueled Last.fm PHP Class

Version 0.6

Author: Matthew Loberg

URL: http://mloberg.com/blog/lastfmclass/ *New URL coming soon, but for now, check out the wiki*

Author URL: http://mloberg.com

Licence: Copyright (c) 2011 Matthew Loberg under the MIT Licence (licence.txt)

Understandable Licence: http://creativecommons.org/licenses/MIT/

***

## About

This was a class I created for last.fm's API. Not all of Last.fm's API calls are supported at this time. Check the source for the supported API calls.

***

##To Do:

* Add more API calls
* Document
* Add error reporting

***

## Changelog:

### 0.6

* Fixed up signature method
* Added a url builder function
* Added user.shout
* Added all user calls

### 0.5.5

* Most calls now return as JSON rather then xml
* Cleaned up some code

### 0.5

* Added authentication
* user.getRecentStations
* artist.getInfo

### 0.4

* Added user.getWeeklyChart methods
* Removed "get" from the beginning of every method
* Added chart methods
* All methods are passed back as an array, rather then echoed

### 0.3

* Changed current method names
* Added library calls
* Added user.getEvents
* Added geo.getEvents
* Added __construct function
* Some methods pass an array back, rather then echo the results directly

### 0.2

* More in file documentation
* Added limit as optional parameter to all existing methods
* Added time back parameter to getRecent()

### 0.1

* Created class
* Added user.getLovedTracks
* Added user.getRecentTracks
* Added user.getBannedTracks
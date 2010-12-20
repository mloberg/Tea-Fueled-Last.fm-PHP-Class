<?php
/*************************
**	Name: Tea-Fueled Last.fm PHP Class
**	Author: Matthew Loberg
**	URL: http://mloberg.com/blog/lastfmclass/
**	Author URL: http://mloberg.com
**	Version: 0.4
**	License: Copyright 2010 Matthew Loberg. Licenced under the MIT licence. More information in licence.txt, readme.txt, and at http://creativecommons.org/licenses/MIT/
**	
**	This is a last.fm class I created for making API calls to last.fm.
**	Currently only a limited number of API calls are supported right now with more being added.
**	
*************************/

class lastFM{

	/************************
		GLOBAL VARIABLES
	************************/
	private $url = 'http://ws.audioscrobbler.com/2.0/';
	private $apikey;
	private $user;
	
	function __construct($api,$user){
		$this->apikey = $api;
		$this->user = $user;
	}
	
	/************************
		USER METHODS
	************************/
	
	function userLoved($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the url
		$lastfm = $this->url . '?method=user.getlovedtracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->lovedtracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'url' => $track->url,
				'artist' => $track->artist->name,
				'artisturl' => $track->artist->url,
				'img' => $track->image[2]
			);
			
			$i++;
		}
		
		return $info;
	}
	
	/**
	*	user.getRecentTracks has muliple optional parameters,
	*	limit, to, and from. More info here, http://www.last.fm/api/show?service=278
	*	In this method, all parameters are optional, so you can use it as it.
	*	The two optional parameters are limit, and time back.
	*	The time back parameter is specified in days.
	*	One "bug" is if you want to specify the time back, you must also specify a limit.
	*	You could pass nothing ('') or -1 as a limit to get all.
	**/
	   
	function userRecent($l='',$t=''){
		// set the parameters if any were passed
		$limit = $l;
		$timeBack = $t;
		
		/**
		*	Last.fm to and from needs to be specified in UNIX timestamp format in the UTC time zone.
		*	Since we are specifying the time back (in days), we can set to as the current time stamp.	
		**/
		
		// set the timezone to UTC
		date_default_timezone_set('UTC');
		// get the current UNIX timestamp
		$to = time();
		// then figure out the time back in seconds
		$from = time() - (60 * 60 * 24 * $timeBack);
		
		// now see if the a time parameter was passed
		if($t == ""){
			// time back was not specified, run normal
			// build the api url
			$lastfm = $this->url . '?method=user.getrecenttracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		}else{
			// build the api url
			$lastfm = $this->url . '?method=user.getrecenttracks&user=' . $this->user . '&limit=' . $limit . '&from=' . $from . '$to=' . $to . '&api_key=' . $this->apikey;
		}
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->recenttracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'url' => $track->url,
				'artist' => $track->artist,
				'album' => $track->album,
				'img' => $track->image[2]
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userBanned($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=user.getbannedtracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);

		$tracks = $xml->bannedtracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'url' => $track->url,
				'artist' => $track->artist->name,
				'img' => $track->image[2],
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userEvents(){
		// build the api url
		$lastfm = $this->url . '?method=user.getevents&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$events = $xml->events->event;
		$info = array();
		$i = 0;
		foreach($events as $event){
			// Turn the time into a 12-hour format
			preg_match('/\d{2}[:]\d{2}[:]\d{2}/',$event->startDate,$time);
			list($fullhour,$minute,$second) = explode(":",$time[0]);
			if($fullhour > 12){
				$hour = ($fullhour - 12) . ":" . $minute . " PM";
			}else{
				$hour = $fullhour . ":" . $minute . " AM";
			}
							
			// Delete time from startdate
			$date = preg_replace('/\d{2}[:]\d{2}[:]\d{2}/','',$event->startDate);
			$date = preg_replace('/[,]/','',$date);
			$date = preg_replace('/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/','',$date);
			$date = trim($date);
			list($day,$month,$year) = explode(" ",$date);
			$date = $month . " " . $day . ", " . $year;
			
			$info[$i] = array(
				'title' => $event->title,
				'artists' => $event->artists->artist,
				'headline' => $event->artists->headliner,
				'date' => $date,
				'time' => $hour,
				'venue' => $event->venue->name,
				'location' => $event->venue->location->city,
				'venueUrl' => $event->venue->url,
				'venueWebsite' => $event->venue->website,
				'startdate' => $event->startDate,
				'description' => $event->description,
				'img' => $event->image[2],
				'url' => $event->url,
				'website' => $event->website,
				'tickets' => $event->ticket,
				'tags' => $event->tags->tag
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userFriends($l=''){
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=user.getfriends&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$users = $xml->friends->user;
		$info = array();
		$i = 0;
		foreach($users as $user){
			$info[$i] = array(
				'name' => $user->name,
				'realname' => $user->realname,
				'img' => $user->image[2],
				'url' => $user->url
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userInfo(){
		// build the api url
		$lastfm = $this->url . '?method=user.getinfo&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$user = $xml->user;
		/**
		 * Because this only returns a single result, we don't have to loop
		 */
		
		$info['name'] = $user->name;
		$info['realname'] = $user->realname;
		$info['img'] = $user->image[2];
		$info['url'] = $user->url;
		$info['country'] = $user->country;
		$info['age'] = $user->age;
		$info['gender'] = $user->gender;
		$info['playcount'] = $user->playcount;
		$info['playlists'] = $user->playlists;
		$info['registered'] = $user->registered;
		
		// and finally send the info
		return $info;
	}
	
	function userPlaylists(){
		// build the api url
		$lastfm = $this->url . '?method=user.getplaylists&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$playlists = $xml->playlists->playlist;
		
		$i = 0;
		$info = array();
		foreach($playlists as $playlist){
			$info[$i] = array(
				'title' => $playlist->title,
				'description' => $playlist->description,
				'size' => $playlist->size,
				'duration' => $playlist->duration,
				'url' => $playlist->url,
				'img' => $playlist->image[2],
				'creator' => $playlist->creator
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userShouts(){
		// build the api url
		$lastfm = $this->url . '?method=user.getshouts&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$shouts = $xml->shouts->shout;
		$info = array();
		$i = 0;
		foreach($shouts as $shout){
			$info[$i] = array(
				'body' => $shout->body,
				'author' => $shout->author,
				'date' => $shout->date
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userTopAlbums(){		
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.gettopalbums&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$albums = $xml->topalbums->album;
		$info = array();
		$i = 0;
		foreach($albums as $album){
			$info[$i] = array(
				'name' => $album->name,
				'artist' => $album->artist->name,
				'artist_url' => $album->artist->url,
				'url' => $album->url,
				'img' => $album->image[2],
				'playcount' => $album->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userTopArtists(){		
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.gettopartists&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$artists = $xml->topartists->artist;
		$info = array();
		$i = 0;
		foreach($artists as $artist){
			$info[$i] = array(
				'name' => $artist->name,
				'url' => $artist->url,
				'img' => $artist->image[2],
				'playcount' => $artist->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userTopTags(){		
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.gettoptags&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tags = $xml->toptags->tag;
		$info = array();
		$i = 0;
		foreach($tags as $tag){
			$info[$i] = array(
				'name' => $tag->name,
				'count' => $tag->count,
				'url' => $tag->url
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userTopTracks(){
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.gettoptracks&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->toptracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'artist' => $track->artist->name,
				'artist_url' => $track->artist->url,
				'playcount' => $track->playcount,
				'url' => $track->url,
				'img' => $track->image[2]
			);
			
			$i++;
		}
		
		return $info;
	}
	
		function userAlbumChart(){		
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.getweeklyalbumchart&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$albums = $xml->weeklyalbumchart->album;
		$info = array();
		$i = 0;
		foreach($albums as $album){
			$info[$i] = array(
				'name' => $album->name,
				'artist' => $album->artist,
				'url' => $album->url,
				'playcount' => $album->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userArtistChart(){		
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.getweeklyartistchart&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$artists = $xml->weeklyartistchart->artist;
		$info = array();
		$i = 0;
		foreach($artists as $artist){
			$info[$i] = array(
				'name' => $artist->name,
				'url' => $artist->url,
				'playcount' => $artist->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function userTrackChart(){		
		/********************
		Need to add optional time period
		********************/
		// build the api url
		$lastfm = $this->url . '?method=user.getweeklytrackchart&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->weeklytrackchart->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'artist' => $track->artist,
				'url' => $track->url,
				'playcount' => $track->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	/********************
		CHART METHODS
	********************/
	
	function chartHypedArtists(){
		// build the api url
		$lastfm = $this->url . '?method=chart.gethypedartists&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$artists = $xml->artists->artist;
		$info = array();
		$i = 0;
		foreach($artists as $artist){
			$info[$i] = array(
				'name' => $artist->name,
				'img' => $artist->image[2],
				'url' => $artist->url,
				'change' => $artist->percentchange
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function chartHypedTracks(){
		// build the api url
		$lastfm = $this->url . '?method=chart.gethypedtracks&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->tracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'img' => $track->image[2],
				'url' => $track->url,
				'artist' => $track->artist->name,
				'artisturl' => $track->artist->url,
				'change' => $track->percentchange
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function chartLovedTracks(){
		// build the api url
		$lastfm = $this->url . '?method=chart.getlovedtracks&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->tracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'img' => $track->image[2],
				'url' => $track->url,
				'artist' => $track->artist->name,
				'artisturl' => $track->artist->url,
				'loves' => $track->loves
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function chartTopArtists(){
		// build the api url
		$lastfm = $this->url . '?method=chart.gettopartists&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$artists = $xml->artists->artist;
		$info = array();
		$i = 0;
		foreach($artists as $artist){
			$info[$i] = array(
				'name' => $artist->name,
				'img' => $artist->image[2],
				'url' => $artist->url,
				'playcount' => $artist->playcount,
				'listeners' => $artist->listeners
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function chartTopTags(){
		// build the api url
		$lastfm = $this->url . '?method=chart.gettoptags&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tags = $xml->tags->tag;
		$info = array();
		$i = 0;
		foreach($tags as $tag){
			$info[$i] = array(
				'name' => $tag->name,
				'url' => $tag->url,
				'reach' => $tag->reach,
				'tags' => $tag->taggings,
				'summary' => $tag->wiki->summary,
				'description' => $tag->wiki->description
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function chartTopTracks(){
		// build the api url
		$lastfm = $this->url . '?method=chart.gettoptracks&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->tracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'artist' => $track->artist->name,
				'artisturl' => $track->artist->url,
				'url' => $track->url,
				'playcount' => $track->playcount,
				'listeners' => $track->listeners
			);
			
			$i++;
		}
		
		return $info;
	}
	
	/************************
		LIBRARY METHODS
	************************/
	
	function libraryTracks($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=library.gettracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->tracks->track;
		$info = array();
		$i = 0;
		foreach($tracks as $track){
			$info[$i] = array(
				'name' => $track->name,
				'url' => $track->url,
				'artist' => $track->artist->name,
				'album' => $track->album->name,
				'img' => $track->image[2],
				'playcount' => $track->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function libraryArtists($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=library.getartists&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$artists = $xml->artists->artist;
		$info = array();
		$i = 0;
		foreach($artists as $artist){
			$info[$i] = array(
				'name' => $artist->name,
				'url' => $artist->url,
				'img' => $artist->image[2],
				'playcount' => $artist->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	function libraryAlbums($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=library.getalbums&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml document
		$xml = simplexml_load_file($lastfm);
		
		$albums = $xml->albums->album;
		$info = array();
		$i = 0;
		foreach($albums as $album){
			$info = array(
				'name' => $album->name,
				'url' => $album->url,
				'artist' => $album->artist->name,
				'artisturl' => $album->artist->url,
				'img' => $album->image[2],
				'playcount' => $album->playcount
			);
			
			$i++;
		}
		
		return $info;
	}
	
	/************************
		GEO CALLS
	************************/

	function nearbyEvents($l=''){
		/************
		 If you do not specify a location, you will recive all events,
		 to fix this I am using geoplugin (http://geoplugin.com) to get the
		 user's current location.
		 You can of course, still specify a location
		************/
		// set the location, if a parameter was passed back, use that
		if($l !== ""){
			$location = $l;
		}else{
			$geoplugin = 'http://www.geoplugin.net/xml.gp?ip=';
			$userip = $_SERVER['REMOTE_ADDR'];
			$geourl = $geoplugin . $userip;
			$geoxml = simplexml_load_file($geourl);
			$location = $geoxml->geoplugin_city;
		}
		// build the api url
		$lastfm = $this->url . '?method=geo.getevents&location=' . $location . '&api_key=' . $this->apikey;
		// get the xml doc
		$xml = simplexml_load_file($lastfm);
		
		$events = $xml->events->event;
		$info = array();
		$i = 0;
		foreach($events as $event){
			// Turn the time into a 12-hour format
			preg_match('/\d{2}[:]\d{2}[:]\d{2}/',$event->startDate,$time);
			list($fullhour,$minute,$second) = explode(":",$time[0]);
			if($fullhour > 12){
				$hour = ($fullhour - 12) . ":" . $minute . " PM";
			}else{
				$hour = $fullhour . ":" . $minute . " AM";
			}
							
			// Delete time from startdate
			$date = preg_replace('/\d{2}[:]\d{2}[:]\d{2}/','',$event->startDate);
			$date = preg_replace('/[,]/','',$date);
			$date = preg_replace('/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/','',$date);
			$date = trim($date);
			list($day,$month,$year) = explode(" ",$date);
			$date = $month . " " . $day . ", " . $year;
			
			$info[$i] = array(
				'title' => $event->title,
				'artists' => $event->artists->artist,
				'headline' => $event->artists->headliner,
				'venue' => $event->venue->name,
				'location' => $event->venue->location->city,
				'venueUrl' => $event->venue->url,
				'venueWebsite' => $event->venue->website,
				'startdate' => $event->startDate,
				'description' => $event->description,
				'img' => $event->image[2],
				'url' => $event->url,
				'website' => $event->website,
				'tickets' => $event->ticket,
				'tags' => $event->tags->tag
			);
			
			$i++;
		}
		
		return $info;
	}
	
}

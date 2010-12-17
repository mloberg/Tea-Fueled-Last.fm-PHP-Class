<?php
/*************************
**	Name: Tea-Fueled Last.fm PHP Class
**	Author: Matthew Loberg
**	URL: http://mloberg.com/blog/lastfmclass/
**	Author URL: http://mloberg.com
**	Version: 0.3
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
	
	function getUserLoved($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the url
		$lastfm = $this->url . '?method=user.getlovedtracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->lovedtracks->track;
		foreach($tracks as $track){
			// gather track info
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist->name;
			$artisturl = $track->artist->url;
			$img = $track->children();
			$img = $img->image[2];
			
			// echo out the track info
			
			/**
			*	Some tracks do not include an album image,
			*	so we do a check to see if there is an image tag in the xml.
			*	If there is, echo $img.
			**/
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $name . '" width="126" height="126" /></p>';
			}
			echo '<p><a href="http://' . $url . '">' . $name . '</a> by <a href="' . $artisturl . '">' . $artist . '</a></p>';
		}
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
	   
	function getUserRecent($l='',$t=''){
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
		foreach($tracks as $track){
			// gather track info
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist;
			$album = $track->album;
			$img = $track->children();
			$img = $img->image[2];
			
			// echo track info
			// make sure it has an image before echoing an image
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $album . '" width="126" height="126" /></p>';
			}
			echo '<p><a href="' . $url . '">' . $name . '</a> off <em>' . $album . '</em> by ' . $artist . '</p>';
		}
	}
	
	function getUserBanned($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=user.getbannedtracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);

		$tracks = $xml->bannedtracks->track;
		foreach($tracks as $track){
			// gather track info
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist->name;
			$img = $track->children();
			$img = $img->image[2];
			
			// echo track info
			
			/**
			*	Some tracks do not include an album image,
			*	so we do a check to see if there is an image tag in the xml.
			*	If there is, echo $img.
			**/
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $name . '" width="126" height="126" /></p>';
			}
			echo '<p><a href="http://' . $url . '">' . $name . '</a> by ' . $artist . '</p>';
		}
	}
	
	function getUserEvents(){
		/**
		*	This api call is a little weird.
		*	An event has a lot of tags, but not all have to filled in.
		*	This makes sytling the response difficult
		**/
		$lastfm = $this->url . '?method=user.getevents&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$events = $xml->events->event;
		
		// check to see if there are any events
		if($events){
			foreach($events as $event){
				// get event information
				$title = $event->title;
				$artists = $event->artists->artist;
				$headline = $event->artists->headliner;
				$venue = $event->venue->name;
				$location = $event->venue->location->city;
				$venueUrl = $event->venue->url;
				$venueWebsite = $event->venue->website;
				$startdate = $event->startDate;
				$description = $event->description;
				$img = $event->image[2];
				$url = $event->url;
				$website = $event->website;
				$tickets = $event->ticket; // not being used, empty in most
				$tags = $event->tags->tag;
				
				// Turn the time into a 12-hour format
				preg_match('/\d{2}[:]\d{2}[:]\d{2}/',$startdate,$time);
				list($fullhour,$minute,$second) = explode(":",$time[0]);
				if($fullhour > 12){
					$hour = ($fullhour - 12) . ":" . $minute . " PM";
				}else{
					$hour = $fullhour . " AM";
				}
								
				// Delete time from startdate var
				$date = preg_replace('/\d{2}[:]\d{2}[:]\d{2}/','',$startdate);
				$date = preg_replace('/[,]/','',$date);
				$date = preg_replace('/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/','',$date);
				$date = trim($date);
				list($day,$month,$year) = explode(" ",$date);
				$date = $month . " " . $day . ", " . $year;
								
				// echo the info
				if($event->image){
					echo '<p><img src="' . $img . '" alt="' . $title . '" /></p>';
				}
				echo '<p>Event: <a href="' . $url . '"><b>' . $title . '</b></a></p>';
				echo '<p>Headlining: <b>' . $headline . '</b></p>';
				if($event->description){
					echo '<p>' . $description . '</p>';
				}
				echo '<p>All Acts:</p><ul>';
				foreach($artists as $artist){
					echo '<li>' . $artist . '</li>';
				}
				echo '</ul>';
				if($event->venue->website){
					echo '<p>Venue: <a href="' . $venueWebsite . '"><b>' . $venue . '</b></a></p>';
				}else{
					echo '<p>Venue: <a href="' . $venueUrl . '"><b>' . $venue . '</b></a></p>';
				}
				echo '<p>Location: ' . $location . '</p>';
				echo '<p>Date: ' . $date . ' ' . $hour . '</p>';
				if($event->tags){
					echo '<p>Event Tags:</p>';
					echo '<ul>';
					foreach($tags as $tag){
						echo '<li>' . $tag . '</p>';
					}
					echo '</ul>';
				}
				echo '<hr />';
			}
		}else{
			echo $this->user . " has no upcoming events.";
		}
	}
	
	function getUserFriends($l=''){
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=user.getfriends&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$users = $xml->friends->user;
		foreach($users as $user){
			// gather info
			$name = $user->name;
			$realname = $user->realname;
			$img = $user->image[2];
			$url = $user->url;
			
			if($user->image){
				echo "<p><img src=\"$img\" alt=\"$name\" />";
			}
			echo "<p><a href=\"$url\">$name ($realname)</a></p>";
		}
	}
	
	function getUserInfo(){
		/* RETURNS AN ARRAY */
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
	
	function getUserPlaylists(){
		/* RETURNS AN ARRAY */
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
	
	function getUserShouts(){
		/* RETURNS AN ARRAY */
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
	
	function getUserTopAlbums(){
		/* RETURNS AN ARRAY */
		
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
	
	function getUserTopArtists(){
		/* RETURNS AN ARRAY */
		
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
	
	function getUserTopTags(){
		/* RETURNS AN ARRAY */
		
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
	
	function getUserTopTracks(){
		/* RETURNS AN ARRAY */
		
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
	
	/************************
		LIBRARY METHODS
	************************/
	
	function getLibraryTracks($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=library.gettracks&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->tracks->track;
		foreach($tracks as $track){
			// gather track info
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist->name;
			$album = $track->album->name;
			$img = $track->children();
			$img = $img->image[2];
			$playcount = $track->playcount;
			
			// if $playcount is equal to one, we need to set the playcount string to played 1 time instead of 1 times.
			if($playcount == "1"){
				$playcount = "Played " . $playcount . " time.";
			}else{
				$playcount = "Played " . $playcount . " times";
			}
			
			// now echo track info
			// make sure there is an image before echoing it
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $album . '" width="126" height="126" /></p>';
			}
			echo '<p><a href="' . $url . '">' . $name . '</a> off <em>' . $album . '</em> by ' . $artist . '.</p>';
			echo '<p>' . $playcount . '</p>';
		}
	}
	
	function getLibraryArtists($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=library.getartists&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$artists = $xml->artists->artist;
		foreach($artists as $artist){
			// gather artist info
			$name = $artist->name;
			$url = $artist->url;
			$img = $artist->children();
			$img = $img->image[2];
			$playcount = $artist->playcount;
			
			// if $playcount is equal to one, we need to set the playcount string to played 1 time instead of 1 times.
			if($playcount == "1"){
				$playcount = "played " . $playcount . " time.";
			}else{
				$playcount = "played " . $playcount . " times.";
			}
			
			// echo the info
			// make sure there is an image before echoing it
			if($artist->image){
				echo '<p><img src="' . $img . '" alt="' . $name . '" /></p>';
			}
			echo '<p><a href="' . $url . '">' . $name . '</a> ' . $playcount . '</p>';
		}
	}
	
	function getLibraryAlbums($l=''){
		// set the parameters if any were passed
		$limit = $l;
		// build the api url
		$lastfm = $this->url . '?method=library.getalbums&user=' . $this->user . '&limit=' . $limit . '&api_key=' . $this->apikey;
		// get the xml document
		$xml = simplexml_load_file($lastfm);
		
		$albums = $xml->albums->album;
		foreach($albums as $album){
			// gather album info
			$name = $album->name;
			$url = $album->url;
			$artist = $album->artist->name;
			$artisturl = $album->artist->url;
			$img = $album->children();
			$img = $img->image[2];
			$playcount = $album->playcount;
			
			// if $playcount is equal to one, we need to set the playcount string to played 1 time instead of 1 times.
			if($playcount == "1"){
				$playcount = 'played ' . $playcount . ' time.';
			}else{
				$playcount = 'played ' . $playcount . ' times.';
			}
			
			// make sure there is an image before we echo it
			if($album->image){
				echo '<p><img src="' . $img . '" alt="' . $name . '" width="126" height="126" /></p>';
			}
			echo '<p><a href="' . $url . '">' . $name . '</a> by <a href="' . $artisturl . '">' . $artist . '</a> ' . $playcount . '</p>';
		}
	}
	
	/************************
		GEO CALLS
	************************/

	function getNearbyEvents($l=''){
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
		
		// check to see if there are any events
		if($events){
			foreach($events as $event){
				// get event information
				$title = $event->title;
				$artists = $event->artists->artist;
				$headline = $event->artists->headliner;
				$venue = $event->venue->name;
				$location = $event->venue->location->city;
				$venueUrl = $event->venue->url;
				$venueWebsite = $event->venue->website;
				$startdate = $event->startDate;
				$description = $event->description;
				$img = $event->image[2];
				$url = $event->url;
				$website = $event->website;
				$tickets = $event->ticket; // not being used, empty in most
				$tags = $event->tags->tag;
				
				// Turn the time into a 12-hour format
				preg_match('/\d{2}[:]\d{2}[:]\d{2}/',$startdate,$time);
				list($fullhour,$minute,$second) = explode(":",$time[0]);
				if($fullhour > 12){
					$hour = ($fullhour - 12) . ":" . $minute . " PM";
				}else{
					$hour = $fullhour . " AM";
				}
								
				// Delete time from startdate var
				$date = preg_replace('/\d{2}[:]\d{2}[:]\d{2}/','',$startdate);
				$date = preg_replace('/[,]/','',$date);
				$date = preg_replace('/(Mon|Tue|Wed|Thu|Fri|Sat|Sun)/','',$date);
				$date = trim($date);
				list($day,$month,$year) = explode(" ",$date);
				$date = $month . " " . $day . ", " . $year;
								
				// echo the info
				if($event->image){
					echo '<p><img src="' . $img . '" alt="' . $title . '" /></p>';
				}
				echo '<p>Event: <a href="' . $url . '"><b>' . $title . '</b></a></p>';
				echo '<p>Headlining: <b>' . $headline . '</b></p>';
				if($event->description){
					echo '<p>' . $description . '</p>';
				}
				echo '<p>All Acts:</p><ul>';
				foreach($artists as $artist){
					echo '<li>' . $artist . '</li>';
				}
				echo '</ul>';
				if($event->venue->website){
					echo '<p>Venue: <a href="' . $venueWebsite . '"><b>' . $venue . '</b></a></p>';
				}elseif($event->venue->url){
					echo '<p>Venue: <a href="' . $venueUrl . '"><b>' . $venue . '</b></a></p>';
				}else{
					echo '<p>Venue: ' . $venue . '</p>';
				}
				echo '<p>Location: ' . $location . '</p>';
				echo '<p>Date: ' . $date . ' ' . $hour . '</p>';
				if($event->tags){
					echo '<p>Event Tags:</p>';
					echo '<ul>';
					foreach($tags as $tag){
						echo '<li>' . $tag . '</p>';
					}
					echo '</ul>';
				}
				echo '<hr />';
			}
		}else{
			echo " There are no nearby events.";
		}
	}
}
?>
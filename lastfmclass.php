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
	public $url = 'http://ws.audioscrobbler.com/2.0/';
	public $apikey;
	public $user;
	
	/************************
		USER METHODS
	************************/
	
	public function getUserLoved($l=''){
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
	   
	public function getUserRecent($l='',$t=''){
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
	
	public function getUserBanned($l=''){
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
	
	public function getUserEvents(){
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
	
	/************************
		LIBRARY METHODS
	************************/
	
	public function getLibraryTracks($l=''){
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
	
	public function getLibraryArtists($l=''){
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
	
	public function getLibraryAlbums($l=''){
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
	
}
?>
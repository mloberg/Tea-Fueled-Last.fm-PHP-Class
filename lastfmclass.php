<?php
/*
	Name: Tea-Fueled Last.fm PHP Class
	Author: Matthew Loberg
	URL: http://mloberg.com/blog/lastfmclass/
	Author URL: http://mloberg.com
	Version: 0.2
	License: Copyright 2010 Matthew Loberg. Licenced under the MIT licence. More information in licence.txt, readme.txt, and at http://creativecommons.org/licenses/MIT/
	.
	This is a last.fm class I created for making API calls to last.fm.
	Currently only a limited number of API calls are supported right now, but I hope to add more.
	.
*/

class lastFM{

	// Set "global" parameters
	public $url = 'http://ws.audioscrobbler.com/2.0/';
	public $apikey;
	public $user;
	
	
	public function getLoved($l=''){
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
			
			/*
				Some tracks do not include an album image,
				so we do a check to see if there is an image tag in the xml.
				If there is, echo $img.
			*/
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $name . '" /></p>';
			}
			echo '<p><a href="http://' . $url . '">' . $name . '</a> by <a href="' . $artisturl . '">' . $artist . '</a></p>';
		}
	}
	
	/*
		user.getRecentTracks has muliple optional parameters,
		limit, to, and from. More info here, http://www.last.fm/api/show?service=278
		In this method, all parameters are optional, so you can use it as it.
		The two optional parameters are limit, and time back.
		The time back parameter is specified in days.
		One "bug" is if you want to specify the time back, you must also specify a limit.
		You could pass nothing ('') or -1 as a limit to get all.
	*/
	   
	public function getRecent($l='',$t=''){
		// set the parameters if any were passed
		$limit = $l;
		$timeBack = $t;
		
		/*
			Last.fm to and from needs to be specified in UNIX timestamp format in the UTC time zone.
			Since we are specifying the time back (in days), we can set to as the current time stamp.
			
		*/
		
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
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $album . ' /></p>';
			}
			echo '<p><a href="' . $url . '">' . $name . '</a> off ' . $album . ' by ' . $artist . '</p>';
		}
	}
	
	public function getBanned($l=''){
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
			
			/*
				Some tracks do not include an album image,
				so we do a check to see if there is an image tag in the xml.
				If there is, echo $img.
			*/
			if($track->image){
				echo '<p><img src="' . $img . '" alt="' . $name . '" /></p>';
			}
			echo '<p><a href="http://' . $url . '">' . $name . '</a> by ' . $artist . '</p>';
		}
	}

}
?>
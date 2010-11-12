<?php
/*
	Name: Tea-Fueled Last.fm PHP Class
	Author: Matthew Loberg
	URL: http://mloberg.com/blog/lastfmclass/
	Author URL: http://mloberg.com
	Version: 0.2a
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
	
	public function getRecent(){
		// build the api url
		$lastfm = $this->url . '?method=user.getrecenttracks&user=' . $this->user . '&api_key=' . $this->apikey;
		// get the xml file
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->recenttracks->track;
		foreach($tracks as $track){
			// gather track info
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist;
			
			// echo track info
			echo '<p><a href="' . $url . '">' . $name . '</a> by ' . $artist . '</p>';
		}
	}
	
	function getBanned($l=''){
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
<?php
/*
	Name: Tea-Fueled Last.fm PHP Class
	Author: Matthew Loberg
	URL: http://
	Author URL: http://mloberg.com
	Version: 0.1
	License: Copyright 2010 Matthew Loberg. Licenced under the MIT licence. More information in licence.txt, readme.txt, and at http://creativecommons.org/licenses/MIT/
	.
	This is a last.fm class I created for making API calls to last.fm.
	Currently only a limited number of API calls are supported right now, but I hope to add more.
	.
*/

class lastFM{

	// Set "global" parameters
	public $url = 'http://ws.audioscrobbler.com/2.0/';
	private $apikey;
	private $api = '&api_key=' . $apikey;
	public $user;
	
	
	public function get_loved(){
		// build the url
		$lastfm = $this->url . '?method=user.getlovedtracks&user=' . $this->user . $this->api;
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->lovedtracks->track;
		foreach($tracks as $track){
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist->name;
			$artisturl = $track->artist->url;
			$img = $track->children();
			$img = $img->image[2];
			
			echo '<p><img src="' . $img . '" alt="' . $name . '" /></p>';
			echo '<p><a href="http://' . $url . '">' . $name . '</a> by <a href="' . $artisturl . '">' . $artist . '</a></p>';
		}
	}
	
	public function get_recent(){
		// build the api url
		$lastfm = $this->url . '?method=user.getrecenttracks&user=' . $this->user . $this->api;
		$xml = simplexml_load_file($lastfm);
		
		$tracks = $xml->recenttracks->track;
		foreach($tracks as $track){
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist;
			
			echo '<p><a href="' . $url . '">' . $name . '</a> by ' . $artist . '</p>';
		}
	}
	
	function get_banned(){
		// build the api url
		$lastfm = $this->url . '?method=user.getbannedtracks&user=' . $this->user . $this->api;
		$xml = simplexml_load_file($lastfm);

		$tracks = $xml->bannedtracks->track;
		foreach($tracks as $track){
			$name = $track->name;
			$url = $track->url;
			$artist = $track->artist->name;
			
			echo '<p><a href="http://' . $url . '">' . $name . '</a> by ' . $artist . '</p>';
		}
	}

}
?>
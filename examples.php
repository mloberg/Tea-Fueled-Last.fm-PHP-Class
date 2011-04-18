<?php

	include_once('lastfmclass.php');
	// make sure you replace with your information
	$lastfm = new LastFM('your-api-key', 'your-username');
	
	// latest tracks
	$tracks = $lastfm->user('getRecentTracks');
	foreach($tracks['recenttracks']['track'] as $t){
		echo $t['name'].' by '.$t['artist']['#text'].'<br />';
	}
	
	// print out the array
	print_r($lastfm->library('getArtists'));
	
	// you can optionally pass in paramters
	
	$parameters = array(
		'artist' => 'Of Mice & Men'
	);
	print_r($lastfm->artist('getInfo', $parameters));
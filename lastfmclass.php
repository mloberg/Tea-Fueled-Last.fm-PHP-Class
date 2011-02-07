<?php

/**
 * Name: Tea-Fueled Last.fm PHP Class
 * Author: Matthew Loberg
 * URL: http://mloberg.com/blog/lastfmclass/
 * Author URL: http://mloberg.com
 * Version: 0.6
 * License: Copyright 2011 Matthew Loberg. Licenced under the MIT licence. More information in licence.txt, readme.txt, and at http://creativecommons.org/licenses/MIT/
 *
 * This is a last.fm class I created for making API calls to last.fm.
 */

class lastFM{

	/************************
		GLOBAL VARIABLES
	************************/
	private $url = 'http://ws.audioscrobbler.com/2.0/';
	private $apikey;
	private $user;
	private $secret;
	private $callback;
	
	function __construct($api,$user,$secret){
		$this->apikey = $api;
		$this->user = $user;
		$this->secret = $secret;
	}
	
	function callback($url){
		$this->callback = $url;
	}
	
	/********************
		AUTHENTICATION
	********************/
	
	/**
	 * Right now we are storing the token, and secret key in a cookie.
	 * I will extend this later to store in a database.
	**/
	
	private function auth(){
		// find out if we already have a key to use
		if(!isset($_COOKIE['lastfmkey'])){
			// if not, find out where we are in the process
			if(!isset($_COOKIE['lftoken']) && $_GET['token'] == ''){
				// get a token
				if($this->callback != ''){
					// if there is a callback, include that in the api call
					$lastfm = 'http://www.last.fm/api/auth/?api_key=' . $this->apikey . '&cb=' . $this->callback;
				}else{
					$lastfm = 'http://www.last.fm/api/auth/?api_key=' . $this->apikey;
				}
				header("Location: $lastfm");
				exit();
			}elseif(!isset($_COOKIE['lftoken'])){
				// get the token, and set a cookie with it
				$token = $_GET['token'];
				setcookie('lftoken', $token, time()+3600);
				// get a api signiture
				$params = array(
					'method' => 'auth.getSession',
					'token' => $token,
				);
				$sig = $this->signiture($params);
				// get the session key
				$lastfm = $this->url . '?method=auth.getSession&token=' . $token . '&api_key=' . $this->apikey . '&api_sig=' . $sig;
				$xml = simplexml_load_file($lastfm);
				$key = $xml->session->key;
				setcookie('lastfmkey',$key,time()+3600*24*30);
				return $key;
			}else{
				// get a api signiture
				$token = $_COOKIE['lftoken'];
				$params = array(
					'method' => 'auth.getSession',
					'token' => $token,
				);
				$sig = $this->signiture($params);
				// get the session key
				$lastfm = $this->url . '?method=auth.getSession&token=' . $token . '&api_key=' . $this->apikey . '&api_sig=' . $sig;
				$xml = simplexml_load_file($lastfm);
				$key = $xml->session->key;
				setcookie('lastfmkey',$key,time()+3600*24*30);
				return $key;
			}
		}else{
			$key = $_COOKIE['lastfmkey'];
			return $key;
		}
	}
	
	/**
	 * This function generates the a signiture for each call.
	 * Each call has a unique signiture. It's an md5 hash of all the passed parameters
	 */
	
	private function signature($params){
		$params['api_key'] = $this->apikey;
		ksort($params);
		foreach($params as $key => $value){
			$sig_string .= $key . $value;
		}
		$sig_string .= $this->secret;
		$sig = md5($sig_string);
		return $sig;
	}
	
	private function url($params,$json=false){
		if($json) $params['format'] = 'json';
		$params['api_key'] = $this->apikey;
		$lastfm = "{$this->url}?method={$params['method']}";
		unset($params['method']);
		foreach($params as $key => $val){
			$lastfm .= "&{$key}={$val}";
		}
		return $lastfm;
	}
	
	private function json($params){
		$params['format'] = 'json';
		$lastfm = $this->url($params);
		$json = file_get_contents($lastfm);
		$return = json_decode($json, true);
		return $return;
	}
	
	/************************
		USER METHODS
	************************/
	
	function userLoved($l=''){
		$lastfm = "{$this->url}?method=user.getlovedtracks&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		
		return $tracks['lovedtracks']['track'];
	}
	   
	function userRecent($opts=''){
		if(is_array($opts)){
			$l = $opts['limit'];
			$t = $opts['days'];
		}else{
			$l;$t;
		}
		
		// set the timezone to UTC
		date_default_timezone_set('UTC');
		// get the current UNIX timestamp
		$to = time();
		// then figure out the time back in seconds
		$from = time() - (60 * 60 * 24 * $t);
		
		// now see if the a time parameter was passed
		if($t == ""){
			// time back was not specified, run normal
			$lastfm = "{$this->url}?method=user.getrecenttracks&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		}else{
			$lastfm = "{$this->url}?method=user.getrecenttracks&user={$this->user}&limit={$l}&from={$from}&to={$to}&api_key={$this->apikey}&format=json";
		}
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['recenttracks']['track'];
	}
	
	function userBanned($l=''){
		$lastfm = "{$this->url}?method=user.getbannedtracks&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['bannedtracks']['track'];
	}
	
	function userEvents(){
		$lastfm = "{$this->url}?method=user.getevents&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$events = json_decode($json, true);
		return $events['events']['event'];
	}
	
	function userFriends($l=''){
		$lastfm = "{$this->url}?method=user.getfriends&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$friends = json_decode($json, true);
		return $friends['friends']['user'];
	}
	
	function userInfo(){
		$lastfm = "{$this->url}?method=user.getinfo&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$info = json_decode($json, true);
		return $info['user'];
	}
	
	function userPlaylists(){
		$lastfm = "{$this->url}?method=user.getplaylists&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$playlists = json_decode($json, true);
		return $playlists['playlists']['playlist'];
	}
	
	function userShouts(){
		$lastfm = "{$this->url}?method=user.getshouts&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$shouts = json_decode($json, true);
		return $shouts['shouts']['shout'];
	}
	
	function userTopAlbums(){		
		/********************
		Need to add optional time period
		********************/
		$lastfm = "{$this->url}?method=user.gettopalbums&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$albums = json_decode($json, true);
		return $albums['topalbums']['album'];
	}
	
	function userTopArtists(){		
		/********************
		Need to add optional time period
		********************/
		$lastfm = "{$this->url}?method=user.gettopartists&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$artists = json_decode($json, true);
		return $artists['topartists']['artist'];
	}
	
	function userTopTags(){		
		/********************
		Need to add optional time period
		********************/
		$lastfm = "{$this->url}?method=user.gettoptags&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tags = json_decode($json, true);
		return $tags['toptags']['tag'];
	}
	
	function userTopTracks(){
		/********************
		Need to add optional time period
		********************/
		$lastfm = "{$this->url}?method=user.gettoptracks&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['toptracks']['track'];
	}
	
		function userAlbumChart(){		
		$lastfm = "{$this->url}?method=user.getweeklyalbumchart&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$albums = json_decode($json, true);
		return $albums['weeklyalbumchart']['album'];
	}
	
	function userArtistChart(){		
		$lastfm = "{$this->url}?method=user.getweeklyartistchart&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$artists = json_decode($json, true);
		return $artists['weeklyartistchart']['artist'];
	}
	
	function userTrackChart(){		
		$lastfm = "{$this->url}?method=user.getweeklytrackchart&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['weeklytrackchart']['track'];
	}
	
	function userChartList(){
		$lastfm = "{$this->url}?method=user.getweeklychartlist&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$list = json_decode($json, true);
		return $list['weeklychartlist']['chart'];
	}
	
	function userArtistTracks($artist){
		$artist = urlencode($artist);
		$lastfm = "{$this->url}?method=user.getartisttracks&user={$this->user}&artist={$artist}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['artisttracks']['track'];
	}
	
	function userNeighbours($l=''){
		$lastfm = "{$this->url}?method=user.getneighbours&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$neighbours = json_decode($json, true);
		return $neighbours['neighbours']['user'];
	}
	
	function userNewReleases(){
		$lastfm = "{$this->url}?method=user.getnewreleases&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$releases = json_decode($json, true);
		return $releases['albums']['album'];
	}
	
	function userPastEvents(){
		$lastfm = "{$this->url}?method=user.getpastevents&user={$this->user}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$events = json_decode($json, true);
		return $events['events']['event'];
	}
	
	function userTags($tag){
		$lastfm = "{$this->url}?method=user.getpersonaltags&user={$this->user}&tag={$tag}&taggingtype=artist&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tags = json_decode($json, true);
		return $tags['taggings'];
	}
	
	/********************
		USER AUTH METHODS
	********************/
	
	function recentStations(){
		// get the lastfm session key
		$sk = $this->auth();
		// get the signiture
		$params = array(
			'method' => 'user.getRecentStations',
			'sk' => $sk,
			'user' => $this->user
		);
		$sig = $this->signature($params);
		$lastfm = "{$this->url}?method=user.getRecentStations&user={$this->user}&sk={$sk}&api_key={$this->apikey}&api_sig={$sig}&format=json";
		$json = file_get_contents($lastfm);
		$stations = json_decode($json, true);
		return $stations['recentstations']['station'];
	}
	
	function recommendedArtists(){
		$sk = $this->auth();
		$params = array(
			'method' => 'user.getRecommendedArtists',
			'sk' => $sk
		);
		$sig = $this->signature($params);
		$params['api_sig'] = $sig;
		$lastfm = $this->url($params,true);
		$json = file_get_contents($lastfm);
		$artists = json_decode($json, true);
		return $artists['recommendations']['artist'];
	}
	
	function recommendedEvents(){
		$sk = $this->auth();
		$params = array(
			'method' => 'user.getRecommendedEvents',
			'sk' => $sk
		);
		$sig = $this->signature($params);
		$params['api_sig'] = $sig;
		$lastfm = $this->url($params,true);
		$json = file_get_contents($lastfm);
		$events = json_decode($json, true);
		return $events['events']['event'];
	}
	
	function userShout($message,$user=''){
		if($user == '') $user = $this->user;
		$sk = $this->auth();
		$params = array(
			'method' => 'user.shout',
			'sk' => $sk,
			'user' => $user,
			'message' => $message,
		);
		$sig = $this->signature($params);
		$params['api_key'] = $this->apikey;
		$params['api_sig'] = $sig;
		$params['format'] = 'json';
		$data = http_build_query($params);
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		if($result['status'] !== "ok"){
			return $result;
		}else{
			return true;
		}
	}
	
	/********************
		CHART METHODS
	********************/
	
	function chartHypedArtists(){
		$lastfm = "{$this->url}?method=chart.gethypedartists&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$artists = json_decode($json, true);
		return $artists['artists']['artist'];
	}
	
	function chartHypedTracks(){
		$lastfm = "{$this->url}?method=chart.gethypedtracks&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['tracks']['track'];
	}
	
	function chartLovedTracks(){
		$lastfm = "{$this->url}?method=chart.getlovedtracks&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['tracks']['track'];
	}
	
	function chartTopArtists(){
		$lastfm = "{$this->url}?method=chart.gettopartists&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$artists = json_decode($json, true);
		return $artists['artists']['artist'];
	}
	
	function chartTopTags(){
		$lastfm = "{$this->url}?method=chart.gettoptags&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tags = json_decode($json, true);
		return $tags['tags']['tag'];
	}
	
	function chartTopTracks(){
		$lastfm = "{$this->url}?method=chart.gettoptracks&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['tracks']['track'];
	}
	
	/************************
		LIBRARY METHODS
	************************/
	
	function libraryTracks($l=''){
		$lastfm = "{$this->url}?method=library.gettracks&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$tracks = json_decode($json, true);
		return $tracks['tracks']['track'];
	}
	
	function libraryArtists($l=''){
		$lastfm = "{$this->url}?method=library.getartists&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$artists = json_decode($json, true);
		return $artists['artists']['artist'];
	}
	
	function libraryAlbums($l=''){
		$lastfm = "{$this->url}?method=library.getalbums&user={$this->user}&limit={$l}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$albums = json_decode($json, true);
		return $albums['albums']['album'];
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
		$lastfm = "{$this->url}?method=geo.getevents&location={$location}&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$events = json_decode($json, true);
		return $events['events']['event'];
	}
	
	/********************
		ARTIST CALLS
	********************/
	
	function artistInfo($artist){
		$lastfm = "{$this->url}?method=artist.getinfo&artist={$artist}&autocorrect=1&api_key={$this->apikey}&format=json";
		$json = file_get_contents($lastfm);
		$info = json_decode($json, true);
		return $info['artist'];
	}
}

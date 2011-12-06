<?php

	/**
	 * Name: Tea-Fueled Last.fm PHP Class
	 * Author: Matthew Loberg
	 * URL: http://mloberg.com/blog/lastfmclass/
	 * Author URL: http://mloberg.com/
	 * Version: 1.5
	 * License: Copyright 2011 Matthew Loberg. Licenced under the MIT licence. More information in licence.txt, readme.txt, and at http://creativecommons.org/licenses/MIT/
	 */
	
	class Lastfm {
	
		protected static $url = 'http://ws.audioscrobbler.com/2.0/';
		protected static $api_key;
		protected static $secret;
		protected static $user;
		protected static $session;
		
		public function __construct($api, $secret, $session = null){
			self::$api_key = $api;
			self::$secret = $secret;
			self:$session = $session;
		}
		
		public function session($session){
			self::$session = $session;
		}
		
		protected static function sign($params){
			ksort($params);
			$str_to_sign = '';
			foreach($params as $key => $value){
				$str_to_sign .= $key . $value;
			}
			$str_to_sign .= self::$secret;
			return md5($str_to_sign);
		}
		
		protected static function get($params){
			$params['format'] = 'json';
			return json_decode(file_get_contents(self::$url . '?' . http_build_query($params)), true);
		}
		
		protected static function post($params){
			$ch = curl_init(self::$url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			return json_decode($result, true);
		}
		
		/**
		 * Return the Last.fm class
		 */
		
		public static function auth(){
			return new Auth();
		}
		
		public static function __callStatic($method, $params){
			return new LastfmWorker($method);
		}
	
	}
	
	class LastfmWorker extends Lastfm {
	
		private $class;
		
		private static $post = array(
			'album' => array('addTags', 'removeTag', 'share'),
			'artist' => array('addTags', 'removeTag', 'share'),
			'event' => array('attend', 'share', 'shout')
		);
		
		public function __construct($class){
			$this->class = $class;
		}
		
		public function __call($method, $params){
			$params = array_pop($params);
			$params['method'] = $this->class . '.' . $method;
			$params['api_key'] = self::$api_key;
			
		}
	
	}
	
	class Auth extends Lastfm {
	
		public function __construct(){}
		
		public function request($callback = null){
			$call = 'http://www.last.fm/api/auth/?api_key=' . self::$api_key;
			if(!is_null($callback)) $call .= '&cb=' . $callback;
			header("Location: {$call}");
		}
		
		public function getSession($token){
			$params = array(
				'api_key' => self::$api_key,
				'token' => $token,
				'method' => 'auth.getSession'
			);
			$params['api_sig'] = self::sign($params);
			return self::get($params);
		}
	
	}
	
	class Album extends Lastfm {
	
		public function __construct(){}
		
		public function __call($method, $params){
			$params = array_pop($params);
			$params['method'] = 'album.' . $method;
			$params['api_key'] = self::$api_key;
			if(preg_match('/^(addTags|removeTag|share)$/', $method)){
				$params['sk'] = self::$session;
				$params['api_sig'] = self::sign($params);
				return self::post($params);
			}
			return self::get($params);
		}
	
	}
	
	class Artist extends Lastfm {
	
		public function __construct(){}
		
		public function __call($method, $params){
			$params = array_pop($params);
			$params['method'] = 'artist.' . $method;
			$params['api_key'] = self::$api_key;
			if(preg_match('/^(addTags|removeTag|share|shout)$/', $method)){
				$params['sk'] = self::$session;
				$params['api_sig'] = self::sign($params);
				return self::post($params);
			}
			return self::get($params);
		}
	
	}
	
	class User extends Lastfm {
	
		public function __construct(){}
		
		public function __call($method, $params){
			$params = array_pop($params);
			$params['method'] = 'user.' . $method;
			$params['api_key'] = self::$api_key;
			if(preg_match('/^(getRecentStations|getRecommendedArtists|getRecommendedEvents|shout)$/', $method)){
				$params['sk'] = self::$session;
				$params['api_sig'] = self::sign($params);
				if($method == 'shout') return self::post($params);
			}
			return self::get($params);
		}
	
	}

class old_lastFM{

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
				$sig = $this->signature($params);
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
				$sig = $this->signature($params);
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
	
	function user($method,$params=array()){
		switch($method){
			case 'getRecentStations':
			case 'getRecommendedArtists':
			case 'getRecommendedEvents':
				$sk = $this->auth();
				$params['method'] = 'user.'.$method;
				$params['sk'] = $sk;
				$params['user'] = $this->user;
				$sig = $this->signature($params);
				$lastfm = $this->url.'?';
				foreach($params as $key => $value){
					$lastfm .= $key.'='.$value.'&';
				}
				$lastfm .= 'api_sig='.$sig.'&api_key='.$this->apikey.'&format=json';
				//return $lastfm;
				break;
			case 'shout':
				if(empty($params['user'])){
					$user = $this->user;
				}else{
					$user = $params['user'];
				}
				$sk = $this->auth();
				$p = array(
					'method' => 'user.shout',
					'sk' => $sk,
					'user' => $user,
					'message' => $params['message']
				);
				$sig = $this->signature($p);
				$p['api_key'] = $this->apikey;
				$p['api_sig'] = $sig;
				$p['format'] = 'json';
				$data = http_build_query($p);
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
				break;
			default:
				$lastfm = $this->url.'?method=user.'.$method.'&user='.$this->user.'&format=json&api_key='.$this->apikey;
				if(!empty($params)){
					foreach($params as $key => $value){
						$p .= '&'.$key.'='.$value;
					}
					$lastfm .= $p;
				}
		}
		$json = file_get_contents($lastfm);
		return json_decode($json, true);
	}
	
	function chart($method,$params=array()){
		$lastfm = $this->url.'?method=chart.'.$method.'&format=json&api_key='.$this->apikey;
		if(!empty($params)){
			foreach($params as $key => $value){
				$p .= '&'.$key.'='.$value;
			}
			$lastfm .= $p;
		}
		$json = file_get_contents($lastfm);
		return json_decode($json, true);
	}
	
	function library($method,$params=array()){
		$lastfm = $this->url.'?method=library.'.$method.'&user='.$this->user.'&format=json&api_key='.$this->apikey;
		if(!empty($params)){
			foreach($params as $key => $value){
				$p .= '&'.$key.'='.$value;
			}
			$lastfm .= $p;
		}
		$json = file_get_contents($lastfm);
		return json_decode($json, true);
	}
	
	function geo($method,$params=array()){
		switch($method){
			case 'getEvents':
				if(empty($params['location']) && empty($params['lat']) && empty($params['long'])){
					$geo = simplexml_load_file('http://www.geoplugin.net/xml.gp?ip='.$_SERVER['REMOTE_ADDR']);
					$params['location'] = $geo->geoplugin_city;
				}
				$lastfm = $this->url.'?method=geo.getEvents&format=json&api_key='.$this->apikey;
				if(!empty($params)){
					foreach($params as $key => $value){
						$p .= '&'.$key.'='.$value;
					}
					$lastfm .= $p;
				}
				$json = file_get_contents($lastfm);
				return json_decode($json, true);
				break;
			default:
				return 'this call is not yet supported';
		}
	}
	
	function artist($method,$params=array()){
		switch($method){
			
			default:
				$lastfm = $this->url.'?method=artist.'.$method.'&autocorrect=1&format=json&api_key='.$this->apikey;
				if(!empty($params)){
					foreach($params as $key => $value){
						$p .= '&'.$key.'='.urlencode($value);
					}
					$lastfm .= $p;
				}
				$json = file_get_contents($lastfm);
				return json_decode($json, true);
		}
	}

}

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
		
		/**
		 * Return the Last.fm class
		 */
		
		public static function auth(){
			return new LastfmAuth();
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
			'event' => array('attend', 'share', 'shout'),
			'library' => array('addAlbum', 'addArtist', 'addTrack', 'removeAlbum', 'removeArtist', 'removeScrobble', 'removeTrack'),
			'playlist' => array('addTrack', 'create'),
			'radio' => array('tune'),
			'track' => array('addTags', 'ban', 'love', 'removeTag', 'scrobble', 'share', 'unban', 'unlove', 'updateNowPlaying'),
			'user' => array('shout')
		);
		private static $auth = array(
			'radio' => array('getPlaylist'),
			'user' => array('getRecentStations', 'getRecommendedArtists', 'getRecommendedEvents')
		);
		
		public function __construct($class){
			$this->class = strtolower($class);
		}
		
		private static function post($params){
			$ch = curl_init(self::$url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			return json_decode($result, true);
		}
		
		private static function get($params){
			$params['format'] = 'json';
			return json_decode(file_get_contents(self::$url . '?' . http_build_query($params)), true);
		}
		
		public function __call($method, $params){
			$params = array_pop($params);
			$params['method'] = $this->class . '.' . $method;
			$params['api_key'] = self::$api_key;
			if(in_array($method, self::$post[$this->class]) || in_array($method, self::$auth[$this->class])){
				$params['sk'] = self::$session;
				$params['api_sig'] = self::sign($params);
				if(in_array($method, self::$post[$this->class])){
					return self::post($params);
				}
			}
			return self::get($params);
		}
	
	}
	
	class LastfmAuth extends Lastfm {
	
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
			$params['format'] = 'json';
			return json_decode(file_get_contents(self::$url . '?' . http_build_query($params)), true);
		}
	
	}
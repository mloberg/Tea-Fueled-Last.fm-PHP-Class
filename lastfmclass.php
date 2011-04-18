<?php

/**
 * Name: Tea-Fueled Last.fm PHP Class
 * Author: Matthew Loberg
 * URL: http://mloberg.com/blog/lastfmclass/
 * Author URL: http://mloberg.com
 * Version: 1.0
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

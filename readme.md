# Tea-Fueled Last.fm PHP Class

Version 1.0

Author: Matthew Loberg

Author URL: http://mloberg.com/

Licence: Copyright (c) 2011 Matthew Loberg under the MIT Licence (licence.txt)

***

This is a complete library for the Last.fm api. You will need an API account to use this class.

	$lfm = new Lastfm($api_key, $secret);

To call user.getInfo

	$lfm::User()->getInfo();

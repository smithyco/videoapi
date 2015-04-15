<?php

namespace Hubble;
	/**
	 * InsightSoftware.com VideoPlatform VideoAPI
	 *
	 * This class is designed to wrap the functionality of the InsightSoftware.com
	 * VideoPlatform (currently under development). It will provide a basic chaining
	 * (fluent) structure for accessing the API and most of its features.
	 *
	 * Notes:
	 * This is a very basic stubbed release for illustration purposes while the
	 * API itself if finalized. The basic syntax nor the stubbed data structures
	 * should change. The API is being optimized to utilize gzip/deflate and
	 * this class will make use of that via both cUrl and Sockets. I'm using sockets
	 * to provide a lightTouch function to allow for POSTing data without waiting for
	 * a response. My tests using sockets show that an average of 50ms latency can
	 * be expected. In GET retrievals its closer to 250-500ms depending on size of data.
	 * 
	 *
	 * PHP version 5.4+
	 *
	 * @category   Wrapper
	 * @package    VideoAPI
	 * @author     Jonathan Reeves <jon.reeves@insightsoftware.com>
	 * @copyright  2015 InsightSoftware.com Inc.
	 * @version    0.03 (pre-alpha) 
	 */

	class VideoAPI {
		// Settings
		public static $endpoint		= null;
		public static $version		= null;
		public static $secure		= null;
		public static $apikey		= null;
		public static $secret		= null;
		public static $signing		= false;
		public static $compress		= false;
		public static $timeout		= 2000;
		// Data
		public $resource			= null;
		public $id					= null;
		public $where				= null;
		public $sort				= null;
		public $retrieve			= null;
		// Settings Constructor and Static Builder
		public static function Setup($apikey = null, $secret = null, $signing = null, $endpoint = null, $version = null, $secure = null, $compress = null, $timeout = null) {
			// Apply Any Provided Settings
			if (!is_null($apikey) || !is_null($secret)) { self::Authentication($apikey, $secret); }
			if (!is_null($signing)) { self::Signing($signing); }
			if (!is_null($endpoint)) { self::Endpoint($endpoint); }
			if (!is_null($version)) { self::Version($version); }
			if (!is_null($secure)) { self::Secure($secure); }
			if (!is_null($compress)) { self::Compress($compress); }
			if (!is_null($timeout)) { self::Timeout($timeout); }
			// Chain
			return new static;
		}
		// Set Values for Setup
		public static function Authentication($apikey, $secret) {
			self::$apikey = $apikey;
			self::$secret = $secret;
			return new static;
		}
		public static function Signing($on = false) {
			self::$signing = $on;
			return new static;
		}
		public static function Version($version = "v1") {
			self::$version = $version;
			return new static;
		}
		public static function Secure($on = true) {
			self::$secure = $on;
			return new static;
		}
		public static function HTTP($on = true) {
			self::$secure = ($on) ? false : true ;
			return new static;
		}
		public static function HTTPS($on = true) {
			self::$secure = ($on) ? true : false ;
			return new static;
		}
		public static function Compress($deflate = true) {
			self::$compress = $deflate;
			return new static;
		}
		public static function Timeout($milliseconds = 2000) {
			self::$timeout = $milliseconds;
			return new static;
		}
		public static function Endpoint($url) {
			// Get address and Protocol
			$location = self::ParseUrl($url);
			// Store Properties
			self::$endpoint = $location['scheme']."://".$location['host'].":".$location['port'].$location['path'].((isset($location['query'])) ? "?".$location['query'] : "");
			self::$secure = $location['ssl'];
			return new static;
		}
		// Starters
		public static function Projects($id = null) {
			// Fork a new Instance from Current Settings
			$obj = new self;
			// Store Project Id
			$obj->resource = "projects";
			$obj->id = $id;
			// Allow Instaniating for Chaining Language Purposes
			return $obj;
		}
		public static function Videos($id = null) {
			// Fork a new Instance from Current Settings
			$obj = new self;
			// Store Video Id
			$obj->resource = "videos";
			$obj->id = $id;
			// Allow Instaniating for Chaining Language Purposes
			return $obj;
		}
		public static function Pages($id = null) {
			// Fork a new Instance from Current Settings
			$obj = new self;
			// Store Page Id
			$obj->resource = "pages";
			$obj->id = $id;
			// Allow Instaniating for Chaining Language Purposes
			return $obj;
		}
		public static function Sitemaps($domain = null, $ssl = null) {
			// Fork a new Instance from Current Settings
			$obj = new self;
			// Store Sitemap Id
			$obj->resource = "sitemaps";
			$obj->id = $domain;
			// Allow Instaniating for Chaining Language Purposes
			return $obj;
		}
		// Actions
		public function Get(){
			// Replacing for cUrl and fsockopen (when no response needed)
			return file_get_contents(self::$endpoint.self::$version."/".$this->resource."/".( is_null($this->id) ? '' : $this->id ));
		}
		// Request Handlers
		public function Sign($data){}
		public function Send($method, $resource, $data, $retrieve){}
		// Helper Functions
		public static function ParseUrl($url) {
			// Get Positions in String
			$proto = strpos($url, '://');
			$double = strpos($url, '//');
			$single = strpos($url, '/');
			// No Protocol Defined - Use Current
			$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http' ;
			// Check for supported Protocol and resolve if ambiguous
			if ($double === false || $double > $single || $double === 0) {
				// Set Correct Separator
				$separator = ($double === 0) ? ":" : "://" ;
				// Parse with http just to get Port Num
				$parsed = parse_url("http".$separator.$url);
				// If Port is 443 Then Assume HTTPS and Override Protocol
				$protocol = (isset($parsed['port']) && $parsed['port'] == 443) ? "https" : "http" ;
				// Rewrite url
				$url = $protocol.$separator.$url;
			}else{
				// Set Protocol to lowercase of found
				$protocol = strtolower(substr($url, 0, $proto));
				// Support for SSL prefix
				$protocol = ($protocol == 'ssl') ? 'https' : $protocol ;
				// Fail if protocol not http or https
				if ($protocol != 'http' && $protocol != 'https') { throw new Exception('Only HTTP or HTTPS urls supported.'); }
			}
			// Explode url Fragments
			$location = parse_url($url);
			// Normalize Scheme
			$location['scheme'] = strtolower($location['scheme']);
			// Set Port if Undefined
			$location['port'] = (isset($location['port'])) ? $location['port'] : ((strtolower($location['scheme']) == 'http') ? 80 : 443) ;
			// Set custom SSL field
			$location['ssl'] = ($location['scheme'] == 'https') ? true : false ;
			// Return Location Info Array
			return $location;
		}
		public static function ParseHeaders($headers, $toLower = false) {
			// Storage
			$headers = array();
			// Delimit into Array
			foreach (explode("\n", $raw_headers) as $i => $h) {
				// Get Key and Value Pairs
				$h = explode(':', $h, 2);
				// If Value Specified
				if (isset($h[1])) {
					// Get Key and Make LowerCase if requested
					$key = ($toLower) ? strtolower($h[0]) : $h[0] ;
					// Store Value
					$headers[$key] = trim($h[1]);
				}
			}
			// Return Associative Array
			return $headers;
		}
		public static function ParseHTTPResponse($response) {
			// Split Response into Headers and Content
			list($headers, $content) = preg_split("/\R\R/", $response, 2);
			// Return Associate Array
			return array(
				"headers"	=> self::ParseHeaders($headers, true),
				"content"	=> $content
			);
		}
	}
?>
<?php

require_once __DIR__ .'/../vendor/autoload.php';

use Hubble\VideoAPI;

	// Setup Credentials
	define("ENDPOINT",	"http://projects.insightsoftware.com/videoplatform/api/");
	define("VERSION",	"v1");
	define("APIKEY",	"c72e7f991f2df0f57793f5c073d803efa1863ff3");
	define("SECRET",	"773411ffeb78b4b6aae1acbb2b5f30927cf96affa791356c238d027369ce5dba");

	// Setup Connection
	VideoAPI::Setup(APIKEY, SECRET)->Endpoint(ENDPOINT)->Version(VERSION)->Secure()->Compress();

	/* Active */
		// Projects
		// $projects = VideoAPI::Projects()->Get();
		// $projects = VideoAPI::Projects("1io32vcyxv")->Get();

		// Videos
		// $videos = VideoAPI::Videos()->Get();
		$videos = VideoAPI::Videos("wregzm53c7")->Get();

		// Pages
		// VideoAPI::Pages()->Get();
		// VideoAPI::Pages("zyxwvutsrq")->Get();

		// Sitemaps
		// VideoAPI::Sitemaps()->Get();
		// VideoAPI::Sitemaps("abcdefghij")->Get();

	/* Upcoming */
		// Projects
		// VideoAPI::Projects()->Stats()->Get();

		// Videos
		// VideoAPI::Videos()->SetOrigin(tags, pageTitle, pageDescription, url, customData, force);

	// Output Testing
	header("Content-Type: application/json");
	echo isset($projects) ? $projects : '' ;
	echo isset($videos) ? $videos : '' ;
	echo isset($pages) ? $pages : '' ;
	echo isset($sitemaps) ? $sitemaps : '' ;
?>
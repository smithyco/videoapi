
	-----------------------------------------
 	-----------------------------------------
 	InsightSoftware.com VideoPlatform Project
 	-----------------------------------------
 	-----------------------------------------

	Introduction
	------------
	We have been looking for a better way to host our own videos and provide numerous frontends
	to present the content from. Development of a Learning Center Web Experience is underway and
	was a primary focus for finding and augmenting a good Video Hosting Platform. Like-wise our
	Corporate Site and Marketing Team have a real need for a platform like this too. Their needs
	focus more on SEO and Integration with existing services like Salesforce and Hubspot.

	The desire was to research, test and identify an ideal Video Hosting Platform and Player that
	would provide the best functionality for our needs, and augment it with any additional pieces
	to provide a robust RESTful API that we can use across all our projects.

	As with any external services with APIs, we have to consider rate limitations, bandwidth
	restrictions and also latency.

	The VideoAPI component represents the exposed RESTful API of our (in development) VideoPlatform
	and everything it wraps.



	Goals
	-----
	There are numerous reasons for us to wrap the Video Hosting Service's API. The main one however
	is managing the API hits and keeping the Rate Limitation within our Allowances. Here are some of
	goals of the project:
		- Simple PHP Class: to allow any projects to utilize the API
		- Caching: 
			- to manage API Hits / Rate Limits
			- to reduce latency
			- to allow better querying of data
		- Aggregation of Stats: Most platforms will only allow stats on a per Resource basis
		- Searching: The ability to search flexibly for Videos
		- Management of Uptime: Keeping the service online 
		- Augment: Provide additional Information about Videos
		- Captions: Hijack captions to provide custom Interactive Transcriptions Plugin
		- Self-Registering Pages: Allow Pages to Claim it is the origin of a Video, for citing
		- Sitemaps: Auto Generate Sitemaps from Claimed Pages
		- Lookups: Using Pages to find Original Sources of Videos in the DB



	Services
	--------
	Wistia is the Video Hosting Platform we are using. It is integrated with Hubspot and by proxy
	Salesforce. Any Video load/play activity will be associated to HubSpot Visitor Records and can
	be also hopefully viewed within Salesforce.



	Data Structure
	--------------
	Here is a Basic rundown of the Objects/Resources at play:
		Videos			- Describes a Video from Wistia
			Stats			- A reworded Wistia Object for Statistics for a given Video or Project
			Captions		- A collection of Raw Caption Text in SRT format
			OpenGraph		- A set of HTML Codes to put on the page for SEO purposes
			Poster			- A Image Resource for illustrating the video without embedding it
			Embed			- A piece of HTML Code that places the Video on the page, types can be
							  iframe,api,seo... more maybe coming
		Projects		- Describes a Folder in Wistia that the Video is inside of
		Pages			- Describes a Page that a Video is hosted on
			Origin			- A Page that is Flagged as the Original Source
			                  of the Video (first one to claim/register)
		Sitemaps		- Describes a Collection of Pages on the same Domain



	Pre-Alpha Endpoint
	------------------
	We have put online a basic RESTful API that is open and available today. It has limited
	caperbilities and some of the objects are just stubbed out for now.

	http://projects.insightsoftware.com/videoplatform/api/v1/



	Resources
	---------
	Right now there are only a few resources available...
		/projects				- All Projects
		/projects/1io32vcyxv	- Project by ID
		/videos					- All Videos
		/videos/mw89ii9yvd		- Video by ID

	You can see some additional ones that are only stubbed out here...
		/videos/mw89ii9yvd/embed
		/pages
		/pages/1234657890
		/sitemaps
		/sitemaps/insightsoftware.com



	Implementation Considerations
	-----------------------------
	The API itself is still under development, and this is a quick represenation of some of the
	resources and their responses. The responses aren't likely to change much, but we are in the
	process of implementing support for a lot of unmentioned parameters and resources.

	We are in the process of adding a security layer (policy signing) and compression (gzip,
	deflate), which should be implemented in the next commit.

	The endpoint is currently hosted on our labs server and will move at a later date.

	With all this in mind, we hope to provide and maintain a PHP Class to allow separation from
	the REST api itself, and keep it simple to upgrade as development continues.



	PHP Class
	---------
	The PHP Class is not fleshed out yet, but the basic fluent (chainable) syntax is there for
	the setup process right now. We have added a few examples that hit the endpoint in a simple
	fashion in order to illustrate behaviour while we finalize it.

	Right now a 'file_get_contents' is in place to handle the request temporarily, but the actual
	process will be handled by cUrl and Sockets. cUrl will handle most of the requests and we only
	really bring in Sockets in order to reduce latency on POST requests which don't require a
	response (e.g.. Setting Origin of a Video on a Page). In our testing we reduced latency from
	~250ms to ~50ms by using Sockets. The code is this is written just not implmented.

	Using the PHP Class will hopefully be pretty simple, however there is a mix of Static and
	Instantiated Methods. To Setup the Video API we use the STATIC Setup method, and can then chain
	any settings to it. This sets up any future requests for this page/script.

		VideoAPI::Setup(APIKEY, SECRET)->Endpoint(ENDPOINT)->Version(VERSION)->Secure()->Compress();

	Next we can Use the Projects or Videos STATIC Method to instantiate a new VideoAPI Object which
	we can then setup for the request.

		$videos = VideoAPI::Videos("wregzm53c7")->Get();

	We created the Get() method execute the query at the end of the chain... we are still evaluating
	whether the fluent (chaining) approach is right for these requests. We are pretty confident that
	we will stick to this structure, but there are alot of pieces of functionality coming soon, that
	we need to confirm works ok this way... here was an early example of functionality that would
	potentially need to work (as you can see its not ideal):

		VideoAPI::Videos()
					->Where()
						->Projects(id, section)
						->Related(id)
						->Search(term)
						->Filter([tag, tag])
					->Arrange()
						->Sort(dimension, order)
						->Limit(count, page)
					->Select()
						->Info(detail)
						->Poster(width, height, play_button)
						->Stats(start, end)
						->Captions(language)
						->Pages()
						->Origin()
						->OpenGraph()
						->Embed(type, options, player_config)
					->Get();

	With this all in mind, its possible that there could be some changes to the Query syntax above,
	but hopefully it should be minor changes on your end. The Setup Process is pretty much set in
	stone now though.



	Coporate Site Requirements
	--------------------------
	Today, with the labs/public Endpoint and this simple PHP Class, it should be possible to at least
	retrieve a Project by ID, a List of Videos and Video by ID:
		/projects
		/videos
		/videos/wregzm53c7

	I'm not familier with all the intentions of how the API will need to be used for the corporate
	site, but I see perhaps two urgent requirements for now..

		1) Get List of all Videos
		   This will allow all Videos to be pulled back so that can be listed in a management
		   page and a user could choose which to Use or create a page from.

		2) Get Video by ID
		   After a Page is created that knows about a Video ID, it can Request that Video by
		   ID from within its templated code.

	We are working on a request to allow Listing of Videos only within a Project as it is highly
	likely that the Corporate-Site will only pull from a single Project, known by hardcoded ID.

	We are also trying to implement an endpoint to Register / SetOrigin for a Video when it is
	loaded on a page. The idea is to store a record of the Page where this video is Embeded, so
	that it can be reverse looked-up as well as aid in generating a Video Sitemap dynamically... 
	This might get implemented as a POST/PUT request over Sockets to reduce latency, or we may
	package it into a API side task that is triggered when a GET request is made for a video.
	Although its not really RESTful to do so.



	More Requirements
	-----------------
	It would be good to know what the most urgent queries/resources are needed.
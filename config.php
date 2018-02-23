<?php
/* Contacts */
$version = 'LTLStats/4.2';
$site_url = '';				// the URL where you'll display this dashboard
$contact_email = ''; 		// your contact address

/* Caching */
/* Using cached data is safest, but means you won't get the most up-to-date.
   You should only change this to false if you've made sure that you're only
   running your php scripts at appropriate times (eg using a cron job). */
$use_cached_data = true;

/* Local folders */
$cache_folder = dirname(__FILE__) . '\\cache\\';
$connections_folder = dirname(__FILE__) . '\\connections\\';
$cookies_folder = dirname(__FILE__) . '\\cookies\\';
$csv_folder = dirname(__FILE__) . '\\csvs\\';   // local filepath
$csv_download_folder = '';						// full web-accessible filepath to the same folder, eg 'http://example.com/LTLstats/csvs/';

/* Styles */
$widgetColour = 'sequential'; // 'plain' or 'sequential' or 'by_tags' - NB if a widget has multiple tags, the colour of the alphabetically-last tag will be applied
$darkColours = array('#4aaa42','#0069b4','#a877b2','#b94d30','#261c02');
$paleColours = array('#b3d88c','#b2cee7','#b5b2d9','#ffdd00','#dadada');
$darkChart = array('#4aaa42','#0069b4','#a877b2','#b94d30','#261c02');
$paleChart = array("rgba(179,216,140,0.2)","rgba(178,206,231,0.2)","rgba(181,178,217,0.2)","rgba(255,221,0,0.2)","rgba(218,218,218,0.2)");

/* Charts */
$js['chart.js'] = '';		// eg 'js/Chart.js' - download from http://www.chartjs.org/
$js['wordcloud2.js'] = '';	// eg 'js/wordcloud2.js' - download from http://timdream.org/wordcloud2.js/
$js['tags'] = 'js/tags.js.php';

/* Access */
$proxy = '';				// eg 'http://ezproxy.lincoln.ac.nz/login?url=';
$proxy_audit_path = '';		// eg 'C:\\\\ezproxy\\audit\\';
$proxy_groups = array();	// eg array("Staff/students" => "Auto everyone restricted", "Community" => "Auto everyone", "On-campus" => "Auto");

$alma_domain = ''; 			// eg 'https://api-ap.hosted.exlibrisgroup.com';
$alma_url = $alma_domain . '/almaws/v1/analytics/reports';
$alma_apikey = '';			// your API key for Alma Analytics
$alma_limit = '50';			// maximum number of rows you want to return - note resumption tokens not yet supported

$alma_hours_url = $alma_domain . '/almaws/v1/conf/open-hours';
$alma_hours_apikey = '';	// your API key for Alma Hours
$alma_hours_scope = ''; 	// eg 'LIU';

$altmetric_url = 'https://www.altmetric.com/api/v1/summary_report/at?num_results=100&key=';
$altmetric_apikey = '';		// your API key for Altmetric

$dspace_url = ''; 			// eg 'https://researcharchive.lincoln.ac.nz/statistics?XML';

$lh3_url = ''; 				// eg 'https://sg.libraryh3lp.com/2011-12-03/';
$lh3_cookies = $cookies_folder . 'libraryh3lp.txt';
$lh3_username = '';			// your LibraryH3lp username
$lh3_password = '';			// your LibraryH3lp password

$mrbs_url = ''; 			// eg 'https://library2.lincoln.ac.nz/rooms/';

$primo_domain = '';			// eg 'https://api-ap.hosted.exlibrisgroup.com' or = $alma_domain;
$primo_url = $primo_domain . '/primo/v1/analytics/reports';
$primo_apikey = '';			// your API key for Alma Analytics
$primo_limit = '50';		// maximum number of rows you want to return - note resumption tokens not yet supported

$scopus_url = 'https://api.elsevier.com/content/affiliation/affiliation_id/';
$scopus_apikey = '';		// your API key for Scopus
$scopus_instid = ''; 		// for your institution eg '60006625';

$supersaas_schedules = array(	
	array('123', 'Staff'),	// Schedule ID number and display name
	array('678', 'Student')	// (ID is at end of URL found from Dashboard -> Configure )
	);
$supersaas_account = '';	// your account name (admin username)
$supersaas_password = '';	// your admin password

$careerhub_url = '';		// eg 'https://careerhub.lincoln.ac.nz/';
$careerhub_host = '';		// eg 'careerhub.lincoln.ac.nz';
$careerhub_id = ''			// provided by CareerHub Support
$careerhub_secret = '';		// provided by CareerHub Support
$careerhub_scope = '';		// provided by CareerHub Support

?>
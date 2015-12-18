<?php
/* Contacts */
$version = 'LTLStats/2.1';
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
$darkColours = array('4aaa42','0069b4','a877b2','b94d30','261c02');
$paleColours = array('b3d88c','b2cee7','b5b2d9','ffdd00','dadada');

/* Access */
$proxy = '';				// eg 'http://ezproxy.lincoln.ac.nz/login?url=';
$proxy_audit_path = '';		// eg 'C:\\\\ezproxy\\audit\\';
$proxy_groups = array();	// eg array("Staff/students" => "Auto everyone restricted", "Community" => "Auto everyone", "On-campus" => "Auto");

$alma_domain = ''; 			// eg 'https://api-ap.hosted.exlibrisgroup.com';
$alma_url = $alma_domain . '/almaws/v1/analytics/reports';
$alma_apikey = '';			// your API key for Alma Analytics

$alma_hours_url = $alma_domain . '/almaws/v1/conf/open-hours';
$alma_hours_apikey = '';	// your API key for Alma Hours
$alma_hours_scope = ''; 	// eg 'LIU';

$altmetric_url = 'http://www.altmetric.com/api/v1/summary_report/at?num_results=100&key=';
$altmetric_apikey = '';		// your API key for Altmetric

$dspace_url = ''; 			// eg 'https://researcharchive.lincoln.ac.nz/statistics?XML';

$lh3_url = ''; 				// eg 'https://sg.libraryh3lp.com/2011-12-03/';
$lh3_cookies = $cookies_folder . 'libraryh3lp.txt';
$lh3_username = '';			// your LibraryH3lp username
$lh3_password = '';			// your LibraryH3lp password

$scopus_url = 'http://api.elsevier.com/content/affiliation/affiliation_id/';
$scopus_apikey = '';		// your API key for Scopus
$scopus_instid = ''; 		// eg '60006625';

?>
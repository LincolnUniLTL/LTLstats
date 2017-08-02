LTLstats
======
A php dashboard for displaying statistics harvested by common library APIs, as tables or colourful charts.

May be pronounced "Little Stats". This repository actually contains two interwoven applications:

1. LTLstats proper: all the files except `warning_note.js.php`
  Creates a web-based dashboard to display tables of libraryland statistics gathered from a variety of APIs. See for example https://library2.lincoln.ac.nz/dashboard/
  
2. WarningNote: `warning_note.js.php` + `cache.php` + `connections/exlibris_status.php`
  This can be added to your Ex Libris Primo footer to provide an automated notice at the top of the page if the Ex Libris Status page notes any major issues with Alma or Primo functionality. There's also the option to override the automatic behaviour with a manual notice.
  
Separate instructions on each of these below.

Versioning
----------
v. 3.3
* adds integration with wordcloud2.js
* adds CareerHub connection
* adds function to sort data by selected columns
* BUGFIX: deals with ampersands in Ex Libris APIs

v. 3.2
* adds SuperSaaS connection - see note in Troubleshooting below
* merges two Scopus examples in `to_schedule.php`

v. 3.1.1
* modifies Wikipedia connection to enable reporting on multiple sites

v. 3.1
* adds Primo Analytics connection (in `exlibris_analytics.php` renamed from `alma.php`)
* number of Alma/Primo Analytics results can be increased in `config.php`

v. 3.0
* adds integration with Chart.js including downloading as PNG
* adds MRBS connection
* adds mergeMonthYear and convertDates data functions

v. 2.1
* adds EZproxy connection
* styling: safer width code; highlight key values

v. 2.0
* separates the API access from the data display, massively improving pageload time of data display
* adds functionality to let viewers download data as CSV files
* bugfix for Scopus connection in December
* adds Alma open hours connection
* adds generic OAI connection

v. 1.1
* reduces redundant code
* adds Scopus connection

v. 1.0 was the initial code.

LTLstats
------------
Scheduled jobs (eg `to_schedule.php`) connect to various APIs (in `connections/`) to gather and save your libraryland statistics into csv format (in `csvs/`). Caching raw API output (using `cache.php` and storing files in `cache/`) is optional. Cookies are stored in `cookies/`. A web dashboard (`index.php`) with a basic style (`layout.css.php`) then displays everything in table format or as charts (with `Chart.js` integration).

Currently the connection files available are to:

* Alma hours REST API
* Altmetric REST API
* CareerHub API with OAuth bearer token
* DSpace statistics XML view (tested in DSpace 3.1 XMLUI)
* Ex Libris analytics (Alma analytics REST API and Primo analytics REST API)
* Ex Libris Status page, screenscraped
* EZproxy audit logs (iff on same server as website)
* LibraryH3lp REST API
* MRBS csv reports
* OAI feeds - total number of items
* Scopus REST API
* Wikipedia search API

### Getting started###

1. Give appropriate read/write permissions for the `cache/`, `csvs/`, and `cookies/` directories.

2. To enable bar graphs etc, download the latest version of `Chart.js` from http://www.chartjs.org/ and `wordcloud2.js` from http://timdream.org/wordcloud2.js/ into the `js/` directory

3. Fill out details in config.php. While you're setting up, it's a good idea to keep $use_cached_data = true

4. Edit your `to_schedule.php` file with whatever modules you want to include. You might have multiple files depending on when or how often you want to retrieve data. Specific paths to Alma reports, Altmetric groups, etc go in here. Here's also where you do advanced fiddling with which columns display and how, and which style of graph/chart if `Chart.js` and/or `wordcloud2.js` is enabled. (See below.) You can run these pages in a web-browser to pull the data in, and run `index.php` to see the final display for users.

5. When you're satisfied, change your `config.php` to `$use_cached_data = true;` and set up scheduled tasks / cron jobs to run the `to_schedule.php`-type files at your preferred schedule.

6. Link users to `index.php`.

### A little more advanced ###
`data_functions.php` contains a bunch of functions to munge and format the data in a table. So then each div can take the raw API data ($rowset) and perform functions to keep only certain columns, display totals, and finally format in a table with headers. Available functions include:

* csv2array($string) - turns a CSV table into an array
* obj2arr($object) - turns an object into an array
* sortByColumn($array,$n) - sorts an array by the column with index $n (usually $n=1 gives you the second column)
* keepRight($array,$n) - returns the rightmost $n columns of an array, eg where $n=1 you'll get the last column only. Similarly:
* keepLeft($array,$n)
* keepTop($array,$n)
* keepBottom($array,$n)
* getColumns($rowset, $columns) - for an array $rowset, returns which columns are listed in the array $columns, eg if $columns={0,4} then you'll get the first and fifth columns. Similarly:
* getRows($rowset, $rows)
* swapColRow($rowset) - switches your rows into columns and vice versa, eg turning a vertical table into a horizontal one
* mergeMonthYear($rowset,$month_column,$year_column) - merges a month column and year column into a single date column
* convertDates($rowset,$column,$from,$to) - converts dates in a given column between formats stated as per http://php.net/manual/en/function.date.php
* totalColumn($rowset, $columnarray) - creates a "Total" row, summing the values in any column listed in $columnarray
* matrix($rowset,$groupCol,$valueCol) - the most powerful and clunkily coded. (I'm convinced there's a better way but haven't nutted it out yet.) Imagine you've got a table:

|purple|cars|  23|  
|:-----|:---|---:|  
|purple|fans|   2|  
|purple|hats|  11|  
|orange|cars|  19|  
|orange|fans|   6|  
|orange|hats|  42|
 
But you want a table:  

|    |purple|orange|  
|----|-----:|-----:|  
|cars|    23|    19|  
|fans|     2|     6|  
|hats|    11|    42|

So $groupCol is the column number for the values you want in each column grouping - ie purple/orange, ie column 0. $valueCol is the column number for the values that are actually values - ie 23,3,11,19,6,42, ie column 2.

* mergeTables($rowsetarray,$headers,$groupCol,$valueCol) - creates a matrix (as above) but starting with two or more similarly structured tables ($rowsetarray) and an array of $headers. Eg  
    $rowset[0] = 
	
|cars|  23|  
|:---|---:|  
|fans|   2|  
|hats|  11|  

    $rowset[1] =   
	
|cars|  19|  
|:---|---:|  
|fans|   6|  
|hats|  42|

  $headers = {"purple","orange"}

$valueCol remains the column number for the actual values, so here (23,2,11,etc) - it would be 1. Since {"purple","orange"} aren't in the original tables, they get added as a new column during the function, so $groupCol should be equal to however many columns are in the original - in this case 2.

Normal php array functions will work as well, eg sorting. Mix and match according to your original data and desired result. When you're done:

* doTable($id,$title,$note,$headers,$rowset,$format) - displays it for test purposes, and calls a function to save it as a csv file that can be used by `index.php`. If blank, $format defaults to "Table". "None" prevents it displaying on the `index.php`. If `Chart.js` is enabled, other values can include: "Bar", "Line", "Radar", "Pie", "Doughnut", "Polar". If `wordcloud2.js` is enabled, you can use "Wordle".

You may want to edit `legendTemplate` in `Chart.js`. If you do so, note this value occurs multiple times in the code as templates are generated differently depending on the chart type, so you can't just copy/paste your code into all of them!


WarningNote
------------
1. Make sure that `connections/exlibris_status.php` has the names of your own Alma/Primo servers.
2. `cache/` needs appropriate read/write permissions.
3. Paste `<script src="http://your.domain.com/warning_note.js.php" type="text/javascript"></script>`  (with appropriate changes to the path) into your Primo footer. If you don't already have a footer, this is controlled via Primo BackOffice > Ongoing Configuration > Views Wizard > [your view] > Tiles Configuration > Static HTML > All Pages Footer

To add a manual note, edit the value "theAlert" in `warning_note.js.php` - the note should display immediately (upon refresh) at the top of your Primo page. This will replace any automated note.

Automated notes will appear when the Ex Libris status page warns of 'Performance issues','Service disruption', or 'Scheduled maintenance' for your Alma/Primo servers, so you won't see these too often. To test that it's successfully scraping the page, you can create a bookmarklet:   
  `javascript:alert('Alma:'+status_A+'\nPrimo:'+status_P);`  
and activate this while on any Primo page: it will alert the current status for each of your Alma and Primo instances.

Note that the page is scraped once a minute only to avoid overloading the server, and the results are cached.

Caveat emptor
-------------

An important limitation is that LTLstats doesn't currently deal with resumption tokens for Alma data, or give you any warning that the results are incomplete... The maximum number of rows to return can be edited in `config.php` though.

The environment this was written for requires backslashes for filepaths eg  
`$filepath = dirname(__FILE__) . '\\cache\\' . $filename;`  
If your environment is different you'll need to do a find-replace or some such. Enjoy!

Troubleshooting
---------------

* *SuperSaaS*: We had trouble accessing supersaas.com, something to do with IPv6. If you have similar problems, try adding
`curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);`
into the `getData` function in `data_functions.php`

* *Blank charts* using Chart.js - make sure your `$id`s don't have any hyphens in them. Underscores are fine.

Potential for development
-------------------------

1. Adding more connections - limited only by API availability (and coding time).
1. Would be neat if Ex Libris made a status API so screenscraping wasn't necessary. :-)
1. Enable categorisation of widgets so users can view a subset depending on interests.

Time may or may not allow any of these, so happy for anyone else to contribute code along these lines! :-)
LTLstats
======
A php dashboard for displaying statistics harvested by common library APIs

May be pronounced "Little Stats". This repository actually contains two interwoven applications:

1. LTLstats proper: all the files except `warning_note.js.php`
  This creates a web dashboard (index.php) with a basic style (layout.css). It connects to various APIs (in /connections) to gather and display your libraryland statistics. These are cached for 24 hours (using cache.php; storing files in /cache). Cookies are stored in /cookies.
  
2. WarningNote: `warning_note.js.php` + cache.php + /connections/exlibris_status.php
  This can be added to your Ex Libris Primo footer to provide an automated notice at the top of the page if the Ex Libris Status page notes any major issues with Alma or Primo functionality. There's also the option to override the automatic behaviour with a manual notice.
  
More instructions on each of these below.

LTLstats
------------
Currently the connection files available are to:

* Alma analytics REST API
* Altmetric REST API
* DSpace statistics XML view (tested in DSpace 3.1 XMLUI)
* Ex Libris Status page, screenscraped
* LibraryH3lp REST API
* Wikipedia search API

For almost all of these you will need to open the connection file to modify your API domain name, add your API key or username/password, specify your Alma/Primo server name, etc.

In index.php you should identify your webpage/contact email in getData($url) line 22. From line 241 onwards (ie where the divs start) you'll also need to specify the specific Alma reports, Altmetric groups, and domain name to search in Wikipedia.

Check the obvious, that you've given appropriate read/write permissions for the /cache and /cookies directories.

### A little more advanced ###
index.php also contains a bunch of functions to munge and format the data in a table. So then each div can take the raw API data ($rowset) and perform functions to keep only certain columns, display totals, and finally format in a table with headers. Available functions include:

* csv2array($string) - turns a CSV table into an array
* obj2arr($object) - turns an object into an array
* keepRight($array,$n) - returns the rightmost $n columns of an array, eg where $n=1 you'll get the last column only. Similarly:
* keepLeft($array,$n)
* keepTop($array,$n)
* keepBottom($array,$n)
* getColumns($rowset, $columns) - for an array $rowset, returns which columns are listed in the array $columns, eg if $columns={0,4} then you'll get the first and fifth columns. Similarly:
* getRows($rowset, $rows)
* swapColRow($rowset) - switches your rows into columns and vice versa, eg turning a vertical table into a horizontal one
* totalColumn($rowset, $columnarray) - creates a "Total" row, summing the values in any column listed in $columnarray
* matrix($rowset,$groupCol,$valueCol) - the most powerful and clunkily coded. (I'm convinced there's a better way but haven't nutted it out yet.) Imagine you've got a table:  
`  purple cars 23  
  purple fans 2  
  purple hats 11  
  orange cars 19  
  orange fans 6  
  orange hats 42  `  
But you want a table:  
`        purple orange  
  cars  23     19  
  fans  2      6  
  hats  11     42  `  
So $groupCol is the column number for the values you want in each column grouping - ie purple/orange, ie column 0. $valueCol is the column number for the values that are actually values - ie 23,3,11,19,6,42, ie column 2.

* mergeTables($rowsetarray,$headers,$groupCol,$valueCol) - creates a matrix (as above) but starting with two or more similarly structured tables ($rowsetarray) and an array of $headers. Eg  
`  $rowset[0] =  
    cars 23  
    fans 2  
    hats 11  
  $rowset[1] =   
    cars 19  
	fans  6  
	hats 42  
  $headers = {"purple","orange"}  `  
$valueCol remains the column number for the actual values, so here (23,2,11,etc) - it would be 1. Since {"purple","orange"} aren't in the original tables, they get added as a new column during the function, so $groupCol should be equal to however many columns are in the original - in this case 2.

* formatTable($rowset,($headerarray)) - turns an array into an html table. If there's a $headerarray, it will make these into the column headers <th>; if there's a total row it will give this its own class.

Mix and match according to your original data and desired result.

WarningNote
------------
1. Make sure that /connections/exlibris_status.php has the names of your own Alma/Primo servers.
2. Obviously /cache needs appropriate read/write permissions.
3. Paste `<script src="http://your.domain.com/`warning_note.js.php`" type="text/javascript"></script>`  (with appropriate changes to the path) into your Primo footer. If you don't already have a footer, this is controlled via Primo BackOffice > Ongoing Configuration > Views Wizard > [your view] > Tiles Configuration > Static HTML > All Pages Footer

To add a manual note, edit the value "theAlert" in `warning_note.js.php` - the note should display immediately (upon refresh) at the top of your Primo page. This will replace any automated note.

Automated notes will appear when the Ex Libris status page warns of 'Performance issues','Service disruption', or 'Scheduled maintenance' for your Alma/Primo servers, so you won't see these too often. To test that it's successfully scraping the page, you can create a bookmarklet:   
  `javascript:alert('Alma:'+status_A+'\nPrimo:'+status_P);`  
and activate this while on any Primo page: it will alert the current status for each of your Alma and Primo instances.

Note that the page is scraped once a minute only to avoid overloading the server, and the results are cached.

Caveat emptor
-------------

The environment this was written for requires backslashes for filepaths eg  
`$filepath = dirname(__FILE__) . '\\cache\\\\' . $filename;`  
If your environment is different you'll need to do a find-replace or some such. Enjoy!

Potential for development
-------------------------

1. Adding more connections - limited only by API availability (and coding time).
1. Would be neat if Ex Libris made a status API so screenscraping wasn't necessary. :-)
1. Including a link to let users download table as csv.
1. Using a pie chart plugin eg http://www.chartjs.org/ to make displays even prettier

Time may or may not allow any of these, so happy for anyone else to contribute code along these lines! :-)
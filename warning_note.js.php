/* Add an automated warning note to the top of the Primo page eg for system down.
   Link to this file in your Primo footer with <script src="http://your.domain.com/warning_note.js.php" type="text/javascript"></script>
   Needs styling in your css for div#warningNote 
   Requires connections/exlibris_status.php to connect to the Ex Libris status page
   which in turn requires cache.php to cache this page for 60 seconds so it's not requested too often
   Can override with a manual warning by editing variable theAlert at top of code.
   */

theAlert = "";
		// eg = "<strong>Search is not working at the moment.</strong> Our tech experts are working hard on it so please try again soon. You can still access 'My Account' and renew items.";

function warningNote(warningText) {
	var warningPlace = document.getElementById("contentEXL");
	var warningNote = document.createElement("div");
	warningNote.id = "warningNote";
	warningNote.innerHTML = warningText;
	warningPlace.parentNode.insertBefore(warningNote,warningPlace);
}

<?php 
	require_once('connections/exlibris_status.php');
?>
status_A = '<?php echo $status_A; ?>';
note_A = '<?php echo $note_A; ?>';
status_P = '<?php echo $status_P; ?>';
note_P = '<?php echo $note_P; ?>';

if (theAlert == "") {
	if (status_A == "Performance issues" || status_A == "Service disruption" || status_A == "Scheduled maintenance") {
		theAlert = theAlert + '<p>Automated note: <strong>' + status_A + '</strong>';
		if (status_A == "Scheduled maintenance") {
			theAlert = theAlert + ' in progress';
		} else {
			theAlert = theAlert + ' detected';
		}
		theAlert = theAlert + '. Some functions (eg requests, renewals, availability statuses, and/or links to full-text) may be ';
		if (status_A == "Performance issues") {
			theAlert = theAlert + 'slow';
		} else {
			theAlert = theAlert + 'temporarily unavailable';
		}
		theAlert = theAlert + '.</p>';
		}
	if (status_P == "Performance issues" || status_P == "Service disruption" || status_P == "Scheduled maintenance") {
		theAlert = theAlert + '<p>Automated note: <strong>' + status_P + '</strong>';
		if (status_P == "Scheduled maintenance") {
			theAlert = theAlert + ' in progress';
		} else {
			theAlert = theAlert + ' detected';
		}
		theAlert = theAlert + '. Some functions (especially search-related) may be ';
		if (status_P == "Performance issues") {
			theAlert = theAlert + 'slow';
		} else {
			theAlert = theAlert + 'temporarily unavailable';
		}
		theAlert = theAlert + '.</p>';
	}
	if (theAlert != "") {
		theAlert = theAlert + '<p>Full service will be restored as soon as possible. We apologise for the inconvenience.</p>';
	}
}

if (theAlert != "") {
	warningNote(theAlert);
}
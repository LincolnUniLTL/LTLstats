if(document.loaded) {
    init_tags();
} else {
    if (window.addEventListener) {  
        window.addEventListener('load', init_tags, false);
    } else {
        window.attachEvent('onload', init_tags);
    }
}

function init_tags() {
/* Identifies all tags used on the page */
	tags = getElementsByClass('tag');
	list = new Array();
	for (var i=0; i < tags.length; i++) {
		t = tags[i].className.substr(8);
		if (list.indexOf(t) === -1) {
			list[list.length] = t;
		}
	}
	if (list.length > 0) {
		list.sort();
	/* Creates tag menu above all widgets */
		var d = document.createElement("div");
		d.id = "tags_div";
		d.className = "tags";
		d.innerHTML = "<span>See only widgets about: </span>";
		c = document.getElementById("statcontainer");
		c.parentNode.insertBefore(d, c);
	/* Adds tags to menu */
		list[list.length] = "all";
		for (var i=0; i < list.length; i++) {
			var s = document.createElement("span");
			s.className = 'tag tag_'+list[i];
			s.id = 'tag_'+list[i];
			s.innerHTML = list[i].replace("_"," ");
			d.appendChild(s);
		}
	/* Adds action to all tags in both menu and widgets */
		tags = getElementsByClass('tag');
		for (var i=0; i < tags.length; i++) {
			tags[i].onclick = function(){showTagged(this.className.substr(8).replace(" selected",""));};
		}
<?php
		include("../config.php");
		if ($widgetColour === 'by_tags') {
?>
	/* CSS tags */
		var css = '';
		var darkColors = new Array(<?php
		foreach ($darkColours as $d) {
			echo '"'.$d.'",';
		}
?>);
		var paleColors = new Array(<?php
		foreach ($paleColours as $p) {
			echo '"'.$p.'",';
		}
?>);
		for (var i=0; i < list.length; i++) {
		// Creates style rules for each tag
			css += '.tag_'+list[i]+' { color: black; border: .1em solid '+darkColors[i % darkColors.length]+'; }\n';
			css += '.tag_'+list[i]+' { background-color: '+paleColors[i % paleColors.length]+'; }\n';
			css += '.tag_'+list[i]+'.selected { color: white; font-weight: bold; background-color: '+darkColors[i % darkColors.length]+'; }\n';			
			css += '.tag_'+list[i]+':hover { color: white; background-color: '+darkColors[i % darkColors.length]+'; }\n';
		// Creates style rules for each widget - coloured by tag alphabetically nearest Z...
			css += '.tagged_'+list[i]+' {border: .2em solid '+darkColors[i % darkColors.length]+'; }\n';
			css += '.tagged_'+list[i]+' {background-color: '+paleColors[i % paleColors.length]+'; }\n';
			css += '.tagged_'+list[i]+' h4 {background-color: '+darkColors[i % darkColors.length]+'; }\n';
			css += '.tagged_'+list[i]+' span.value {border: .1em solid '+darkColors[i % darkColors.length]+'; color: '+darkColors[i % darkColors.length]+'; }\n';
			css += '.tagged_'+list[i]+' canvas {border: .1em solid '+darkColors[i % darkColors.length]+'; }\n';
		}
	// Adds stylesheet to head
		var style = document.createElement('style');
		style.id = "tag_style";
		if (style.styleSheet) {
			style.styleSheet.cssText = css;
		} else {
			style.appendChild(document.createTextNode(css));
		}
		document.getElementsByTagName('head')[0].appendChild(style);
<?php
		}
?>
	/* If a tag is included in the URL when the page is first loaded, let the charts load then show only appropriately tagged widgets */
		if(window.location.hash) {
			setTimeout(function() { showTagged(window.location.hash.replace("#","")); }, 1000);
		}
	}
}

function showTagged(tag) {
	document.location.hash = tag;
	allw = getElementsByClass('statdiv');
	if (tag == "all") {
		for (var i=0; i < allw.length; i++) {
			allw[i].style.display = "block";
		}
	} else {
		widgets = getElementsByClass('tagged_'+tag);
		for (var i=0; i < allw.length; i++) {
			if (widgets.indexOf(allw[i]) == -1) {
				allw[i].style.display = "none";
			} else {
				allw[i].style.display = "block";
			}
		}
	}
	allt = getElementsByClass('tag');
	for (var j=0; j < allt.length; j++) {
		allt[j].className = allt[j].className.replace(" selected","");
	}
	allt = getElementsByClass('tag_'+tag);
	for (var j=0; j < allt.length; j++) {
		allt[j].className += " selected";
	}
}

function getElementsByClass (string,containerId) {  // Returns array
  var classElements = new Array();
  ( containerId === undefined ) ? containerId = document : containerId = document.getElementById(containerId);
  var allElements = containerId.getElementsByTagName('*');
  for (var i = 0; i < allElements.length; i++) {
    var multiClass = allElements[i].className.split(' ');
    for (var j = 0; j < multiClass.length; j++)
      if (multiClass[j] === string)
        classElements[classElements.length] = allElements[i];
  }
  return classElements;
}
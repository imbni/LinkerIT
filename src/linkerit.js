var check_element = "code";
var check_newline = "<br>";
var check_mode = "onload"; // onload, onlink
var check_link_text = "Click to check links";


if (typeof window.jQuery === "undefined") {
	document.write('<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"><\/script>');
	if (typeof Prototype !== "undefined") {
		document.write('<script>jQuery.noConflict();<\/script>');
	}
}



jQuery(document).ready(function() {
	if (check_mode == "onload") {
		linkerit_check();
	}
	else if (check_mode == "onlink") {
		jQuery(check_element).each(function() {
			jQuery(this).prepend('<a href class="check_link">' + check_link_text + '</a>' + check_newline);
		});
		jQuery(".check_link").click(function() {
			linkerit_check();
			return false;
		});
	}
	
});

function linkerit_check() {
	jQuery(check_element).each(function() {
		var list = jQuery(this).html().split(check_newline);
		jQuery.each(lista, function(n, line) {
			line = line.replace(/<\/?[^>]+(>|$)/g, "");
			if(jQuery.trim(elem).match(/https?:\/\/[www\.]*(mediafire|ddlstorage|filefactory|sharpfile|turbobit|rapidshare|hotfile|easybytez|uploaded|ul|uploading|rapidgator|netload|netfolder|glumbouploads|bitshare|depositfiles|uploadstation|filecloud|share-online|fiberupload|lumfile|billionuploads|ncrypt).+/gi)) {
				/*jQuery.ajax({
					method: "POST",
					url: "linkerit/linkerit.php",
					data: {url: line}
				}, function(res) {
					//
				});*/
				console.log(line);
			};
		});

	});
}


function linkerit_set(linkerit_orig, linkerit_result) {
	if (linkerit_result == "ok") {
		jQuery("code").each(function() {
			jQuery(this).html(jQuery(this).html().replace(linkerit_orig, linkerit_orig + " <img src='http://linkerit.youontech.net/images/y.png' title='Link online' style='vertical-align: middle' alt='' />"));
		});
	}
	else if (linkerit_result == "ko") {
		jQuery("code").each(function() {
			jQuery(this).html(jQuery(this).html().replace(linkerit_orig, linkerit_orig + " <img src='http://linkerit.youontech.net/images/n.png' title='Link offline' style='vertical-align: middle' alt='' />"));
		});
	}
	else if (linkerit_result.substr(0,2) == "n;") {
		jQuery("code").each(function() {
			jQuery(this).html(jQuery(this).html().replace(linkerit_orig, linkerit_orig + " <img src='data:image/png;base64,"+linkerit_result.split(";")[1]+"' style='vertical-align: middle' alt='' />"));
		});
	}
	
}



if (typeof window.jQuery === "undefined") {
    var script = document.createElement('script');
    script.onload = function(){
        if (typeof Prototype !== "undefined") {
            jQuery.noConflict();
        }
        linkerit_prepare();
    }
    script.src = 'http://code.jquery.com/jquery-latest.min.js';
    document.getElementsByTagName('head')[0].appendChild(script);   
}


function linkerit_prepare() {
	jQuery(document).ready(function() {
		if (check_mode == "onload") {
			linkerit_check();
		}
		else if (check_mode == "onlink") {
			jQuery(check_element).each(function() {
				jQuery(this).prepend('<a href class="check_link">' + check_link_text + '</a>' + check_newline);
			});
			jQuery(".check_link").on('click', function(event) {
				event.preventDefault();
				linkerit_check();
				jQuery(".check_link").fadeOut();
			});
		}
		
	});
}



function linkerit_check() {
	jQuery(check_element).each(function() {
		var list = jQuery(this).html().split(check_newline);
		jQuery.each(list, function(n, line) {
			line = line.replace(/<\/?[^>]+(>|$)/g, "");
			if(jQuery.trim(line).match(/https?:\/\/[www\.]*(mediafire|ddlstorage|filefactory|sharpfile|turbobit|rapidshare|hotfile|easybytez|uploaded|ul|uploading|rapidgator|netload|netfolder|glumbouploads|bitshare|depositfiles|uploadstation|filecloud|share-online|fiberupload|lumfile|billionuploads|ncrypt).+/gi)) {
				jQuery.ajax({
					type: "POST",
					url: "linkerit/linkerit.php",
					data: {url: line},
					success: function(res) {
						console.log(res);
						if (res != null) {
							if (res[0] == "file") {
								if (res[1].substr(0,5) == "https")
									res[1] = res[1].replace(/https:/, "");
								else
									res[1] = res[1].replace(/http:/, "");
								if (res[2] == true) {
									jQuery(check_element).each(function() {
										jQuery(this).html(jQuery(this).html().replace(res[1], res[1] + " <img src='linkerit/online.png' style='vertical-align: middle' alt='' />"));
									});
								}
								else {
									jQuery(check_element).each(function() {
										jQuery(this).html(jQuery(this).html().replace(res[1], res[1] + " <img src='linkerit/offline.png' style='vertical-align: middle' alt='' />"));
									});
								}
								
							}
							else if (res[0] == "folder") {
								jQuery(check_element).each(function() {
									jQuery(this).html(jQuery(this).html().replace(res[1], res[1] + " <img src='data:image/png;base64," + res[3] + "' style='vertical-align: middle' alt='' />"));
								});
							}
						}
					}
				});
			};
		});

	});
}




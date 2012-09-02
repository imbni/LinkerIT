<?php
/* LinkerIT link checker
Version: 1.02
02/09/2012
linkerIT.YouOnTech.net for informations
linkerIT.YouOnTech.net/ticket for support
info@YouOnTech.net subject: LinkerIT */


isset($_POST['url']) or exit;
header('Content-type: application/json');


class linkerit {

	function __construct($url) {
		$this->url = trim($url);
		preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url) or exit;

		// regex patterns
		// [0] => file [1] => folder
		$this->regex_data = array(
			"mediafire" => array("#http://[www\.]*mediafire\.com/[download\.php\?|file/|\?]+(.*)/*#", "#http://[www\.]*mediafire\.com/\?(.*)/*#"),
			"ddlstorage" => array("#http://[www\.]*ddlstorage\.com/[a-z0-9]{12}/*(.*)#",
				"#http://[www\.]*ddlstorage\.com/folder/[\w]+#"),
			"filefactory" => array("#http://[www\.]*filefactory\.com/file/[a-z0-9]{12}/?(n/.+)*$#", ""),
			"sharpfile" => array("#http://[www\.]*sharpfile\.com/[a-z0-9]{12}/*(.*)#",
				"#http://[www\.]*sharpfile\.com/folder/[\w]+#"),
			"turbobit" => array("#http://[www\.]*turbobit\.net/(.+)\.html#",
				"#http://[www\.]*turbobit\.net/download/folder/(\d+)#"),
			"rapidshare" => array("#https?://[www\.]*rapidshare\.com/files/([\d]+)/(.+)#", ""),
			"hotfile" => array("#https?://[www\.]*hotfile\.com/dl/\d+/\w+/[.+/*]?#",
				"#https?://[www\.]*hotfile\.com/list/\d+/\w+#"),
			"easybytez" => array("#http://[www\.]*easybytez\.com/[a-z0-9]{12}(/.+)?#",
				"#http://[www\.]*easybytez.com/users/.+/\d+#"),
			"uploaded" => array("#(http://[www\.]*uploaded\.(to|net)/file/[\w]+/?)|(http://[www\.]*ul\.to/^f[\w]+/?)#",
				"#http://[www\.]*(uploaded|ul)\.(to|net)/(folder|f)/[a-z0-9]+#"),
			"uploading" => array("#http://[www\.]*uploading\.com/files/(get/)?\w+(/*.+)?/*#", ""),
			"rapidgator" => array("#http://[www\.]*rapidgator\.net/file/\d+(/[^/<>]+\.html)?#",
				"#http://[www\.]*rapidgator\.net/folder/\d+/.+?\.html#"),
			"glumbouploads" => array("#http://[www\.]*glumbouploads\.com/[a-z0-9]{12}(/.+)?#",
				"#http://[www\.]*glumbouploads\.com/users/.+/\d+#"),
			"netload" => array("#http://[www\.]*netload\.in/datei(\w+)(/.+)?\.htm#",
				"#http://[www\.]*netfolder\.in/folder\.php\?folder_id\=[\w]{7}|http://[www\.]*netfolder\.in/[\w]{7}/.*?#"),
			"bitshare" => array("#http://[www\.]*bitshare\.com/files/[a-z0-9]{8}/.+?\.html#",
				"#http://[www\.]*bitshare\.com/\?d=[a-z0-9]+#"),
			 "depositfiles" => array("#https?://[www\.]*depositfiles\.com/files/\w+#",
			 	"#http://?[www\.]*depositfiles\.com/folders/(.+)#"),
			 "uploadstation" => array("#http://[www\.]*uploadstation\.com/files?/\w+(/.+)?#",
			 	"#http://[www\.]*uploadstation\.com/list/\w+(/.+)?#"),
			 "filecloud" => array("#http://[www\.]*filecloud\.ws/[a-z0-9]{12}(/.+)?#",
			 	"#http://[www\.]*filecloud\.ws/users/.+/\d+#"),
			 "ncrypt" => array("", "#http://[www\.]*ncrypt\.in/folder\-.+#"),
			 "shareonline" => array("#http://[www\.]*share\-online\.biz/dl/[\w]+#", ""),
			 "fiberupload" => array("#http://[www\.]*fiberupload\.com/[a-z0-9]{12}(/.+)?#",
				"#http://[www\.]*fiberupload\.com/users/.+/\d+#"),
			 "lumfile" => array("#http://[www\.]*lumfile\.com/[a-z0-9]{12}(/.+)?#",
				""),
			 "billionuploads" => array("#http://[www\.]*billionuploads\.com/[a-z0-9]{12}(/.+)?#",
				"#http://[www\.]*billionuploads\.com/users/.+/\d+#")
		);
		$this->analyze();
	}

	protected function analyze() {
		foreach ($this->regex_data as $key => $value) {
			if ($value[0] != "" && preg_match($value[0] . "i", $this->url, $this->regex_result)) {
				$func = "file_" . $key;
				$this->$func();
				break;
			}
			elseif ($value[1] != "" && preg_match($value[1] . "i", $this->url, $this->regex_result)) {
				$func = "folder_" . $key;
				$this->$func();
				break;
			}
		}
	}

	function xml_load($xml) {
		$xml = @simplexml_load_file($xml);
		return $xml;
	}

	function get($link, $return_follow = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		if ($result === false) exit();
		if ($return_follow) return array($result, curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
		curl_close($ch);
		return $result;
	}

	function post($link, $params, $header = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($header !== false) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$result = curl_exec($ch);
		if ($result === false) exit;
		curl_close($ch);
		return $result;
	}

	function contains($page, $yes, $no) {
		if (stristr($page, $yes)) return true;
		elseif (stristr($page, $no)) return false;
	}

	function count_elements($page, $element) {
		if (!function_exists("file_get_html")) include("simple_html_dom.php");
		$page = str_get_html($page) or exit();
		$n = 0;
		foreach ($page->find($element) as $a) {
			$n++;
		}
		return $n;
	}

	function element_number($page, $element) {
		if (!function_exists("file_get_html")) include("simple_html_dom.php");
		$page = str_get_html($page) or exit;
		return preg_replace("/[^0-9]*/", "", @$page->find($element, 0)->plaintext);
	}

	function print_status($status) {
		$this->results_array = array("file", $this->url, $status);
		echo json_encode($this->results_array);
	}

	function print_number($number) {
		$im = imagecreate(50, 14);
		imagecolortransparent($im, imagecolorallocate($im, 0, 0, 0));
		imagestring($im, 2, 0, 0,  "$number files", imagecolorallocate($im, 50, 205, 50));
		ob_start();
		imagepng($im);
		$contents =  ob_get_contents();
		ob_end_clean();
		//echo "<img src='data:image/png;base64,".base64_encode($contents)."' />";
		imagedestroy($im);
		$this->results_array = array("folder", $this->url, intval($number), base64_encode($contents));
		echo json_encode($this->results_array);
	}


	function file_mediafire() {
		// http://www.mediafire.com/?ec3x475fs4l5y8l
		// http://www.mediafire.com/download.php?ec3x475fs4l5y8l
		// http://www.mediafire.com/file/ec3x475fs4l5y8l
		$xml = $this->xml_load("http://www.mediafire.com/api/file/get_info.php?quick_key=" . $this->regex_result[1]);
		if ($xml !== false) {
			if ($xml->result == "Success") $this->print_status(true);
			else $this->print_status(false);
		}
		else {
			$xml = $this->xml_load("http://www.mediafire.com/api/folder/get_info.php?folder_key=" . $this->regex_result[1]);
			if ($xml !== false) {
				if ($xml->result == "Success") $this->print_number($xml->folder_info->file_count);
			}
			else $this->print_status(false);
		}
	}

	function file_ddlstorage() {
		// http://www.ddlstorage.com/karb0e0z7b07
		// http://www.ddlstorage.com/zt8gmtlcd0kk
		$result = $this->post("http://www.ddlstorage.com/checkfiles.html", "list=" . $this->url . "&op=checkfiles");
		$result = $this->contains($result, "found</font>", "not found!</font>");
		if ($result === true)
			$this->print_status(true);
		elseif ($result === false)
			$this->print_status(false);

	}
	function folder_ddlstorage() {
		// http://www.ddlstorage.com/folder/X2evmwB3OS
		$number = $this->count_elements($this->get($this->url) ,"a[style=text-decoration:none;]");
		$this->print_number($number);
	}


	function file_filefactory() {
		$result = $this->post("http://www.filefactory.com/tool/links.php", "links=" . $this->url . "&func=links");
		$result = $this->contains($result, "greenCheck", "redThumb");
		if ($result === true)
			$this->print_status(true);
		elseif ($result === false)
			$this->print_status(false);
	}

	function file_sharpfile() {
		// http://sharpfile.com/z17gamuc9lc9/2ec192e899816a7a1ac42f8e47568015.jpg.html
		// http://sharpfile.com/yhkab6o95b5b/p.txt.html
		$result = $this->post("http://www.sharpfile.com/check-files.php", "url_list==" . $this->url);
		$result = $this->contains($result, "- Found</font>", "- Not Found</font>");
		if ($result === true)
			$this->print_status(true);
		elseif ($result === false)
			$this->print_status(false);

	}
	function folder_sharpfile() {
		// http://www.sharpfile.com/folder/2y9vpcr3wfk8
		$number = $this->count_elements($this->get($this->url) ,"tr[align=center]");
		$this->print_number($number);
	}


	function file_turbobit() {
		// http://turbobit.net/mgxuii5d6o7i.html
		// http://turbobit.net/ff468wcunc3t.html
		$result = $this->post("http://turbobit.net/linkchecker/csv", "links_to_check=" . $this->url);
		$result = explode(",", $result);
		if (trim($result[1]))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_turbobit() {
		// http://turbobit.net/download/folder/1292520
		$result = $this->get("http://turbobit.net/downloadfolder/gridFile?id_folder=" . $this->regex_result[1]);
		$result = json_decode($result);
		$this->print_number($result->records);
	}


	function file_rapidshare() {
		// http://rapidshare.com/files/3694982920/2ec192e899816a7a1ac42f8e47568015.jpg
		// https://rapidshare.com/files/3962859489/image.png
		$result = $this->get("http://api.rapidshare.com/cgi-bin/rsapi.cgi?sub=checkfiles&files=" . $this->regex_result[1] . "&filenames=" . $this->regex_result[2]);
		$result = explode(",", $result);
		if (trim($result[4]))
			$this->print_status(true);
		else
			$this->print_status(false);
	}


	function file_hotfile() {
		// https://hotfile.com/dl/165550363/581c77f/2ec192e899816a7a1ac42f8e47568015.jpg.html
		// https://hotfile.com/dl/165550687/e866121/phpbb.sql.html
		$result = $this->get("http://api.hotfile.com/?action=checklinks&links=" . $this->url);
		$result = explode(",", $result);
		if ($result[1])
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_hotfile() {
		// https://hotfile.com/list/2074143/e0864bc
		$result = $this->get($this->url);
		if (!preg_match("#\((\d+) files\)#", $result, $r)) exit;
		$this->print_number($r[1]);
	}


	function file_easybytez() {
		// http://www.easybytez.com/n0cuz4pkrz41
		// http://www.easybytez.com/1p04d9dm15kx
		$result = $this->get($this->url);
		if ($this->contains($result, '<input type="submit" name="method_free" value="Free Download" class="btn">', '<li class="title"><h1>File not available</h1></li>'))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_easybytez() {
		// http://www.easybytez.com/users/NincoNanco/44967/%20Goal%202%20-%20Vivere%20Un%20Sogno
		// http://www.easybytez.com/users/crazyzone/50285/E%20nata%20un4%20star
		$n = $this->element_number($this->get($this->url), "small");
		if ($n == 0)
			$this->print_number($this->count_elements($this->get($this->url), "div[class=link]"));
		else
			$this->print_number($n);
	}


	function file_uploaded() {
		// http://uploaded.to/file/l8algxwa
		// http://uploaded.to/file/5rwq9jcn
		$result = $this->get($this->url, true);
		if (strstr($result[1], "/404") or strstr($result[1], "/410"))
			$this->print_status(false);
		else
			$this->print_status(true);
	}
	function folder_uploaded() {
		// http://uploaded.net/f/qvddwd
		$result = $this->get($this->url);
		$this->print_number($this->element_number($result, "span[title=containing files]"));
	}



	function file_uploading() {
		// http://uploading.com/files/f5cfe1c6
		// http://uploading.com/files/get/c5143731/2ec192e899816a7a1ac42f8e47568015.jpg
		$result = $this->post("http://uploading.com/filechecker/?ajax", "urls=" . $this->url, array("X-Requested-With: XMLHttpRequest"));
		if ($this->contains($result, "result clearfix ok", "result clearfix failed"))
			$this->print_status(true);
		else
			$this->print_status(false);
	}


	function file_rapidgator() {
		// http://rapidgator.net/file/29895526/Screenshot_1.png.html
		// http://rapidgator.net/file/17906592/ilfatto_20120612_www.rapidstoday.com_leech.pdf.html
		$result = $this->get($this->url);
		if ($this->contains($result, "Downloading:", "File not found"))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_rapidgator() {
		// http://rapidgator.net/folder/500678/Dragon_crusa_bip.html
		$result = $this->get($this->url);
		$n = $this->count_elements($result, "a[href^=/file/]");
		$this->print_number($n);
	}


	function file_glumbouploads() {
		// http://glumbouploads.com/uyree73kaj0u.html
		// http://glumbouploads.com/9s4lh3x5u6tw.html
		$result = $this->get($this->url);
		if ($this->contains($result, "You have requested: ", "File Not Found"))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_glumbouploads() {
		// http://glumbouploads.com/users/dade92/23524/Dark.Shadows.2012.iTALiAN.MD.HDTV.XviD-BmA
		$n = $this->element_number($this->get($this->url), "small");
		if ($n == 0)
			$this->print_number($this->count_elements($this->get($this->url), "div[class=link]"));
		else
			$this->print_number($n);
	}



	function file_netload() {
		// http://netload.in/datei0OTdSxHVuE/2ec192e899816a7a1ac42f8e47568015.jpg.htm
		$id = $this->regex_result[1];
		$result = $this->get("http://api.netload.in/info.php?auth=Rjr22w855MN2HzcBATPAfBmD8Qtz24w4&file_id=" . $id);
		$result = explode(";", $result);
		if (trim($result[3]) == "online")
			$this->print_status(true);
		elseif (trim($result[3]) == "offline")
			$this->print_status(false);
	}
	function folder_netload() {
		// http://netfolder.in/KcpR2xu/A.TEAM.2010.720P
		// http://netfolder.in/folder.php?folder_id=KcpR2xu
		$result = $this->get($this->url);
		$this->print_number( $this->count_elements($result, "a[class^=Link_]") );
	}


	function file_bitshare() {
		// http://bitshare.com/files/8q1n09bg/phpbb.sql.html
		// http://bitshare.com/files/dzyffw8p/download-youontech.bat.html
		$result = $this->post( "http://bitshare.com/api/openapi/general.php", "action=getFileStatus&files=" . $this->url );
		$result = explode("#", $result);
		if (trim($result[1]) == "online")
			$this->print_status(true);
		elseif (trim($result[1]) == "offline")
			$this->print_status(false);
	}
	function folder_bitshare() {
		// http://bitshare.com/?d=3x26mf21
		$result = $this->get($this->url);
		$this->print_number( $this->count_elements($result, "td[style*=width:160px]") );
	}


	function file_depositfiles() {
		// http://depositfiles.com/files/hcmp2zuby
		// http://depositfiles.com/files/gdd7arnut
		$result = $this->post($this->url, "gateway_result=1");
		if ($this->contains($result, "gold_speed_promo_block hide_download_started", "no_download_message"))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_depositfiles() {
		// http://depositfiles.com/folders/5MBDNNVN1
		$n = 0;
		for ($i = 0; ; $i++) {
			$result = json_decode($this->post( "http://depositfiles.com/api/download/folder", "folder_id=".$this->regex_result[1]."&page=".$i ));

			if (count($result->data) == 0 or $result->status == "Error")
				break;
			$n += count($result->data);
		}
		$this->print_number($n);
	}


	function file_uploadstation() {
		// http://www.uploadstation.com/file/5gsTyW7/phpbb.sql
		$result = $this->post("http://www.uploadstation.com/check-links.php", "urls=" . $this->url);
		if ($this->contains($result, 'col4">Available', 'col4">Not available'))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_uploadstation() {
		// http://uploadstation.com/list/GeJqjvv
		$result = $this->get($this->url);
		$n = $this->element_number($result, "div[class=title col col1]");
		$this->print_number($n);
	}


	function file_filecloud() {
		// http://www.filecloud.ws/qqxh9yugneqj
		// http://www.filecloud.ws/618ydxrtm685
		$result = $this->post("http://www.filecloud.ws/?op=checkfiles", "list=".$this->url."&op=checkfiles");
		if ($this->contains($result, '<td style="color:green;">Found</td>', '<td style="color:red;">Not found!</td>'))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_filecloud() {
		// http://www.filecloud.ws/users/pampurio97/266/abc
		$n = $this->element_number($this->get($this->url), "small");
		if ($n == 0)
			$this->print_number($this->count_elements($this->get($this->url), "div[class=link]"));
		else
			$this->print_number($n);
	}



	function folder_ncrypt() {
		// http://ncrypt.in/folder-uC8CqU4a
		// http://ncrypt.in/folder-uC8Cq
		$result = $this->post("http://ncrypt.in/api_status.php", "link=" . $this->url);
		$result = explode(";", $result);
		@$this->print_number($result[3]);
	}


	function file_shareonline() {
		// http://www.share-online.biz/dl/9T5B2I8M1DNV
		// http://www.share-online.biz/dl/4CPEL58M6UO deleted
		$result = $this->post("http://api.share-online.biz/linkcheck.php", "links=" . $this->url);
		$result = explode(";", $result);
		if ($result[1] == "OK")
			$this->print_status(true);
		else
			$this->print_status(false);
	}


	function file_fiberupload() {
		// http://fiberupload.com/86axacaqesm1
		$result = $this->post("http://fiberupload.com/checkfiles.html", "list=".$this->url."&op=checkfiles");
		if ($this->contains($result, "found</font>", "not found!</font>"))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_fiberupload() {
		// http://fiberupload.com/users/pampurio97/9524/test 201 links
		$n = $this->element_number($this->get($this->url), "small");
		if ($n == 0)
			$this->print_number($this->count_elements($this->get($this->url), "div[class=link]"));
		else
			$this->print_number($n);
	}


	function file_lumfile() {
		// http://lumfile.com/hnwz9094c592
		$result = $this->post("http://lumfile.com/checkfiles.html", "list=".$this->url."&op=checkfiles");
		if ($this->contains($result, '<td style="color:green;">Found</td>', '<td style="color:red;">Not found!</td>'))
			$this->print_status(true);
		else
			$this->print_status(false);
	}


	function file_billionuploads() {
		// http://billionuploads.com/1trrmuef9ln9
		$result = $this->post("http://billionuploads.com/checkfiles.html", "list=".$this->url."&op=checkfiles");
		if ($this->contains($result, "found</font>", "not found!</font>"))
			$this->print_status(true);
		else
			$this->print_status(false);
	}
	function folder_billionuploads() {
		$n = $this->element_number($this->get($this->url), "small");
		if ($n == 0)
			$this->print_number($this->count_elements($this->get($this->url), "div[class=link]"));
		else
			$this->print_number($n);
	}

}




$checker = new linkerit($_POST['url']);


?>
<?php

/* LinkerIT auto-update script */
// v1.02

// Replace 'password' below with the password you want to use to access this page
$yourpassword = "password";


// DO NOT EDIT ANYTHING ELSE UNDER THIS LINE
/* ---------------------------------------- */



if (isset($_GET['act']) && @$_SESSION['auth'] == true) {
	$act = $_GET['act'];
	switch ($act) {
		case 'check':
			$version = file_get_contents("VERSION");
			$new = file_get_contents("http://linkerit.youontech.net/script/update");
			$new = explode(";", $new);
			if ($version != $new[0]) {
				$page = <<<PAGE
	New version available. <a href="update.php?act=update">Update</a>. Notes:
	<pre>$new[1]</pre>
PAGE;
			}
			else {
				$page = <<<PAGE
	LinkerIT is updated!
PAGE;
			}
			show($page);
			break;
		case 'update':

			$version = file_get_contents("VERSION");
			$new = file_get_contents("http://linkerit.youontech.net/script/update");
			$new = explode(";", $new);
			if ($version != $new[0]) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://github.com/downloads/youontech/LinkerIT/pclzip.lib.php");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$result = curl_exec($ch);
				if ($result === false) {
					$page = "Fatal error #1, please contact support"; show($page);
				}
				file_put_contents("pclzip.lib.php", $result);

				curl_setopt($ch, CURLOPT_URL, "https://github.com/downloads/youontech/LinkerIT/update.zip");
				$result = curl_exec($ch);
				if ($result === false) {
					$page = "Fatal error #2, please contact support"; show($page);
				}
				file_put_contents("update.zip", $result);

				curl_close($ch);
				require_once("pclzip.lib.php");
				$archive = new PclZip("update.zip");
				if ($archive->extract(PCLZIP_OPT_REPLACE_NEWER) == 0) {
					$page = "Fatal error #3, please contact support"; show($page);
				}
				unlink("update.zip");
				unlink("pclzip.lib.php");
				$page = "Updated successful!<br />Notes:<pre>" . $new[1] . "</pre>";
			}
			else {
				$page = <<<PAGE
	LinkerIT is updated!
PAGE;
			}
			show($page);
			// end update
		default:
			# code...
			break;
	}
}
elseif (isset($_POST['password'])) {
	if  ($_POST['password'] == $yourpassword) {
		session_start();
		$_SESSION['auth'] = true;
		header("Location: update.php");
	}
	else
		show("The password is incorrect, <a href='update.php'>try again</a>");
}
else {
	session_start();
	if (@$_SESSION['auth'] == true) {
		$page = <<<PAGE
	<a href="update.php?act=check">Check if new version is available</a>
PAGE;
	}
	else {
		$page = <<<PAGE
	<form method="post" action="update.php">
		<input type="password" value="" name="password" />
		<input type="submit" value="Go!" />
	</form>
PAGE;
		show($page);
	}

}



function show($code) {
	echo <<<PAGE
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>LinkerIT Update Script</title>
<style type="text/css">
body {
	font-family: Verdana;
	font-size: 14px;
}
</style>
</head>

<body>
$code
</body>
</html>


PAGE;
}



?>
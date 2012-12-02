<html>
<head>
<title>Icon Images</title>
<link rel="stylesheet" type="text/css" href="<?=$path_config['doc_root'];?>used.css">
</head>
<body bgcolor="#C3C7D3" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">

<?php

include_once('includes/config.inc');

$image_dir = $sys_config['logos_path'];
$image_html_path = $path_config['doc_root'] . "../images/logos/";

$cols = 4;

print("<table height=\"100%\" width=\"100%\" cellspacing=\"2\" cellpadding=\"1\" border=\"1\" align=\"center\">\n");
print("<tr>\n");

$count = 0;
$files = array();
if ($dir = opendir($image_dir)) {
	while (($file = readdir($dir)) !== false) {
		if (preg_match('/^[\.]+$/', $file)) continue;
		if (preg_match('/^.*\.gd2$/', $file)) continue;
		$files[] = $file;
	}
	closedir($dir);

	sort($files);
	foreach($files as $file) {
		print("\t<td><center><img src=\"" . $image_html_path . $file . "\" width=\"32\" height=\"32\"><br><font size=\"2\">" . $file . "</font></center></td>\n");
		$count++;
		if($count == 4) {
			print("</tr>\n");
			print("<tr>\n");
			$count = 0;
		}
	}
	
	print("</tr>\n");
	print("</table>\n");

}else{

	print("<center><font size=\"3\" color=\"red\">Error reading the directory " . $image_dir . "</font></center>\n");

}

?>

</body>
</html>

<html>
<head>
<title>Help Command</title>
<link rel="stylesheet" type="text/css" href="<?=$path_config['doc_root'];?>style/used.css">
</head>

<body bgcolor="#C3C7D3" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">

<?php

include_once('includes/config.inc');

$fruity->get_resource_conf($resources);

$prefix_dir = $resources['user1'];
$command = $_GET['command'];
exec( $prefix_dir . "/" . $command . " --help", $output, $exit_code);
?>

<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">

<?php

foreach ($output as $line) {
	/*$line = preg_replace( '/Nagios/i', 'OpMon', $line);
	if (preg_match('/^Copyright/', $line))
		continue;
	if (preg_match('/^Last Modified/', $line))
		continue;
	if (preg_match('/^License/', $line))
		continue;
	if (preg_match('/^Send email/i', $line))
		continue;
	if (preg_match('/^regarding/i', $line))
		continue;*/
	print("<font size=\"2\">" . $line . "</font><br>\n");
}

?>

</div>

</body>
</html>

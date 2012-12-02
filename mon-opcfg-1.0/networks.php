<?php
/*
Fruity - A Nagios Configuration Tool
Copyright (C) 2005 Groundwork Open Source Solutions

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
	Filename: networks.php
	Description:
	Fruity starting point, lists networks to manage, and gateway to network 
	operations.
	
/*
Fruity Index Page, Displays Menu, and Statistics
*/
include_once('includes/config.inc');

// POST Action Handling
if(isset($_POST['request'])) {
	// Load Up The Session Data
	if(count($_POST['network_choose'])) {
		foreach($_POST['network_choose'] as $key=>$value) {
			$_SESSION['tempData']['network_choose'][$key] = $value;
		}
	}
	
	if($_POST['request'] == 'network_choose') {
		// Error check to see if network still exists
		if($fruity->networkExists($_SESSION['tempData']['network_choose']['network_id'])) {
			// Network exists, let's set the session and pass off to home.php
			session_register("network_id");
			$_SESSION['network_id'] = $_SESSION['tempData']['network_choose']['network_id'];
			$fruity->getNetworkInfo($_SESSION['tempData']['network_choose']['network_id'], $netInfo);
			session_register("network_name");
			$_SESSION['network_name'] = $netInfo['network_name'];
			header("Location: " . "home.php");
			die();
			// Relocated and script terminated
		}
		else {
			$status_msg = "That network no longer exists.";
		}
	}
}
			


$networkListing = null;
$fruity->getNetworks($networkListing);

print_header("Welcome To Fruity");
?>
<br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}

print_window_header("Choose A Network", "100%", "center");
?>
<br />
<blockquote>
Choose your network below:<br />
<br />
<form name="network_select" action="<?=$path_config['doc_root'];?>networks.php" method="post">
<input type="hidden" name="request" value="network_choose" />
<?php print_select("network_choose[network_id]", $networkListing, "network_id", "network_name"); ?>&nbsp;<input type="submit" value="Choose Network" />
</form>
<br />

</blockquote>
<?php
print_window_footer();
print_footer();
?>
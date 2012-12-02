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
Fruity Resource
*/
include_once('includes/config.inc');

if(isset($_POST['request'])) {
	if(count($_POST['resource_config'])) {
		foreach($_POST['resource_config'] as $key=>$value) {
			$_SESSION['tempData']['resource_config'][$key] = $value;
		}
	}
	
	if($_POST['request'] == 'update') {
		$fruity->update_resource_conf($_SESSION['tempData']['resource_config']);
		unset($_SESSION['tempData']);
		$status_msg = "Updated Resource Configuration.";
	}
}

// Get Existing Resource Configuration
$fruity->get_resource_conf($_SESSION['tempData']['resource_config']);

print_header("Environment Resources");
?>
<div class="navbar"><?print_navbar($networkHeaderLinks);?></div>
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}
	?>
	<br />
	<?php
	print_window_header("Resource Variables", "100%", "center");
		?>	
		<form name="resource_config[resource_config_form" method="post" action="<?=$path_config['doc_root'];?>resources.php">
		<input type="hidden" name="request" value="update" />
		<table width="100%" cellspacing="10" align="center" border="0">
		<tr>
			<td width="50%" valign="top">
			<b>$USER1$:</b> <input type="text" size="60" name="resource_config[user1]" value="<?=$_SESSION['tempData']['resource_config']['user1'];?>"><br />
			<br />
			
			<b>$USER2$:</b> <input type="text" size="60" name="resource_config[user2]" value="<?=$_SESSION['tempData']['resource_config']['user2'];?>"><br />
			<br />
			
			<b>$USER3$:</b> <input type="text" size="60" name="resource_config[user3]" value="<?=$_SESSION['tempData']['resource_config']['user3'];?>"><br />
			<br />
			
			<b>$USER4$:</b> <input type="text" size="60" name="resource_config[user4]" value="<?=$_SESSION['tempData']['resource_config']['user4'];?>"><br />
			<br />
			
			<b>$USER5$:</b> <input type="text" size="60" name="resource_config[user5]" value="<?=$_SESSION['tempData']['resource_config']['user5'];?>"><br />
			<br />
			
			<b>$USER6$:</b> <input type="text" size="60" name="resource_config[user6]" value="<?=$_SESSION['tempData']['resource_config']['user6'];?>"><br />
			<br />
			
			<b>$USER7$:</b> <input type="text" size="60" name="resource_config[user7]" value="<?=$_SESSION['tempData']['resource_config']['user7'];?>"><br />
			<br />
			
			<b>$USER8$:</b> <input type="text" size="60" name="resource_config[user8]" value="<?=$_SESSION['tempData']['resource_config']['user8'];?>"><br />
			<br />
			
			<b>$USER9$:</b> <input type="text" size="60" name="resource_config[user9]" value="<?=$_SESSION['tempData']['resource_config']['user9'];?>"><br />
			<br />
			
			<b>$USER10$:</b> <input type="text" size="60" name="resource_config[user10]" value="<?=$_SESSION['tempData']['resource_config']['user10'];?>"><br />
			<br />
			
			<b>$USER11$:</b> <input type="text" size="60" name="resource_config[user11]" value="<?=$_SESSION['tempData']['resource_config']['user11'];?>"><br />
			<br />
			
			<b>$USER12$:</b> <input type="text" size="60" name="resource_config[user12]" value="<?=$_SESSION['tempData']['resource_config']['user12'];?>"><br />
			<br />
			
			<b>$USER13$:</b> <input type="text" size="60" name="resource_config[user13]" value="<?=$_SESSION['tempData']['resource_config']['user13'];?>"><br />
			<br />
			
			<b>$USER14$:</b> <input type="text" size="60" name="resource_config[user14]" value="<?=$_SESSION['tempData']['resource_config']['user14'];?>"><br />
			<br />
			
			<b>$USER15$:</b> <input type="text" size="60" name="resource_config[user15]" value="<?=$_SESSION['tempData']['resource_config']['user15'];?>"><br />
			<br />
			
			<b>$USER16$:</b> <input type="text" size="60" name="resource_config[user16]" value="<?=$_SESSION['tempData']['resource_config']['user16'];?>"><br />
			</td>
			<td width="50%">
			
			<b>$USER17$:</b> <input type="text" size="60" name="resource_config[user17]" value="<?=$_SESSION['tempData']['resource_config']['user17'];?>"><br />
			<br />
			
			<b>$USER18$:</b> <input type="text" size="60" name="resource_config[user18]" value="<?=$_SESSION['tempData']['resource_config']['user18'];?>"><br />
			<br />
			
			<b>$USER19$:</b> <input type="text" size="60" name="resource_config[user19]" value="<?=$_SESSION['tempData']['resource_config']['user19'];?>"><br />
			<br />
			
			<b>$USER20$:</b> <input type="text" size="60" name="resource_config[user20]" value="<?=$_SESSION['tempData']['resource_config']['user20'];?>"><br />
			<br />
			
			<b>$USER21$:</b> <input type="text" size="60" name="resource_config[user21]" value="<?=$_SESSION['tempData']['resource_config']['user21'];?>"><br />
			<br />
			
			<b>$USER22$:</b> <input type="text" size="60" name="resource_config[user22]" value="<?=$_SESSION['tempData']['resource_config']['user22'];?>"><br />
			<br />
			
			<b>$USER23$:</b> <input type="text" size="60" name="resource_config[user23]" value="<?=$_SESSION['tempData']['resource_config']['user23'];?>"><br />
			<br />
			
			<b>$USER24$:</b> <input type="text" size="60" name="resource_config[user24]" value="<?=$_SESSION['tempData']['resource_config']['user24'];?>"><br />
			<br />
			
			<b>$USER25$:</b> <input type="text" size="60" name="resource_config[user25]" value="<?=$_SESSION['tempData']['resource_config']['user25'];?>"><br />
			<br />
			
			<b>$USER26$:</b> <input type="text" size="60" name="resource_config[user26]" value="<?=$_SESSION['tempData']['resource_config']['user26'];?>"><br />
			<br />
			
			<b>$USER27$:</b> <input type="text" size="60" name="resource_config[user27]" value="<?=$_SESSION['tempData']['resource_config']['user27'];?>"><br />
			<br />
	
			<b>$USER18$:</b> <input type="text" size="60" name="resource_config[user28]" value="<?=$_SESSION['tempData']['resource_config']['user28'];?>"><br />
			<br />
			
			<b>$USER29$:</b> <input type="text" size="60" name="resource_config[user29]" value="<?=$_SESSION['tempData']['resource_config']['user29'];?>"><br />
			<br />
			
			<b>$USER30$:</b> <input type="text" size="60" name="resource_config[user30]" value="<?=$_SESSION['tempData']['resource_config']['user30'];?>"><br />
			<br />
			
			<b>$USER31$:</b> <input type="text" size="60" name="resource_config[user31]" value="<?=$_SESSION['tempData']['resource_config']['user31'];?>"><br />
			<br />
			
			<b>$USER32$:</b> <input type="text" size="60" name="resource_config[user32]" value="<?=$_SESSION['tempData']['resource_config']['user32'];?>"><br />
			<br />
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="submit" value="Update Resource Configuration" />
			</td>
		</tr>
		</table>
		</form>
	<?php
	print_window_footer();
print_footer();
?>
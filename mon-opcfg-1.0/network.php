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

print_header($sys_config['network_desc']);
?>
<div class="navbar"><?print_navbar($networkHeaderLinks);?></div>
<br />
<br />
<?php
	if(isset($status_msg)) {
		?>
		<div align="center" class="statusmsg"><?=$status_msg;?></div><br />
		<?php
	}

print_window_header("Network Details", "100%", "center");
?>
<br />
<blockquote>
<?=$sys_config['network_desc'];?><br />
<br />
</blockquote>
<?php
print_window_footer();
?>
<br />
<br />
<?php
print_window_header("Top Level Hosts for " . $_SESSION['network_name'], "100%");
?>
&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&child_host_add=1">Add A New Child Host</a><br />
<br />
<?php
if($numOfChildren) {
	?>
	<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
	<tr class="altTop">
	<td>Host Name</td>
	<td>Description</td>
	</tr>
	<?php
	for($counter = 0; $counter < $numOfChildren; $counter++) {
		if($counter % 2) {
			?>
			<tr class="altRow1">
			<?php
		}
		else {
			?>
			<tr class="altRow2">
			<?php
		}
		?>
		<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$children_list[$counter]['host_id'];?>"><?=$children_list[$counter]['host_name'];?></a> <? $numOfSubChildren = return_num_of_children($children_list[$counter]['host_id']); if($numOfSubChildren) print("(".$numOfSubChildren.")");?></td>
		<td height="20" class="altRight"><?=$children_list[$counter]['alias'];?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}
else {
	?>
	<div class="statusmsg">No Children Hosts Exists</div>
	<?php
}
print_window_footer();

print_footer();
?>

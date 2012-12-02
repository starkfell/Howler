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
Fruity Index Page, Displays Menu, and Statistics
*/
include_once('includes/config.inc');

// Get list of host templates
$fruity->get_host_template_list($hostTemplateList);
$numOfHostTemplates = count($hostTemplateList);

// Get list of service templates
$fruity->get_service_template_list($serviceTemplateList);
$numOfServiceTemplates = count($serviceTemplateList);

print_header("Template Listings");
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
	print_window_header("Host Templates", "100%");
	?>
	&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>host_templates.php?host_template_add=1">Add A New Host Template</a>
	| <a class="sublink" href="<?=$path_config['doc_root'];?>templates_import.php">Import A New Host Template</a><br />
		<br />
		<?php
	if($numOfHostTemplates) {
		?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Host Template Name</td>
			<td>Description</td>
			</tr>
		<?php
		for($counter = 0; $counter < $numOfHostTemplates; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>host_templates.php?host_template_id=<?=$hostTemplateList[$counter]['host_template_id'];?>"><?=$hostTemplateList[$counter]['template_name'];?></a></td>
				<td height="20" class="altRight">&nbsp;<?=$hostTemplateList[$counter]['template_description'];?></td>
				</tr>
				<?php
		}
		?>
			</table>
			<?php
	}
	else {
	?>
		<div class="statusmsg">No Host Templates Exists</div>
			<?php
	}
	print_window_footer();
	print("<br />");
	print_window_header("Service Templates", "100%");
	?>
	&nbsp;<a class="sublink" href="<?=$path_config['doc_root'];?>service_templates.php?service_template_add=1">Add A New Service Template</a><br />
		<br />
		<?php
	if($numOfServiceTemplates) {
		?>
			<table width="100%" align="center" cellspacing="0" cellpadding="2" border="0">
			<tr class="altTop">
			<td>Service Template Name</td>
			<td>Description</td>
			</tr>
		<?php
		for($counter = 0; $counter < $numOfServiceTemplates; $counter++) {
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
				<td height="20" class="altLeft">&nbsp;<a href="<?=$path_config['doc_root'];?>service_templates.php?service_template_id=<?=$serviceTemplateList[$counter]['service_template_id'];?>"><?=$serviceTemplateList[$counter]['template_name'];?></a></td>
				<td height="20" class="altRight">&nbsp;<?=$serviceTemplateList[$counter]['template_description'];?></td>
				</tr>
				<?php
		}
		?>
			</table>
			<?php
	}
	else {
	?>
		<div class="statusmsg">No Service Templates Exists</div>
			<?php
	}
	print_window_footer();
print("<br /><br />");
print_footer();
?>

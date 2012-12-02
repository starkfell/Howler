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

// 2dcoordexport.php
// XML Export of hosts and their coordinates

include_once('config/config.php');
include_once('adodb/adodb.inc.php');
include_once('output/output.php');
include_once('sitedb/sitedb-general.php');

get_host_list($host_list);	// This is the entire host list
$numOfHosts = count($host_list);

header('Content-type: text/xml');

function exportParentTree($curhost) {
	global $icon, $expandedIcon, $startpath;
	$dir = dir($curpath);
	get_children_hosts_list($curhost, $childrenList);
	if(count($childrenList)) {
		foreach($childrenList as $child) {
			get_children_hosts_list($child['host_id'], $subChildrenList);
			if($subChildrenList) {	// This host has children hosts
				if($curhost != 0)
					print("<link fromhost=\"".$curhost."\" tohost=\"".$child['host_id']."\" />\r\n");
				exportParentTree($child['host_id']);
			}
			else {
				if($curhost != 0)
					print("<link fromhost=\"".$curhost."\" tohost=\"".$child['host_id']."\" />\r\n");
			}
		}
	}
}


print("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n");
print("<fruity version=\"".$sys_config['version']."\">\r\n");
print("<hostlist>\r\n");

for($counter = 0; $counter < $numOfHosts; $counter++) {
	print("<host name=\"".$host_list[$counter]['host_name']."\" host_id=\"".$host_list[$counter]['host_id']."\"");
	get_host_extended_info($host_list[$counter]['host_id'], $tempExtendedInfo);
	if($tempExtendedInfo['statusmap_image'] != '') {
		print(" image=\"http://".$_SERVER['HTTP_HOST'].$path_config['doc_root']."logos/".$tempExtendedInfo['statusmap_image']."\"");
	}
	print(">\r\n");
	$temp2dCoordinates = explode(",",$tempExtendedInfo['two_d_coords']);
	$temp3dCoordinates = explode(",",$tempExtendedInfo['three_d_coords']);
	print("<twodcoordinates x=\"".(int)$temp2dCoordinates[0]."\" y=\"".(int)$temp2dCoordinates[1]."\" />\r\n");
	print("<threedcoordinates x=\"".(int)$temp3dCoordinates[0]."\" y=\"".(int)$temp3dCoordinates[1]."\" z=\"".(int)$temp3dCoordinates[2]."\" />\r\n");
	
	print("</host>\r\n");
}
print("</hostlist>\r\n");

print("<linklist>\r\n");

exportParentTree(0);

print("</linklist>\r\n");
print("</fruity>");
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
Searches the host collection, this is called via a Dojo widget.
*/
include_once('includes/config.inc');

// $_GET['match'] should have our list

$matches = $fruity->search_hosts($_GET['match']);

$result = "[";

$count = 0;
foreach($matches as $id => $match) {
	if($count)
		$result .= ",";
	$result .= '["' . $match['host_name'].  '", "' . $id . '"]';
	$count++;
}

$result .= "]";

print($result);




?>
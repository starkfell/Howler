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

// Icinga Custom Commands

$icinga_restart['restart'] = 'sudo /etc/init.d/icinga';

// MySQL Database Connection Configuration
$sitedb_config['host']     = 'localhost';
$sitedb_config['username'] = '[mysql_username]';
$sitedb_config['password'] = '[mysql_password]';
$sitedb_config['database'] = 'opcfg';
$sitedb_config['dbserv']   = 'mysql';

// System Configuration
$sys_config['name'] = 'OpCfg - 1.2.4';
$sys_config['version'] = '- Configured For Icinga Core 1.8.1';
$sys_config['base_dir'] = '/usr/local/icinga';
$sys_config['web_dir'] = $sys_config['base_dir'] . '/share';
$sys_config['logos_path'] = $sys_config['web_dir'] . '/images/logos';
$sys_config['network_desc'] = "[Name of Environment Goes Here!]";

// Icinga Configuration
$sys_config['nagios_preflight'] = true;
$sys_config['nagios_path'] = $sys_config['base_dir'] . '/bin/icinga';
$sys_config['temp_dir'] = '/tmp';

/*

##### IMPORTANT! #####
	
To use sudo, you must add the following line to /etc/sudoers

{WEBSERVER USER}  ALL=(ALL)       NOPASSWD: /etc/rc.d/init.d/icinga

*/

//$sys_config['nagios_stop']   =  'sudo /etc/init.d/icinga stop';
//$sys_config['nagios_start']  = 'sudo /etc/init.d/icinga start';
//$sys_config['nagios_stop'] = 'sudo /etc/init.d/icinga restart';

$sys_config['nagios_stop']   =  'sudo /etc/init.d/icinga restart';



// Web Application Path Configuration
$path_config['doc_root'] = '/icinga/opcfg/';
$path_config['image_root'] = $path_config['doc_root'] . 'images/';

// Defines
define("AUTH_MODULES_URL_PATH", $path_config['doc_root'] . "modules/auth/");
define("SESSION_SECRET", $sys_config['name']."-".$sys_config['version'].":".$sys_config['network_desc']);			

if (DIRECTORY_SEPARATOR=='/')
	define("FRUITY_FS_ROOT", str_replace('includes', '', dirname(__FILE__).'/'));
else
	define("FRUITY_FS_ROOT", strreplace('includes', '', str_replace('\\\\', '/', dirname(__FILE__)).'/'));

// Do *not* modify any of the below
include_once(FRUITY_FS_ROOT . 'includes/adodb/adodb.inc.php');
include_once(FRUITY_FS_ROOT . 'output/output.php');
include_once(FRUITY_FS_ROOT . 'includes/output.inc');
include_once(FRUITY_FS_ROOT . 'modules/modules.inc');
include_once(FRUITY_FS_ROOT . 'includes/objects.inc');
include_once(FRUITY_FS_ROOT . 'includes/host.inc');
include_once(FRUITY_FS_ROOT . 'includes/fruity.inc');
include_once(FRUITY_FS_ROOT . 'includes/functions.php');
include_once(FRUITY_FS_ROOT . 'includes/sessions.inc');

?>

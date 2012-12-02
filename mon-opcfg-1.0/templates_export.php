<?php

//------------------------------------------------------
//  Global Configuration
//------------------------------------------------------

include_once('includes/config.inc');

// Globals
$tmp_path = "{$sys_config['web_dir']}/opcfg/tmp/";
$libexec_path = "{$sys_config['base_dir']}/libexec/templates/";
$host_template_id = $_GET['host_template_id'];

// Host Template Handles
$global_info = array();
$extended_info = array();

// Services Handles
$services_list = array();

// Commands and Plugins Handles
$commands = array();
$plugins = array();

//------------------------------------------------------
//  Host Template Export
//------------------------------------------------------

#print_header("Host Template - Export");
#print("<br>\n<br>\n");

#print('<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">' . "\n");

// Get global info
#print("Exporting host template info... ");
$fruity->get_host_template_info( $host_template_id, $global_info );

// Unset unused vars
unset($global_info['use_template_id']);
unset($global_info['host_template_id']);
unset($global_info['template_id']);
unset($global_info['check_period']);
unset($global_info['notification_period']);

if (isset($global_info['check_command'])) {

	$temp_command = array();
	
	// Get command
	$fruity->get_command($global_info['check_command'], $temp_command);
	
	// New Command Name
	$new_command_name = $global_info['template_name'] . "_" . $temp_command['command_name'];
	$temp_command['command_name'] = $new_command_name;
	unset($temp_command['command_id']);
	unset($temp_command['network_id']);
	
	// Add command to array
	$commands[$new_command_name] = $temp_command;
		
	$global_info['check_command'] = $new_command_name;
	
	unset($temp_command);
	unset($new_command_name);
	
}

$fruity->get_service_check_command_parameters($service_info['service_id'], $command_parameters);

#print("<b>done</b></br>\n");

// Get extended info
#print("Exporting host template extended info... ");
$fruity->get_host_template_extended_info( $host_template_id, $extended_info );
unset($extended_info['host_template_id']);
#print("<b>done</b></br>\n");

//------------------------------------------------------
//  Host Template's Services Export
//------------------------------------------------------

// Get services
#print("Exporting host templates services: <br>\n");
$t_services_list = array();
$fruity->get_host_template_services_list( $host_template_id, $t_services_list );

foreach($t_services_list as $index => $value) {

	$service_description = $fruity->return_service_description($value['service_id']);
	
	$service_info = array();
	$service_inherited_info = array();
	$service_inherited_sources = array();
	
	$command_info = array();
	$command_parameters = array();
	
	$service_extended_info = array();
	$service_inherited_extended_info = array();
	$service_inherited_extended_sources = array();
	
	#print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;>>> $service_description ... ");
	
	// Get service info
	$fruity->get_service_info( $value['service_id'], $service_info );

	// Get service inherited info
	if (isset($service_info['use_template_id'])) {
		$fruity->get_inherited_service_template_values( $service_info['use_template_id'], $service_inherited_info, $service_inherited_sources );
		foreach($service_inherited_info as $key => $val) {
			if(isset($service_inherited_info[$key]) && !isset($service_info[$key]))
				$service_info[$key] = $val;
		}
	}
	
	// Get check command details
	$fruity->get_command($service_info['check_command'], $command_info);
	$fruity->get_service_check_command_parameters($service_info['service_id'], $command_parameters);
	
	// Get service extended info
	$fruity->get_service_extended_info( $service_info['service_id'], $service_extended_info );
	
	// Get inherited service extended info
	if (isset($service_info['use_template_id'])) {
		$fruity->get_inherited_service_template_extended_values( $service_info['use_template_id'], $service_inherited_extended_info, $service_inherited_extended_sources );
		foreach($service_inherited_extended_info as $key => $val) {
			if(isset($service_inherited_extended_info[$key]) && !isset($service_extended_info[$key]))
				$service_extended_info[$key] = $val;
		}
	}
	
	// Unset unused vars
	unset($service_info['service_id']);
	unset($service_info['use_template_id']);
	unset($service_info['host_id']);
	unset($service_info['host_template_id']);
	unset($service_info['hostgroup_id']);
	unset($service_info['check_period']);
	unset($service_info['notification_period']);

	// Adjust commands information and put into command array
	$new_command_name = $global_info['template_name'] . "_" . $command_info['command_name'];
	$command_info['command_name'] = $new_command_name;
	
	unset($command_info['command_id']);
	unset($command_info['network_id']);
	
	for ($i = 0; $i < count($command_parameters); $i++) {
	
		unset($command_parameters[$i]['checkcommandparameter_id']);
		unset($command_parameters[$i]['service_id']);
		unset($command_parameters[$i]['service_template_id']);
		
		$command_parameters[$i]['parameter'] = htmlentities( $command_parameters[$i]['parameter'] );
		
	}
	
	$commands[$new_command_name] = $command_info;
		
	$service_info['check_command'] = $new_command_name;
	
	// Put it all together
	$services_list[$service_description]['info'] = $service_info;
	$services_list[$service_description]['extended'] = $service_extended_info;
	$services_list[$service_description]['parameters'] = $command_parameters;
	
	unset($new_command_name);

	#print("<b>done</b></br>\n");

}

//------------------------------------------------------
//  Plugins Export
//------------------------------------------------------

#print("Exporting Plugins ...");

// Get resources
$resources = array();
$fruity->get_resource_conf($resources);

// Extract plugins from commands
foreach($commands as $value => $param) {
	
		
	// Translate $USER1$
	$command_line = str_replace('$USER1$', $resources['user1'], $param['command_line']);

	$matches = array();
	$sub_dir = array();
	if (preg_match("/^(\S*) .*$/", $command_line, $matches)) {

		$temp_plugin = str_replace( $resources['user1'], "", $matches[1] );

		if (preg_match("/^\/(\S*)\/.*$/", $temp_plugin, $sub_dir)) 
			$plugins[] = "{$resources['user1']}/{$sub_dir[1]}";
		else 
			$plugins[] = $matches[1];

	}

	// Adjust the path
	$parts = array();
	preg_match("/.*\/([^\/]+)/", $matches[1], $parts);
	if (isset($sub_dir[1]))
		$parts[1] = "{$sub_dir[1]}/{$parts[1]}";

	$command_line = $libexec_path . $global_info['template_name'] . "/" . str_replace($matches[1], $parts[1], $command_line);
	$commands[$value]['command_line'] = $command_line;
	
}

$plugins = array_unique($plugins);

#print("<b>done</b></br>\n");

//------------------------------------------------------
//  File Export
//------------------------------------------------------

#print("Creating file to export ...\n");

// Generate random dir
$new_dir = time() + mt_rand(0,time());
$tmp_dir = $tmp_path . $new_dir;

// Create directory structure
mkdir($tmp_dir);
mkdir("$tmp_dir/plugins");

// Copy plugins
foreach($plugins as $plugin)
	system("/bin/cp -a $plugin $tmp_dir/plugins");

// Creating tpl file
if ($fh = fopen( "$tmp_dir/template.tpl", "w")) {
	
	// Writing Commands
	foreach($commands as $param => $aValue) {
		fputs($fh, "[command_$param]\n");
		foreach($aValue as $key => $value) {
			fputs($fh, "$key=$value\n");
		}
	}

	// Write host template
	fputs($fh, "[host_template]\n");
	foreach($global_info as $param => $value) {
		if ($value == '') continue;
		fputs($fh, "$param=$value\n");
	}
	
	// Write extended host template
	fputs($fh, "[extended_host_template]\n");
	foreach($extended_info as $param => $value) {
		if ($value == '') continue;
		fputs($fh, "$param=$value\n");
	}
	
	// Write services
	foreach($services_list as $service_name => $aValues) {
		foreach($aValues as $key => $param) {
			fputs($fh, "[service_{$key}_{$service_name}]\n");
			if (count($param)) {
				foreach($param as $key => $value) {
					if ($value == '') continue;
				
					if (is_array($value)) {
						foreach($value as $val)
							fputs($fh, "$key=$val\n");
					}else{
						fputs($fh, "$key=$value\n");
					}
				}
			}
		}
	}

	fclose($fh);
	
} else {
	
	#print(" <b>Unable to create tpl file</b></br>\n");
	die;

}

// Create the tar and remove old files
chdir($tmp_dir);
system("/bin/tar -czf  ../{$global_info['template_name']}.tar.gz *");
chdir($tmp_path);
system("/bin/rm -rf $tmp_dir");

#print("<b>done</b></br>\n");

// Print Footer
#print_footer();

// Redirect to download the file created
header("Location: {$path_config['doc_root']}tmp/{$global_info['template_name']}.tar.gz");

?>

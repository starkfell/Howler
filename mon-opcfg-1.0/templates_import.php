<?php

//------------------------------------------------------
//  Global Configuration
//------------------------------------------------------

include_once('includes/config.inc');

// Globals
$tmp_path = "{$sys_config['web_dir']}/opcfg/tmp/";
$libexec_path = "{$sys_config['base_dir']}/libexec/templates/";
$tpl_conf = array();

global $fruity;

//------------------------------------------------------
// If it's running from command-line, set variables
// manually.
//------------------------------------------------------

if ( isset( $_SERVER['SHELL']) ) {
	
	if ($argc == 1) {
		print("Invalid parameters:\n\n\t{$argv[0]} [template_file]\n");
		exit(0);
	} else {
	
		$fruity = new Fruity();
		$tmp_path = "";
		$_SERVER["PHP_AUTH_USER"] = "nagiosadmin";
		$_FILES['template_file']['error'] = 0;
		$_FILES['template_file']['name'] = $argv[1];
		
	}
	
}

//------------------------------------------------------
//  Import Host Template
//------------------------------------------------------

print_header("Host Template - Import");

print("<br>\n<br>\n");

if (!isset($_FILES['template_file']['name']) && !isset($_SERVER['SHELL']) ) {

	print_window_header("Select the template package", "100%");

	?>

	<br>
	<form name='mainForm' enctype='multipart/form-data' action='<?=$_SERVER['REQUEST_URI'];?>' method='post'>
		Template File: <input name='template_file' type='file' /><br><br>
		<input type='submit' value='Import' />
	</form>

	<?php
	
} else {
	
	print_window_header("Importing Template", "100%");
	
	print("<br>\n");
	
	if ($_FILES['template_file']['error']) {
		
		print("<b> One or more errors occurred during the upload process.</b>\n");
		
	} else if (!preg_match("/.*\.tar\.gz/", $_FILES['template_file']['name'])) {
		
		print("<b> Incorrect file format. Upload the correct file.</b>\n");

	} else {
	
	
		// Move file to temporary directory
		move_uploaded_file( $_FILES['template_file']['tmp_name'], "{$tmp_path}{$_FILES['template_file']['name']}");
		
		// Create tmp directory
		$tmp_dir = $tmp_path;
		$tmp_dir .= time() + mt_rand(0, time());
		mkdir($tmp_dir);
		
		// Untar template package
		system("/bin/tar -xzf {$tmp_path}{$_FILES['template_file']['name']} -C $tmp_dir");
		
		// Remove tar file
		unlink("{$tmp_path}{$_FILES['template_file']['name']}");
		
		// Open tpl file and parse informations
		$fh = fopen("$tmp_dir/template.tpl", "r");

		$key = "";
		while( ($string = fgets( $fh )) !== false ) {
			
			if (preg_match("/\[(\S*)\]/", $string, $matches))
				$key = $matches[1];
				
			if (preg_match("/^(\S*)=(.*)$/", $string, $matches))
				$tpl_conf[$key][$matches[1]] = $matches[2];
			
		}
		
		fclose($fh);

		// Verify if the host template already exist
		if ($fruity->host_template_exists($tpl_conf['host_template']['template_name'])) {
			
			print("&nbsp;&nbsp;Host Template <b>{$tpl_conf['host_template']['template_name']}</b> already exists! ");
			
		} else {
			
			print('<div style="margin: 5px; padding: 5px; background: #cccccc; border: 1px solid grey;">' . "\n");
			
			print("Creating template directory structure... ");
			@mkdir($libexec_path);
			@mkdir("{$libexec_path}{$tpl_conf['host_template']['template_name']}");
			print("<b>done</b></br>\n");
			
			print("Inserting Commands... ");
			foreach($tpl_conf as $key => $aValues) {
				
				// Skip non commands
				if (!preg_match("/^command_.*$/", $key)) continue;
				
				// Add command
				if (!$fruity->command_exists( $aValues['command_name'] ))
					$fruity->add_command( $aValues );
					
							
			}
			print("<b>done</b></br>\n");
			
			print("Inserting Host Template Information... ");
			unset($tpl_conf['host_template']['check_command']);
			$tpl_conf['host_template']['check_period'] = $fruity->return_period_id_by_name( "24x7" );
			$tpl_conf['host_template']['notification_period'] = $fruity->return_period_id_by_name( "24x7" );
			$fruity->add_host_template( $tpl_conf['host_template'] );
			$template_id = $fruity->return_host_template_id_by_name( $tpl_conf['host_template']['template_name'] );
			if (count($tpl_conf['extended_host_template']))
				$fruity->modify_host_template_extended( $template_id, $tpl_conf['extended_host_template'] );
			print("<b>done</b></br>\n");
			
			print("Inserting Services Information: <br>\n");
			foreach($tpl_conf as $key => $aValues) {
				
				// Skip non services
				if (preg_match("/^service_([a-zA-Z]+)_(.*)$/", $key, $matches)) {
					
					$type = $matches[1];
					$service_name = $matches[2];
					
					if ($type == "info") {
						
						print("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;>>> $service_name ... ");
						
						$aValues['host_template_id'] = $template_id;
						$tCommand = $fruity->get_command_by_name($aValues['check_command']);
						$aValues['check_command'] = $tCommand['command_id'];
						
						$aValues['check_period'] = $fruity->return_period_id_by_name( "24x7" );
						$aValues['notification_period'] = $fruity->return_period_id_by_name( "24x7" );						

						$fruity->add_service( $aValues );
						
						print("<b>done</b></br>\n");
					} else if ($type == "extended") {
						
						$service_id = $fruity->return_service_id_by_host_template_and_description($template_id, $service_name);
						$fruity->modify_service_extended( $service_id, $aValues );
					} else if ($type == "parameters") {
						
						$service_id = $fruity->return_service_id_by_host_template_and_description($template_id, $service_name);
						
						
						foreach( $aValues as $param => $value ) {
							
							$t_array = array();
							$t_array['parameter'] = html_entity_decode( $value );
							$fruity->add_service_command_parameter( $service_id, $t_array );
							
						}
						
					}
					
					
				} else {
					continue;
				}
			}
			
			print("Copying plugins... \n");
			system("/bin/cp -a $tmp_dir/plugins/* {$libexec_path}{$tpl_conf['host_template']['template_name']}");
			print("<b>done</b></br>\n");
			
			print("<br>\n");
			print("<center><b>Template Imported!</b></center>\n");
			print("<br>\n");
			
			
			print('</div>');
			
		}
		
		// Remove tmp directory
		system("/bin/rm -rf $tmp_dir");
		
	}

}

print("<br>\n");

print_window_footer();

print("<br>\n");

print("<a href='{$path_config['doc_root']}templates.php'>Back to Templates</a>");

print("<br>\n");

print_footer();

?>

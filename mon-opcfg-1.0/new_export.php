<?php

//----------------------------------
// New Export Engine
//----------------------------------

include_once('includes/config.inc');

$mainConfig = array();
$fruity->get_main_conf($mainConfig);

//---------------------------------
// Start Output
//---------------------------------

/* Header */
print_header("Configuration Export");
print("<br>\n");
print("<br>\n");

/* Body */
print_window_header("Export Options", "100%");

print("<b>{$sys_config['name']}</b> is currently set to write it's configuration files to <b>{$mainConfig['config_dir']}</b>, as defined ");
print("in <b>Main Config</b>, under <u>Paths</u>.<b>  {$sys_config['name']}</b> must have write access to this 
directory. <b> {$sys_config['name']}</b> will also ");
print("attempt to create backups of existing files (if they exist) with an extension of <b>.fruity.backup</b>.<br />");

print("<br>\n");
print("<table border='0' width='100%'><tr>");
print("<td><div><b><font size='2'>Export Information</font></b></div></td>");
print("<td align='right' valign='center'><div id='loader' style='display: none;'><img src='{$path_config['doc_root']}ajax_loader.gif'>Export running...</div><td>");
print("</tr></table>");
print("<div id='run_output' style='padding: 5px; background: #cccccc; border: 1px solid grey;'>");
print("</div>\n");
print("<br>\n");

print("<center>\n");
print("Start a New Export Process?\n<br><br>");
print("<input type='button' value='YES' onclick='javascript:startExport();'>\n");
print("</center>\n");
print("<br>\n");

print("<center>\n");
print("<div><b><font color='red' size='2'>Last Export Log</font></b></div>\n");
print("</center>\n");
print("<div id='log_output' style='padding: 5px; background: #cccccc; border: 1px solid grey;'>");
print("</div>\n");

print("<script>\n");
print("setInterval( 'updateRun()', 2000 );\n");
print("setInterval( 'updateLog()', 2000 );\n");
print("setInterval( 'showLoader()', 500 );\n");
print("</script>\n");

print_window_footer();

/* Footer */
print_footer();

?>

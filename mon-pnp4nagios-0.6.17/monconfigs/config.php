<?php
#########################################################################################
#
# [config.php]   customized for [mon-pnp4nagios] RPM Build.
#
# Inception:     05.03.2012
# Last Modified: 05.03.2012
#
# Changes:       [R. Irujo] - 05.03.2012
#                - ['nagios_base'] variable entry changed to '/icinga/cgi-bin'.
#                - The following variables have been commented out:
#                  $conf['multisite_base_url']
#                  $conf['multisite_site']
#                  $conf['livestatus_socket']                
#
#########################################################################################

$conf['use_url_rewriting'] = 1;
#
# Location of rrdtool binary
#
$conf['rrdtool'] = "/usr/local/rrdtool/bin/rrdtool";
#
# RRDTool image size of graphs
#
$conf['graph_width'] = "500";
$conf['graph_height'] = "100";
#
# RRDTool image size of graphs in zoom window
#
$conf['zgraph_width'] = "500";
$conf['zgraph_height'] = "100";
#
# Right zoom box offset.
# rrdtool 1.3.x = 30px 
# rrdtool 1.4.x = 22px
#
$conf['right_zoom_offset'] = 30;

#
# RRDTool image size of PDFs
#
$conf['pdf_width']        = "675";
$conf['pdf_height']       = "100";
$conf['pdf_page_size']    = "A4";   # A4 or Letter
$conf['pdf_margin_top']   = "30";
$conf['pdf_margin_left']  = "17.5";
$conf['pdf_margin_right'] = "10";
#
# Additional options for RRDTool
#
# Example: White background and no border
# "--watermark 'Copyright by example.com' --slope-mode --color BACK#FFF --color SHADEA#FFF --color SHADEB#FFF"
#
$conf['graph_opt'] = ""; 
#
# Additional options for RRDTool used while creating PDFs
#
$conf['pdf_graph_opt'] = ""; 
#
# Directory where the RRD Files will be stored
#
$conf['rrdbase'] = "/usr/local/pnp4nagios/var/perfdata/";
#
# Location of "page" configs
#
$conf['page_dir'] = "/usr/local/pnp4nagios/etc/pages/";
#
# Site refresh time in seconds
#
$conf['refresh'] = "90";
#
# Max age for RRD files in seconds
# 
$conf['max_age'] = 60*60*6;   
#
# Directory for temporary files used for PDF creation 
#
$conf['temp'] = "/var/tmp";
#
# Link back to Nagios or Thruk ( www.thruk.org ) 
#
$conf['nagios_base'] = "/icinga/cgi-bin";

#
# Link back to check_mk´s multisite ( http://mathias-kettner.de/checkmk_multisite.html )
#
#$conf['multisite_base_url'] = "/check_mk";
#
# Multisite Site ID this PNP installation is linked to
# This is the same value as defined in etc/multisite.mk
#
#$conf['multisite_site'] = "";

#
# check authorization against mk_livestatus API 
# Available since 0.6.10
#
$conf['auth_enabled'] = FALSE;

#
# Livestatus socket path
# 
#$conf['livestatus_socket'] = "tcp:localhost:6557";
#$conf['livestatus_socket'] = "unix:/usr/local/nagios/var/rw/live";

#
# Which user is allowed to see all services or all hosts?
# Keywords: <USERNAME>
# Example: conf['allowed_for_all_services'] = "nagiosadmin,operator";
# This option is used while $conf['auth_enabled'] = TRUE
$conf['allowed_for_all_services'] = "";
$conf['allowed_for_all_hosts'] = "";

# Which user is allowed to see additional service links ?
# Keywords: EVERYONE NONE <USERNAME>
# Example: conf['allowed_for_service_links'] = "nagiosadmin,operator";
# 
$conf['allowed_for_service_links'] = "EVERYONE";

#
# Who can use the host search function ?
# Keywords: EVERYONE NONE <USERNAME>
#
$conf['allowed_for_host_search'] = "EVERYONE";

#
# Who can use the host overview ?
# This function is called if no Service Description is given.  
#
$conf['allowed_for_host_overview'] = "EVERYONE";

#
# Who can use the Pages function?
# Keywords: EVERYONE NONE <USERNAME>
# Example: conf['allowed_for_pages'] = "nagiosadmin,operator";
#
$conf['allowed_for_pages'] = "EVERYONE";

#
# Which timerange should be used for the host overview site ? 
# use a key from array $views[]
#
$conf['overview-range'] = 1 ;

#
# Scale the preview images used in /popup 
#
$conf['popup-width'] = "300px";

#
# jQuery UI Theme
# http://jqueryui.com/themeroller/
# Possible values are: lightness, smoothness, redmond, multisite
$conf['ui-theme'] = 'smoothness';

# Language definitions to use.
# valid options are en_US, de_DE, es_ES, ru_RU, fr_FR 
#
$conf['lang'] = "en_US";

#
# Date format
#
$conf['date_fmt'] = "d.m.y G:i";

#
# This option breaks down the template name based on _ and then starts to 
# build it up and check the different template directories for a suitable template.
#
# Example:
#
# Template to be used: check_esx3_host_net_usage you create a check_esx3.php
#
# It will find and match on check_esx3 first in templates dir then in templates.dist
#
$conf['enable_recursive_template_search'] = 1;

#
# Direct link to the raw XML file.
#
$conf['show_xml_icon'] = 1;

#
# Use FPDF Lib for PDF creation ?
#
$conf['use_fpdf'] = 1;	

#
# Use this file as PDF background.
#
$conf['background_pdf'] = '/usr/local/pnp4nagios/etc/background.pdf' ;

#
# Enable Calendar
#
$conf['use_calendar'] = 1;

#
# Define default views with title and start timerange in seconds 
#
# remarks: required escape on " with backslash
#
#$views[] = array('title' => 'One Hour',  'start' => (60*60) );
$views[] = array('title' => '4 Hours',   'start' => (60*60*4) );
$views[] = array('title' => '25 Hours',  'start' => (60*60*25) );
$views[] = array('title' => 'One Week',  'start' => (60*60*25*7) );
$views[] = array('title' => 'One Month', 'start' => (60*60*24*32) );
$views[] = array('title' => 'One Year',  'start' => (60*60*24*380) );

#
# rrdcached support
# Use only with rrdtool svn revision 1511+
#
# $conf['RRD_DAEMON_OPTS'] = 'unix:/tmp/rrdcached.sock';
$conf['RRD_DAEMON_OPTS'] = '';

# A list of directories to search for templates
# /usr/local/pnp4nagios/share/templates.dist is always the last directory to be searched for templates
#
# Add your own template directories here
# First match wins!
#$conf['template_dirs'][] = '/usr/local/check_mk/pnp-templates';
$conf['template_dirs'][] = '/usr/local/pnp4nagios/share/templates';
$conf['template_dirs'][] = '/usr/local/pnp4nagios/share/templates.dist';

#
# Directory to search for special templates
#
$conf['special_template_dir'] = '/usr/local/pnp4nagios/share/templates.special';

#
# Regex to detect mobile devices
# This regex is evaluated against the USER_AGENT String
#
$conf['mobile_devices'] = 'iPhone|iPod|iPad|android';
?>

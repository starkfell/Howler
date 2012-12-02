<?php
/* <--- Host Documentation Generator - [upate_host_doc_post.php] -- [p. 2]--->

  Author:       R. Irujo
  Inception:    06.07.2012

  Description:  Generates Host and Service Documentation in Wiki Format for a specific named Host.
                The 'update_host_doc_search.php' page passes the Hostname value to the 'update_host_doc_post.php'
                page which then returns back the values to an embedded iframe in the 'update_host_doc_search.php'
                page. Three new functions were added to the 'fruity.inc' file located in the 'includes/' folder
                to gather the required results: 'documentation_search_services()', 'documentation_search_paramters()',
                and 'documentation_search_notifications()'.

  Last Updated: 06.07.2012 - [R. Irujo]
                v1.0
                Inception.

*/

include_once('includes/config.inc');
global $sys_config;


if ($_POST["Host_Name"]) {
        $HostName = $_POST["Host_Name"];

        $HostName_Check = $fruity->documentation_search_services("$HostName");
        if (!$HostName_Check || empty($HostName_Check)) {
                echo " <font face='Courier New' color='red' size='2'>
                       Documentation could not be generated for [$HostName]! <br><br>
                       If you know that [$HostName] Exists. Please do the following and try again: <br><br>
                       Make sure at least ONE [Contact Group] is defined for [$HostName]. <br>
                       Make sure at least ONE [Service] is defined for [$HostName]. <br></font></strong><br>";                
		}
        else {

// <--- Start Wiki Table Formatted Output --->
echo    "<font face='Courier New' size='2'>"                         .
        "=== Current Monitoring Configuration - [$HostName] ==="     . " <br> " .
        "{| cellspacing=\"1\" cellpadding=\"1\" border=\"1\" style=\"width: 1051px; height: 240px;\" class=\"wikitable\"" . " <br> " .
        "|-"                                                         . " <br> " .
        "| align=\"center\" | '''Monitor Name'''"                    . " <br> " .
        "| align=\"center\" | '''Check Command'''"                   . " <br> " .
        "| align=\"center\" | '''Parameters'''"                      . " <br> " .
        "| align=\"center\" | '''Max Check Attempts'''"              . " <br> " .
        "| align=\"center\" | '''Check Interval (Min)'''"            . " <br> " .
        "| align=\"center\" | '''Retry Interval (Min)'''"            . " <br> " .
        "| align=\"center\" | '''Notification Interval (Min)'''"     . " <br> " .
        "| align=\"center\" | '''Notifications'''"                   . " <br> " .
        "</font>";

// $HostName parameter is passed to return back all services attached to the Host.
$Services = $fruity->documentation_search_services("$HostName");

        // SQL Query is called and returns back all matching Services.
        foreach ($Services as $Section => $Rows) {

                 // SQL Query is called and returns back all matching Parameters.
                 $Service_Parameters = $fruity->documentation_search_parameters($Rows['service_description']);

                 // Service ID's Values are compared between the nagios_services table and the nagios_services_check_command_parameters table.
                 // The Parameter Values that match are returned back and added to the $Parameters Array.
                        foreach($Service_Parameters as $Section => $Col_Vals) {
                                if ($Col_Vals['service_id'] == $Rows['service_id']) {
                                $Parameters[] = ($Col_Vals['parameter']);
                                        }
                                }
                        // Parameter Values in the $Parameters Array are separated by an '!' to match Icinga/Nagios formatting.
                        $Parameters = implode("!",$Parameters);


                // SQL Query is called and returns back all matching Service Notifications.
                 $Service_Notifications = $fruity->documentation_search_notifications($HostName,$Rows['service_description']);

                 // Service ID's Values are compared between the nagios_services table and the nagios_service_contactgroups table.
                 // The Notifications Entries that match are returned back and added to the $Notifications Array.
                        foreach($Service_Notifications as $Section => $Col_Vals) {
                                if ($Col_Vals['service_id'] == $Rows['service_id']) {
                                $Notifications[] = ($Col_Vals['contactgroup_name']);
                                        }
                                }
                        // Notification Entries in the $Notifications Array are separated by a colon.
                        $Notifications = implode(":",$Notifications);

        // Processed Service and Parameter Values are returned back in Wiki Format for documentation.
        echo "|- <font face='Courier New' size='2'>    <br> " .
             "| " . $Rows['service_description']   . " <br> " .
             "| " . $Rows['command_name']          . " <br> " .
             "| " . $Parameters                    . " <br> " .
             "| " . $Rows['max_check_attempts']    . " <br> " .
             "| " . $Rows['normal_check_interval'] . " <br> " .
             "| " . $Rows['retry_check_interval']  . " <br> " .
             "| " . $Rows['notification_interval'] . " <br> " .
             "| " . $Notifications                 . " <br></font>" ;

        // The $Parameters Array and the $Notifications Array aredestroyed before being reused again in the foreach loop.
        unset($Parameters);
        unset($Notifications);
        }

        // <--- End Wiki Table Formatted Output --->
        echo "|}";

        // Closing $HostName Variable Check If/Else Statement.
        }

// Closing $POST Variable Check at the beginning of the page.
}

?>




<?php
/* <--- Update Multiple Service Contact Groups - [update_multiple_service_contactgroups.php] --->

  Author:       R. Irujo
  Inception:    06.17.2012

  Description:  Allows a user to Add or Remove a Single Contact Group to multiple Services belonging to a Single Host.
                Two new functions, 'get_host_services' and 'get_host_service_contactgroups' were added to the 'fruity.inc'
                file located in the '/includes' directory. These two functions provide the information displayed in the HTML
                Table on the page as well as providing the 'service_id' values in the '$_POST' variable. The 'host.inc' file
                was also modified to include a link to the page under the title, 'All Host Services Contact Groups'.

  Last Updated: 06.17.2012 - [R. Irujo]
                v1.0
                Inception.

                09.20.2012 - [R. Irujo]
                v1.1
                Added a query to retrieve the Host Name of the Server and include it in the 'print_window_header' section.
                The 'get_host_name' query was added to the 'fruity.inc' file which is located in the 'includes' folder.

*/

// Retrieving standard includes and global variables.
include_once('includes/config.inc');
global $sys_config;

// Retrieving the Host Name of the Server being modified.
$HostName = $fruity->get_host_name($_GET['host_id']);


print_header("Update Multiple Service Contact Groups");
print("<br /><br />");
print_window_header("Update a Single Contact Group for Multiple Services on {$HostName['0']['host_name']}", "100%");
?>


<!-- Select all Checkboxes Toggle Function (IE/Firefox/Chrome Compatible) -->
<script language="JavaScript">
function SelectAll(source) {
    var checkboxes = document.getElementsByTagName("input");
    for (var i = 0; i < checkboxes.length; i++)
        checkboxes[i].checked = source.checked;
}
</script>


<!-- Creating Navigation Bar manually, for now. Will look into creating Navigation Bar automatically from the 'host.inc' file at a later date. -->
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=general">General</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checks">Checks</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=flapping">Flapping</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=logging">Logging</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=notifications">Notifications</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=services">Services</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=groups">Group Membership</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=contactgroups">Host Contact Groups</a> |
<a href="<?=$path_config['doc_root'];?>update_multiple_service_contactgroups.php?host_id=<?=$_GET['host_id'];?>">All Host Services Contact Groups</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=extended">Extended Information</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=dependencies">Dependencies</a> |
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=escalations">Escalations</a>
<a class="sublink" href="<?=$path_config['doc_root'];?>hosts.php?host_id=<?=$_GET['host_id'];?>&section=checkcommand">Check Command Parameters</a>
<br>
<br>


<!-- [START!] ----  Table created to list existing Host Services and the Contact Groups associated with those Services. -->
<table>
<form action="" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" >
<b>Add New Contact Group:</b>

<?php
// Retrieving List of All Contact Groups.
$fruity->get_contactgroup_list( $contactgroups_list);
print_select("contact_group[contactgroup_add][contactgroup_id]", $contactgroups_list, "contactgroup_id", "contactgroup_name", "0");
?>

<input type="submit" name="Add"     value="Add Contact Group">
<input type="submit" name="Delete"  value="Delete Contact Group">
<input type="submit" name="Refresh" value="Refresh Results">
<br>
<br>
<tr>
<th style='background-color:#cccccc; width: 110px'><font size='1px'>Select All Services<br></font><input type='checkbox' onClick='SelectAll(this)' /></th>
<th style='background-color:#cccccc;'><font size='2px'>Service Name</font></th>
<th style='background-color:#cccccc;'><font size='2px'>Contact Groups</font></th>
<th style='background-color:#cccccc;'><font size='1px'>Service ID</font></th>
</tr>

<?php
// Retrieving all Services belonging to the Host we are modifying Contact Groups for.
$Host_Services = $fruity->get_host_services($_GET['host_id']);

foreach($Host_Services as $Entry => $Rows) {

        // Retrieving all Contact Groups that are currently assigned to the Hosts' Services.
        $Service_Notifications = $fruity->get_host_service_contactgroups($Rows['host_id'],$Rows['service_description']);

                // Service ID's Values are compared between the nagios_services table and the nagios_service_contactgroups table.
                // The Notifications Entries that match are returned back and added to the $Notifications Array.
                foreach($Service_Notifications as $Section => $Col_Vals) {
                        if ($Col_Vals['service_id'] == $Rows['service_id']) {
                                $Notifications[] = ($Col_Vals['contactgroup_name']);
                                }
                        }

                // Notification Entries in the $Notifications Array are separated by a colon.
                $Notifications = implode(":",$Notifications);

        // Existing Service IDs, Service Descriptions, and currently Assigned Contact Groups are returned.
        // The hidden input section for 'service_description' is passed in the '$_POST' variable but is currently not in use.
        echo "<tr>" .
             "<td style='background-color:#D3D3D3;text-align: center;'>" .
             "<input type='checkbox' name='service_id[]'                       value='"    . $Rows['service_id']           . "' />" .
             "<input type='hidden'   name='service_description[]'              value='"    . $Rows['service_description']  . "' />" .
             "<td style='background-color:#A9A9A9; text-align: center; width: 500px'><b>"  . $Rows['service_description']  . "</b></td>" .
             "<td style='background-color:#D3D3D3; text-align: center; width: 500px;'>"    . $Notifications                . "</td>" .
             "<td style='background-color:#A9A9A9; text-align: center; width: 50px'>"      . $Rows['service_id']           . "</td>" .
             "</tr>";

        // The $Notifications Array is destroyed before being reused again in the foreach loop.
        unset($Notifications);
        }
?>

<!-- [END!] ----  Table created to list existing Host Services and the Contact Groups associated with those Services. -->
</table>
<br>
<br>


<?php

// Initializing Variables passed from the $_POST variable.
#$Service_Name       = $_POST['service_description'];
$Service_ID         = $_POST['service_id'];
$Contact_Group      = $_POST['contact_group'];
$Add_Group          = $_POST['Add'];
$Delete_Group       = $_POST['Delete'];
$Refresh_Results    = $_POST['Refresh'];

// Extracting the Contactgroup ID that is to be added to the selected of the Services.
foreach($Contact_Group as $Entry) {
        $Contact_Group_ID = $Entry['contactgroup_id'];
        }


// Adding a Contact Group to the selected Services.
if($Add_Group){

        // Verifying that at least one Service was selected to Add a Contact Group to.
        if (is_null($Service_ID)){
        echo "<b><font color='red'>No Services were selected to Add a Contact Group to! </font></b>";
        }

        // Each Selected Service is processed to determine if the Contactgroup ID is assigned to that particular
        // Service ID. Results are returned back and are shown below the HTML Table.
        foreach($Service_ID as $Service) {
              if($fruity->service_has_contactgroup($Service, $Contact_Group_ID)) {
                      echo "<b><font color='red'>Service ID [$Service] already has any entry for Contactgroup ID [$Contact_Group_ID]. </font></b><br>";
                      }
              else {
                      $fruity->add_service_contactgroup($Service, $Contact_Group_ID);
                      echo "<b><font color='green'>Contactgroup ID [$Contact_Group_ID] has been added to Service ID [$Service]. </font></b><br>";
                      }
              }
      }

// Removing a Contact Group fom the selected Services.
elseif($Delete_Group){

        // Verifying that at least one Service was selected to Remove a Contact Group from.
        if (is_null($Service_ID)){
        echo "<b><font color='red'>No Services were selected to Remove a Contact Group from! </font></b>";
        }

        // Each Selected Service is processed to determine if the Contactgroup ID is assigned to that particular
        // Service ID. Results are returned back and are shown below the HTML Table.
        foreach($Service_ID as $Service) {
                if(!$fruity->service_has_contactgroup($Service, $Contact_Group_ID)) {
                        echo "<b><font color='red'>Service ID [$Service] does not have an entry for Contactgroup ID [$Contact_Group_ID]! </font></b><br>";
                        }
                else {
                        $fruity->delete_service_contactgroup($Service, $Contact_Group_ID);
                        echo "<b><font color='green'>Contactgroup ID [$Contact_Group_ID] has been removed from Service ID [$Service]. </font></b><br>";
                        }
                }
        }

// Refresh Page Results.
elseif($Refresh_Results){
        echo "<b>Page has been refreshed. </b>";
        }

print_window_footer();
print_footer();

?>




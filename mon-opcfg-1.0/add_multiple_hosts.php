<?php

/* [ Add Multiple Hosts Page ]

This page gives the user the ability to add multiple hosts into OpCfg by copying and pasting the
Hostname and IP Address of the Servers they want to add into monitoring via CSV format.

*/

include_once('includes/config.inc');

global $sys_config;

print_header("Add Multiple Hosts");
print("<br /><br />");
print_window_header("Add Multiple Hosts Below", "100%");
?>


<!-- Confirmation Script which is utilized by the Continue button. -->
<script>
        function confirmAddServers(msg) {
                msg = msg || 0;
                if (msg == 0) {
                        return confirm("Are Both [Server] and [IP Address] fields filled out correctly?");
                }
        }
</script>


<!-- The list of Servers and IP Addresses are processed from these two textarea fields. -->
<div style="width 50%; float:left">
<form type="hidden" action="add_multiple_hosts.php" method="post">

Type in the <strong>Server Names and IP Addresses</strong> of the <strong>Hosts</strong> you want to add to <strong>OpCfg</strong> and then <strong>click</strong> on</strong>
<input type="submit" value="Continue"  onClick="javascript:return confirmAddServers();" />
<br>
<br>
<table style="font-size:8pt" border="0">
  <tr>
    <th align="left" style="width:auto">New Servers</th>
    <th align="left" style="width:auto">New IP Addresses</th>
  </tr>
  <tr>
    <td><textarea name='ServerList' rows="40" cols="35" class='html_text-box'></textarea></td>
    <td><textarea name='IPList' rows="40" cols="35" class='html_text-box'></textarea></td>
  </tr>
</table>
</form>
<br>
<br>
</div>


<?php

// The Servers and IP Addresses submitted from above are processed here.
if(isset($_POST["ServerList"]) && ($_POST["IPList"])){

$NewHosts       = $_POST["ServerList"];
$NewIPs         = $_POST["IPList"];
$ExistingHosts  = $fruity->search_hostnames();
$ExistingIPs    = $fruity->search_ipaddress();

$NewHostNames       = preg_split("[\s+]",$NewHosts, NULL, PREG_SPLIT_NO_EMPTY);
$NewHostIPs         = preg_split("[\s+]",$NewIPs, NULL, PREG_SPLIT_NO_EMPTY);
$ExistingHostNames  = array();
$ExistingHostIPs    = array();


foreach ($ExistingHosts as $Entry => $HostName){
        $ExistingHostNames[] = $HostName['host_name'];
        }
foreach ($ExistingIPs as $Entry => $IP){
        $ExistingHostIPs[] = $IP['address'];
        }


// Duplication Check occures below:
$HostDuplicate = (array_intersect($ExistingHostNames,$NewHostNames));
$HostsToAdd    = (array_diff($NewHostNames,$ExistingHostNames));
$IPsToAdd      = (array_intersect_key($NewHostIPs,$HostsToAdd));


// List of Hosts Being Added are processed here:
echo "<strong>Hosts being Added</strong><p>";
        foreach ($HostsToAdd as $Entry){
                echo "<font color='green'><strong>$Entry</strong></font><br>";
                }
        echo "<br><br><br><br>";


// Duplicate Servers, which will be discard, are processed here:
echo "<strong>Hosts already in OpCfg - (will NOT be added)</strong><p>";
        foreach ($HostDuplicate as $Value){
                echo "<font color='red'><strong>$Value</strong></font><br>";
                }
        if (empty($HostDuplicate)){
                echo "<strong><font color='blue'>Yay! No Duplicate Entries Found!</font><strong>";
                }
        echo "<br><br><br><br>";
        }
?>


<!-- Hidden HTML Form that is used to pass the Server Names and Respective IP Addresses to be added into OpCfg -->
<div style="width 50%; float:left">
<form action="add_multiple_hosts.php" method="post">
<input type="submit" value="Add Servers to OpCfg" />
<br>
<br>
<table style="display:none;font-size:8pt" border="0">
  <tr>
    <th align="left" style="width:auto">New Servers</th>
    <th align="left" style="width:auto">New IP Addresses</th>
  </tr>
  <tr>
    <td><textarea name='ServersToAdd' rows="50" cols="35" class='html_text-box'><?php foreach($HostsToAdd as $value){echo "$value\n";}?></textarea></td>
    <td><textarea name='IPsToAdd' rows="50" cols="15" class='html_text-box'><?php foreach($IPsToAdd as $value){echo "$value\n";}?></textarea></td>
  </tr>
</table>
</form>
</div>


<?php

// Final Section where the Servers and IP Addresses are processed and added into OpCfg.
if(isset($_POST["ServersToAdd"]) && ($_POST["IPsToAdd"])){

$AddedHosts       = $_POST["ServersToAdd"];
$AddedIPs         = $_POST["IPsToAdd"];
$AddedHostNames   = preg_split("[\s+]",$AddedHosts, NULL, PREG_SPLIT_NO_EMPTY);
$AddedHostIPs     = preg_split("[\s+]",$AddedIPs, NULL, PREG_SPLIT_NO_EMPTY);


// Error checking that exits out of the process if Special Characters are found in a Hostname.
if(preg_match ("/[<!@#$%^&*> ]/",$AddedHosts)) {
                echo "<br><br><br><br>";
                echo "<b>Unable to added Hosts into OpCfg as special characters, ex. [@#$%^<>], were found in one of the Hostnames!</b>";
                exit(2);
                }

// Final Set of Hostnames and IP Addresses that will be added into OpCfg.
$Server_IP_Matches = array_combine($AddedHostNames,$AddedHostIPs);


echo "<br><br>";
echo "<br><br>";
echo "<strong>Multiple Host Import Process is Complete!</strong>";
echo "<br><br>";
echo "The following <strong>Servers</strong> have been <strong>Successfully</strong> added to
      <strong>OpCfg</strong>:<br><br>";


// Adding Servers into OpCfg.
foreach($Server_IP_Matches as $HostName => $IP){
        echo "<strong><font color='green'>Hostname = $HostName, IP Address = $IP </font></strong><br>";
        $fruity->add_multiple_hosts($HostName,$IP);
        }

// Adding Basic Extended Host Information of those Servers into OpCfg.
foreach($Server_IP_Matches as $HostName => $IP){
        $fruity->add_multiple_hosts_extended_info($HostName);
        }
}

print_window_footer();
print_footer();
?>




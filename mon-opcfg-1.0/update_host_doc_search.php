<?php
/* <--- Host Documentation Generator - [update_host_doc_search.php] -- [p. 1]--->

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

print_header("Update Host Documentation");
print("<br /><br />");
print_window_header("Generate Host Documentation in Wiki Format Below", "100%");

?>

<br>
<br>
<form action="update_host_doc_post.php" target="documentation"  method="post" >
<strong>Hostname: </strong><input type="text" size="50" name="Host_Name" />
<input type="submit" />
</form>
<br>
Type in the <strong>hostname</strong> of the <strong>Server</strong> you want to <strong>Generate Documentation</strong> for Above.
Make sure to use the <strong>EXACT NAME [FQDN]</strong> of the <strong>hostname</strong> you are trying to <strong>Generate Documenation</strong> for
as it is <strong>CASE SENSITIVE</strong>.
<p>
<b>Example Hostnames:</b>
<p>
server301.webpools.server.ad<br>
AppServer201.webappservices.ad<br>
SERVICES112.webservices.apps.net<br>
<br>

<iframe name="documentation" src="update_host_doc_post.php" scrolling="auto" width="1000" height="620"></iframe>

<?php
print_window_footer();
print_footer();
?>




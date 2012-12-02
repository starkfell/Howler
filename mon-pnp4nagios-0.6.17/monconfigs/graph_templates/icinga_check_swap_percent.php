<?php
#
# icinga_check_swap_percent.php PNP4Nagios Template
#
# Author:        Ryan Irujo
# Created:       05.24.2012
# Last Modified: 11.25.2012
#
# Description:   Customized PNP4Nagios Template for displaying graphs generated from the 'icinga_check_swap_percent'
#                plugin. Note that the 'icinga_check_swap_percent' plugin utilizes the 'check_swap' nagios-plugin. 
#                This PHP File utlizes a lot of the features found in the 'check_mk-df' template found at the following link:
#                http://docs.pnp4nagios.org/templates/check_mk-df
#
#
# Changes:       05.28.2012 - [R. Irujo]
#                - Added variables and graphical output for '$crit_percent','$used_percent', and '$free_percent'.
#
#		 11.25.2012 - [R. Irujo]
#		 - Renamed file from 'icinga_check_swap_percent_v2.php' to 'icinga_check_swap_percent.php'.
#


#
# Displays Performance Data from the 'check_swap' plugin.
#

# Declaring Variables that are used for formatting data to display in the Graphs
$crit_percent = sprintf("%.1f", ($CRIT[1] / $MAX[1]) * 100);
$used_percent = sprintf("%.1f", 100 - (($ACT[1] / $MAX[1]) * 100));
$free_percent = sprintf("%.1f", ($ACT[1] / $MAX[1]) * 100);
 

$opt[1] = "-X 0 --vertical-label MB -l 0 -u $MAX[1] --title \"Swap usage $hostname /  $NAGIOS_SERVICEDISPLAYNAME\" ";

# Graph Definitions
$def[1] = "DEF:var1=$RRDFILE[1]:$DS[1]:AVERAGE ";
$def[1] .= "AREA:var1#c6c6c6:\"$NAGIOS_SERVICEDISPLAYNAME\\n\" ";
$def[1] .= "LINE1:var1#003300: ";
if ($MAX[1] != "") {
        $def[1] .= "HRULE:$MAX[1]#003300:\"Capacity $MAX[1] MB \" ";
}
if ($WARN[1] != "") {
        $def[1] .= "HRULE:$WARN[1]#ffff00:\"Warning on $WARN[1] MB \" ";
}
if ($CRIT[1] != "") {
        $def[1] .= "HRULE:$CRIT[1]#ff0000:\"Critical on $CRIT[1] MB \\n\" ";
}

# This sectin outputs the Critical Threshold Percentage Value, Used Space in GB, and Free Space in GB.

$def[1] .= rrd::hrule( $used_percent, "#c6c6c6", "Used - $used_percent %     ");
$def[1] .= rrd::hrule( $free_percent, "#c6c6c6", "Free - $free_percent %    ");
$def[1] .= rrd::hrule( $crit_percent, "#c6c6c6", "Critical - $crit_percent %\\n");


$def[1] .= "GPRINT:var1:LAST:\"%6.2lf MB currently free  \\n\" ";
$def[1] .= "GPRINT:var1:MAX:\"%6.2lf MB max free \\n\" ";
$def[1] .= "GPRINT:var1:AVERAGE:\"%6.2lf MB average free\" ";



#
# [Check Time] - Returns back the amount of time that the Plugin took to run.
#
$opt[2] = "--vertical-label \"$UNIT[2]\" --title \"Check Time - $hostname /  $NAGIOS_SERVICEDISPLAYNAME\" ";

$def[2] =  "DEF:var1=$RRDFILE[2]:$DS[2]:AVERAGE " ;
if ($WARN[2] != "") {
        $def[2] .= "HRULE:$WARN[2]#FFFF00 ";
}
if ($CRIT[2] != "") {
        $def[2] .= "HRULE:$CRIT[2]#FF0000 ";
}

$def[2] .= rrd::gradient("var1", "FFCC00", "FF0000", "$NAME[2]");
$def[2] .= "LINE1:var1#333333 " ;
$def[2] .= rrd::gprint("var1", array("LAST","MAX","AVERAGE"), "%6.2lf %s$UNIT[2]");



?>

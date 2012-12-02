<?php
#
# icinga_check_users.php PNP4Nagios Template
#
# Author:        Ryan Irujo
# Created:       05.28.2012
# Last Modified: 05.28.2012
#
# Description:   Customized PNP4Nagios Template for displaying graphs generated from the 'icinga_check_users' plugin.
#                Note that the 'icinga_check_users' NRPE definition utilizes the 'icinga_check_users' custom plugin.
#                This PHP File utlizes a lot of the features found in the 'check_mk-df' template found at the following link:
#                http://docs.pnp4nagios.org/templates/check_mk-df
#
# Changes:	 11.25.2012 - [R. Irujo]
#		 - Renamed file from 'icinga_check_users_v2.php' to 'icinga_check_users.php'.
#


#
# [Check Users] - Returns back the number of Current Users logged into the Host.
#
$opt[1] = "--vertical-label \"# of Users\" --title \" $hostname / $NAGIOS_SERVICEDISPLAYNAME \" ";
$def[1] =  "DEF:var1=$RRDFILE[1]:$DS[1]:AVERAGE " ;

if ($WARN[1] != "") {
        $def[1] .= "HRULE:$WARN[1]#FFFF00 ";
}
if ($CRIT[1] != "") {
        $def[1] .= "HRULE:$CRIT[1]#FF0000 ";
}

$def[1] .= rrd::gradient("var1", "#244F1E", "#1EFF00", "Current Users \\t");
$def[1] .= "GPRINT:var1:LAST:\"Current\: %.0lf             \" ";
$def[1] .= "GPRINT:var1:AVERAGE:\"Avg\: %.0lf  \\t\\t\" ";
$def[1] .= "GPRINT:var1:MAX:\"Max\: %.0lf \\n\" ";
$def[1] .= rrd::hrule( $WARN[1], "#FFFF00", "Warning - $WARN[1] \\n");
$def[1] .= rrd::hrule( $CRIT[1], "#FF0000", "Critical - $CRIT[1] \\n");


#
# [Check Time] - Returns back the amount of time that the Plugin took to run.
#
$opt[2] = "--vertical-label \"$UNIT[2]\" --title \"Check Time - $hostname / $NAGIOS_SERVICEDISPLAYNAME \" ";

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

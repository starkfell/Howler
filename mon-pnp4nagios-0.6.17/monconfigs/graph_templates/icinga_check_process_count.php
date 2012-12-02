<?php
#
# icinga_check_process_count.php PNP4Nagios Template
#
# Author:        Ryan Irujo
# Created:       05.28.2012
# Last Modified: 11.25.2012
#
# Description:   Customized PNP4Nagios Template for displaying graphs generated from the 'icinga_check_process_count' plugin.
#                Note that the 'icinga_check_process_count' NRPE definition utilizes the 'icinga_check_process_count' custom plugin.
#                This PHP File utlizes a lot of the features found in the 'check_mk-df' template found at the following link:
#                http://docs.pnp4nagios.org/templates/check_mk-df
#
# Changes:       11.25.2012 - [R. Irujo]
#		 - Renamed file from 'icinga_check_process_count_v2.php' to 'icinga_check_process_count.php'.
#

#
# [Process Count] - Returns back the number of processes found running on the Host.
#
$opt[1] = "--vertical-label \"$UNIT[1]\" --title \"Process State - $hostname / $NAGIOS_SERVICEDISPLAYNAME\" ";

$def[1] =  "DEF:var1=$RRDFILE[1]:$DS[1]:AVERAGE " ;
if ($WARN[1] != "") {
        $def[1] .= "HRULE:$WARN[1]#FFFF00 ";
}
if ($CRIT[1] != "") {
        $def[1] .= "HRULE:$CRIT[1]#FF0000 ";
}

$def[1] .= rrd::gradient("var1", "66CCFF", "009966", "$NAME[1]\\t");
$def[1] .= "GPRINT:var1:LAST:\"Current\: %6.0lf \" ";
$def[1] .= "GPRINT:var1:AVERAGE:\"Avg\: %6.0lf \" ";
$def[1] .= "GPRINT:var1:MAX:\"Max\: %6.0lf \\n\" ";
$def[1] .= rrd::hrule( $CRIT[1], "#ff0000", "Critical - $CRIT[1]\\n" );


#
# [Check Time] - Returns back the amount of time that the Plugin took to run.
#
$opt[2] = "--vertical-label \"$UNIT[2]\" --title \"Check Time - $hostname / $NAGIOS_SERVICEDISPLAYNAME\" ";

$def[2] =  "DEF:var1=$RRDFILE[2]:$DS[2]:AVERAGE " ;
if ($WARN[2] != "") {
        $def[2] .= "HRULE:$WARN[2]#FFFF00 ";
}
if ($CRIT[2] != "") {
        $def[2] .= "HRULE:$CRIT[2]#FF0000 ";
}

$def[2] .= rrd::gradient("var1", "FFCC00", "FF0000", "$NAME[2]\\t");
$def[2] .= "GPRINT:var1:LAST:\"Current\: %6.2lf %s$UNIT[2]\" ";
$def[2] .= "GPRINT:var1:AVERAGE:\"Avg\: %6.2lf %s$UNIT[2]\" ";
$def[2] .= "GPRINT:var1:MAX:\"Max\: %6.2lf %s$UNIT[2]\\n\" ";


?>

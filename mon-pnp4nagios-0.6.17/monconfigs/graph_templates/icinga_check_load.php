<?php
#
# icinga_check_load.php PNP4Nagios Template
#
# Author:        Ryan Irujo
# Created:       05.28.2012
# Last Modified: 11.25.2012
#
# Description:   Customized PNP4Nagios Template for displaying graphs generated from the 'icinga_check_load' plugin.
#                Note that the 'icinga_check_load' NRPE definition utilizes the 'icinga_check_load' custom plugin.
#                This PHP File utlizes a lot of the features found in the 'check_mk-df' template found at the following link:
#                http://docs.pnp4nagios.org/templates/check_mk-df
#
# Changes:	 11.25.2012 - [R. Irujo]
#		 - Renamed file from 'icinga_check_load_v2.php' to 'icinga_check_load.php'.
#


#
# [CPU Load] - Returns back the CPU Load for 1,5, and 15 Minutes Counters on the Host.
#
$opt[1] = "--vertical-label Load -l0  --title \"CPU Load for $hostname / $NAGIOS_SERVICEDISPLAYNAME\" ";

$def[1]  = rrd::def("var1", $RRDFILE[1], $DS[1], "AVERAGE");
$def[1] .= rrd::def("var2", $RRDFILE[2], $DS[2], "AVERAGE");
$def[1] .= rrd::def("var3", $RRDFILE[3], $DS[3], "AVERAGE");

if ($WARN[1] != "") {
    $def[1] .= "HRULE:$WARN[1]#FFFF00 ";
}
if ($CRIT[1] != "") {
    $def[1] .= "HRULE:$CRIT[1]#FF0000 ";       
}

# [15 Minute] Load Value
$def[1] .= rrd::area("var3", "#CC66FF", "[Load 15]") ;
$def[1] .= "GPRINT:var3:LAST:\"Current\: %6.2lf \" ";
$def[1] .= "GPRINT:var3:AVERAGE:\"Avg\: %6.2lf \" ";
$def[1] .= "GPRINT:var3:MAX:\"Max\: %6.2lf \\n\" ";

# [5 Minute] Load Value
$def[1] .= rrd::area("var2", "#336699", "[Load 5] ") ;
$def[1] .= "GPRINT:var2:LAST:\"Current\: %6.2lf \" ";
$def[1] .= "GPRINT:var2:AVERAGE:\"Avg\: %6.2lf \" ";
$def[1] .= "GPRINT:var2:MAX:\"Max\: %6.2lf \\n\" ";

# [1 Minute] Load Value
$def[1] .= rrd::area("var1", "#33CC66", "[Load 1] ") ;
$def[1] .= "GPRINT:var1:LAST:\"Current\: %6.2lf \" ";
$def[1] .= "GPRINT:var1:AVERAGE:\"Avg\: %6.2lf \" ";
$def[1] .= "GPRINT:var1:MAX:\"Max\: %6.2lf \\n\" ";


#
# [Check Time] - Returns back the amount of time that the Plugin took to run.
#
$opt[4] = "--vertical-label \"$UNIT[4]\" --title \"Check Time - $hostname / $NAGIOS_SERVICEDISPLAYNAME\" ";

$def[4] =  "DEF:var1=$RRDFILE[2]:$DS[4]:AVERAGE " ;
if ($WARN[4] != "") {
        $def[4] .= "HRULE:$WARN[4]#FFFF00 ";
}
if ($CRIT[4] != "") {
        $def[4] .= "HRULE:$CRIT[4]#FF0000 ";
}

$def[4] .= rrd::gradient("var1", "FFCC00", "FF0000", "$NAME[4]");
$def[4] .= "GPRINT:var1:LAST:\"Current\: %6.2lf %s$UNIT[4]\" ";
$def[4] .= "GPRINT:var1:AVERAGE:\"Avg\: %6.2lf %s$UNIT[4]\" ";
$def[4] .= "GPRINT:var1:MAX:\"Max\: %6.2lf %s$UNIT[4]\\n\" ";



?>

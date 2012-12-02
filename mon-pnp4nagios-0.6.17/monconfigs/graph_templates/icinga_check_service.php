<?php
#
# Copyright (c) 2006-2010 Joerg Linge (http://www.pnp4nagios.org)
#
# Plugin: icinga_check_service
#
# Last Modified By: [R. Irujo]
# Last Modified:    05.17.2012
#
#
# [Process State] - Returns back a value of "1" if the Service is running and a "0" if it is not.
#
$opt[1] = "--vertical-label \"$UNIT[1]\" --title \"Service State - $hostname / $servicedesc\" ";

$def[1] =  "DEF:var1=$RRDFILE[1]:$DS[1]:AVERAGE " ;
if ($WARN[1] != "") {
        $def[1] .= "HRULE:$WARN[1]#FFFF00 ";
}
if ($CRIT[1] != "") {
        $def[1] .= "HRULE:$CRIT[1]#FF0000 ";
}

$def[1] .= rrd::gradient("var1", "66CCFF", "009966", "$NAME[1]");
$def[1] .= "LINE1:var1#666666 " ;
$def[1] .= rrd::gprint("var1", array("LAST","MAX","AVERAGE"), "%6.2lf $UNIT[1]");


#
# [Check Time] - Returns back the amount of time that the Plugin took to run.
#
$opt[2] = "--vertical-label \"$UNIT[2]\" --title \"Check Time - $hostname / $servicedesc\" ";

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

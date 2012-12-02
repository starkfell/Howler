<?php
#
# Copyright (c) 2006-2010 Joerg Linge (http://www.pnp4nagios.org)
#
# Plugin: check_users - [command = check_local_users]
#
# Last Modified By: [R. Irujo]
# Last Modified:    05.17.2012
#
# Purpose:          Displays the Number of Users logged in to a Host.
#

$opt[1] = "--lower=$MIN[1] --vertical-label \"# of Users\"  --title \"Current Users - $hostname\" ";


$def[1] =  "DEF:var1=$RRDFILE[1]:$DS[1]:MAX " ;
$def[1] .= "AREA:var1#00FF00:\"Users logged in \" " ;
$def[1] .= "LINE1:var1#000000:\"\" " ;

if ($WARN[1] != "") {
        $def[1] .= "HRULE:$WARN[1]#FFFF00 ";
}
if ($CRIT[1] != "") {
        $def[1] .= "HRULE:$CRIT[1]#FF0000 ";
}

$def[1] .= "GPRINT:var1:LAST:\"%.0lf $UNIT[1] Last \" ";
$def[1] .= "GPRINT:var1:MAX:\"%.0lf $UNIT[1] Max \" ";
$def[1] .= "GPRINT:var1:AVERAGE:\"%.0lf $UNIT[1] Average \" ";
?>

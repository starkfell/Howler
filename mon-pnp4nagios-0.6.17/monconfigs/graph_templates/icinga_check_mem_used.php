<?php
#
# icinga_check_mem_used.php PNP4Nagios Template
#
# Author:        Ryan Irujo
# Created:       07.10.2012
# Last Modified: 07.10.2012
#
# Description:   Customized PNP4Nagios Template for displaying graphs generated from the 'icinga_check_mem_used' plugin.
#
# Changes:
#


# Declaring Variables that are used for formatting data to display in the Graphs

$Total_GB = sprintf("%.2lf", $ACT[1] / 1048576);
$Free_GB  = sprintf("%.2lf", $ACT[3] / 1048576);
$Used_GB  = sprintf("%.2lf", $ACT[2] / 1048576);


#
# [Memory Utilization] - Returns back Total, Used, Free, and Cached Memory in MB Values.
#

$opt[1]  = " --vertical-label '$UNIT[1]' --lower-limit 0 --rigid --title \"$NAGIOS_SERVICEDISPLAYNAME - $hostname\" ";

$def[1]  = rrd::def("var1", $RRDFILE[1], $DS[1], "AVERAGE");
$def[1] .= rrd::def("var2", $RRDFILE[2], $DS[2], "AVERAGE");
$def[1] .= rrd::def("var3", $RRDFILE[3], $DS[3], "AVERAGE");
$def[1] .= rrd::def("var4", $RRDFILE[4], $DS[4], "AVERAGE");


# Memory [Total, Free, & Used] formatted to display in GB.
$def[1] .= rrd::hrule($Total_GB, "#FFFFFF", "Total = $Total_GB GB \\t\\t" );
$def[1] .= rrd::hrule($Free_GB, "#FFFFFF",  "Free  = $Free_GB GB \\t\\t" );
$def[1] .= rrd::hrule($Used_GB, "#FFFFFF",  "Used  = $Used_GB GB" );

# Memory Free
$def[1] .= rrd::area("var3", "#33FF66", "$NAME[3]  ","STACK");
$def[1] .= rrd::gprint("var3", "LAST", "Current %6.0lf KB");
$def[1] .= rrd::gprint("var3", "AVERAGE", "Avg %6.0lf KB");
$def[1] .= rrd::gprint("var3", "MAX", "Max %6.0lf KB " . "\\n");

# Memory Used
$def[1] .= rrd::area("var2", "#FF9933", "$NAME[2]  ","STACK");
$def[1] .= rrd::gprint("var2", "LAST", "Current %6.0lf KB");
$def[1] .= rrd::gprint("var2", "AVERAGE", "Avg %6.0lf KB");
$def[1] .= rrd::gprint("var2", "MAX", "Max %6.0lf KB " . "\\n");

# Memory Cached
$def[1] .= rrd::area("var4", "#00000020", "$NAME[4]");
$def[1] .= rrd::gprint("var4", "LAST", "Current %6.0lf KB");
$def[1] .= rrd::gprint("var4", "AVERAGE", "Avg %6.0lf KB");
$def[1] .= rrd::gprint("var4", "MAX", "Max %6.0lf KB " . "\\n");


?>



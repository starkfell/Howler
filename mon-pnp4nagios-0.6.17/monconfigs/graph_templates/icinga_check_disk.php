<?php
#
# icinga_check_disk.php PNP4Nagios Template
#  
# Author:        Ryan Irujo
# Created:       05.24.2012
# Last Modified: 11.25.2012
#
# Description:   Customized PNP4Nagios Template for displaying graphs generated from the 'icinga_check_disk' plugin.
#		 Note that the 'icinga_check_disk' plugin utilizes the 'check_disk' nagios-plugin. This PHP File
#                utlizes a lot of the features found in the 'check_mk-df' template found at the following link:
#                http://docs.pnp4nagios.org/templates/check_mk-df
#
#
# Changes:       11.25.2012 - [R. Irujo]
#		 - Renamed file from 'icinga_check_disk_v2.php' to 'icinga_check_disk.php'.
#		 - Added '$ds_name' Variable Entry to show the Service Display Name in the Data Source field.
#

# Declaring Variables that are used for formatting data to display in the Graphs
$crit_percent = sprintf("%.1f", ($CRIT[1] / $MAX[1]) * 100);
$size_max_gb  = sprintf("%.1f", $MAX[1] / 1024.0);
$size_crit_gb = sprintf("%.1f", ($MAX[1] - $CRIT[1]) / 1024.0);
$size_last_gb = sprintf("%.1f", $ACT[1] / 1024.0);
$size_free_gb = sprintf("%.1f", ($MAX[1] - $ACT[1]) / 1024.0);


#
# [Disk Space] - Returns back Used, Free, and Max amount of Disk Space available in GB and Percentage Values.
#
$ds_name[0] = "$NAGIOS_SERVICEDISPLAYNAME";
$opt[1]     = "--vertical-label '% Used' --slope-mode -l 0 -u 100 --title '$hostname: $NAGIOS_SERVICEDISPLAYNAME [Disk Size = $size_max_gb'GB']'";


# Graph Definitions [ Thank You Romuald Fronteau! ]
$def[1]  = "DEF:fs=$rrdfile:$DS[1]:MAX ";
$def[1] .= "CDEF:var1=fs,$MAX[1],/,100,* ";

$def[1] .=  "CDEF:sp1=var1,100,/,10,* " ;
$def[1] .=  "CDEF:sp2=var1,100,/,20,* " ;
$def[1] .=  "CDEF:sp3=var1,100,/,30,* " ;
$def[1] .=  "CDEF:sp4=var1,100,/,40,* " ;
$def[1] .=  "CDEF:sp5=var1,100,/,50,* " ;
$def[1] .=  "CDEF:sp6=var1,100,/,60,* " ;
$def[1] .=  "CDEF:sp7=var1,100,/,70,* " ;
$def[1] .=  "CDEF:sp8=var1,100,/,80,* " ;
$def[1] .=  "CDEF:sp9=var1,100,/,90,* " ;
 
$def[1] .= "VDEF:slope=var1,LSLSLOPE " ;
$def[1] .= "VDEF:int=var1,LSLINT " ;
$def[1] .= "CDEF:proj=var1,POP,slope,COUNT,*,int,+ " ;

# Displaying and formatting Disk Used in Graph in Green. 
$def[1] .= "AREA:var1#84C21F:\" [% Disk Used] \\t\\t\" ";
$def[1] .= "AREA:sp9#84C21F: " ;
$def[1] .= "AREA:sp8#8CC427: " ;
$def[1] .= "AREA:sp7#9CCA37: " ;
$def[1] .= "AREA:sp6#A5CD3F: " ;
$def[1] .= "AREA:sp5#B4D14F: " ;
$def[1] .= "AREA:sp4#C5D760: " ;
$def[1] .= "AREA:sp3#D5DC70: " ;
$def[1] .= "AREA:sp2#E6E280: " ;
$def[1] .= "AREA:sp1#F6E790: " ;
 
# This section outputs the Current, Average, and Max Percentage Values.
$def[1] .= "LINE1:var1#226600: ";
$def[1] .= "GPRINT:var1:LAST:\"Current\: %6.2lf %%\" ";
$def[1] .= "GPRINT:var1:AVERAGE:\"Avg\: %6.2lf %%\" ";
$def[1] .= "GPRINT:var1:MAX:\"Max\: %6.2lf %%\\n\" ";

# This sectin outputs the Critical Threshold Percentage Value, Used Space in GB, and Free Space in GB.
$def[1] .= rrd::hrule( $crit_percent, "#ff0000", "Critical - $crit_percent %\\n" );
$def[1] .= rrd::hrule( $size_last_gb, "#ffffff", "Used - $size_last_gb GB\\n" );
$def[1] .= rrd::hrule( $size_free_gb, "#ffffff", "Free - $size_free_gb GB\\n" );


#
# [Check Time] - Returns back the amount of time that the Plugin took to run.
#
$opt[2] = "--vertical-label \"$UNIT[2]\" --title \"[Check Time] - $hostname: $NAGIOS_SERVICEDISPLAYNAME\" ";

$def[2] = "DEF:var1=$RRDFILE[2]:$DS[2]:AVERAGE " ;
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

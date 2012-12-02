#!/bin/sh
# the next line restarts using tclsh -*- tcl -*- \
exec tclsh "$0" "$@"

#package require Tnm 3.0
package require Rrd 1.4.7

set rrdfile "[lindex $argv 0]-[lindex $argv 1].rrd"

# create rrdfile if not yet existent
if {[file exists $rrdfile] == 0} {
    Rrd::create $rrdfile --step 5 \
	    DS:inOctets:COUNTER:10:U:U DS:outOctets:COUNTER:10:U:U \
	    RRA:AVERAGE:0.5:1:12
}

# get an snmp session context
set session [Tnm::snmp generator -address [lindex $argv 0]]

# walk through the ifDescr column to find the right interface
$session walk descr IF-MIB!ifDescr {

    # is this the right interface?
    if {"[Tnm::snmp value $descr 0]" == "[lindex $argv 1]"} {

	# get the instance part of this table row
	set inst [lindex [Tnm::mib split [Tnm::snmp oid $descr 0]] 1]

	# get the two interface's octet counter values
	set in [lindex [lindex [$session get IF-MIB!ifInOctets.$inst] 0] 2]
	set out [lindex [lindex [$session get IF-MIB!ifOutOctets.$inst] 0] 2]

	# write the values to the rrd
	puts "$in $out"
	Rrd::update $rrdfile --template inOctets:outOctets N:$in:$out

	Rrd::graph gaga.png --title "gaga" \
		DEF:in=$rrdfile:inOctets:AVERAGE \
		DEF:out=$rrdfile:outOctets:AVERAGE \
		AREA:in#0000FF:inOctets \
		LINE2:out#00C000:outOctets

	#puts [Rrd::fetch $rrdfile AVERAGE]
    }
}

#!/usr/bin/perl
#
# (c) 2003              Stéphane Urbanovski <stephane.urbanovski@ac-nancy-metz.fr>
#                                       DSI - Académie de Nancy-Metz
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# you should have received a copy of the GNU General Public License
# along with this program (or with Netsaint);  if not, write to the
# Free Software Foundation, Inc., 59 Temple Place - Suite 330,
# Boston, MA 02111-1307, USA
#
# Changes:
# Sat Apr 22 19:07:07 CEST 2006 (Volker Uhrig <volker@uhrig.eu.org>)
#  - Fixed some minor errors
#  - moved from libplugins.pm to nagios plugins utils.pm
#  - added and fixed Timeout option
# Source of changes: http://uhrig.eu.org/

use strict;
use warnings;

use File::Basename;
use Getopt::Long;
use Socket;
use lib "/usr/lib64/nagios/plugins";
use utils qw($TIMEOUT %ERRORS &print_revision &support &usage);

# Squid default connection port :
our $DEFAULT_PORT=3128;
# Warning and critical filedescriptor levels :
our $DEFAULT_FD_WARNING = 200;
our $DEFAULT_FD_CRITICAL = 50;
# Debug?
my $DEBUG = 0;

# Get the options :
# $opt_H = remote host
# $opt_P = [port] $DEFAULT_PORT
# $opt_p = cache manager password
# $opt_w = [warn level]  (unused yet)
# $opt_c = [critical level] (unused yet)
# $opt_t = [timeout]

use vars qw( $opt_H $opt_P $opt_w $opt_c $opt_p $opt_d $opt_t);

# Squid default connection port :
$opt_P = $DEFAULT_PORT;
$opt_w = $DEFAULT_FD_WARNING;
$opt_c = $DEFAULT_FD_CRITICAL;


my ($opt_V,$opt_h,$opt_m);

Getopt::Long::Configure('bundling');
GetOptions (
	"V"		=> \$opt_V,	"version"		=> \$opt_V,
	"h|?"	=> \$opt_h,	"help"			=> \$opt_h,
	"man"	=> \$opt_m,
	"d=i"	=> \$DEBUG,	"debug=i"		=> \$DEBUG,
	"w=i"	=> \$opt_w,	"warning=i"		=> \$opt_w,
	"c=i"	=> \$opt_c,	"critical=i"	=> \$opt_c,
	"H=s"	=> \$opt_H, "hostname=s"	=> \$opt_H,
    "t=s"   => \$opt_t, "timeout=i"     => \$opt_t,
	"p=s"	=> \$opt_p, "password=s"	=> \$opt_p,
	"P=i"	=> \$opt_P, "port=i"		=> \$opt_P,
);

# check if someone want help...
if ($opt_h) {print_help(); exit $ERRORS{'OK'};}

# check if we got an hostname
# TODO: opt_m
if ( (!defined ($opt_H)) || ($opt_H eq "")) {
	print "Missing hostname parameter!\n";
	&usage($opt_m);
} 

# Set password to "" if none defined
if ( (!defined ($opt_p)) || ($opt_p eq "")) {
	$opt_p = "";
} else {
	$opt_p = "\@$opt_p";
}
# define timeout
if ( (!defined ($opt_t)) || ($opt_t eq "")) {
    $opt_t = $TIMEOUT;
}

# Define the Message we send to ask for values
my $sendMsg = "GET cache_object://$opt_H/info".$opt_p." HTTP/1.1\n\n";

# print sendet command if in debug mode
if ($DEBUG) { 
	print "Sended [ ".$sendMsg."]";
}

# Connecting to Squid:
my $remoteaddr ;
my $proto = getprotobyname('tcp');
my $paddr;

# Check if given hostname is a real host
if ( ! ($remoteaddr = inet_aton("$opt_H")) ) {
	print "Unkown host: $opt_H $!\n";
	exit $ERRORS{'UNKNOWN'};
}

if ( ! ($paddr = sockaddr_in($opt_P, $remoteaddr)) ) {
	print "Can't create info for connection: $!\n";
	exit $ERRORS{'UNKNOWN'};
}
if (!socket(SERVER, PF_INET, SOCK_STREAM, $proto) ) {
	print "Can't create socket: $!\n";
	exit $ERRORS{'UNKNOWN'};
}

setsockopt(SERVER, SOL_SOCKET, SO_REUSEADDR, 1);

# Just in case of problems, let's not hang Nagios
$SIG{'ALRM'} = sub {
	close(SERVER);
	print "No Answer from Squid!\n";
	exit $ERRORS{'UNKNOWN'};
};
alarm($opt_t);

# Print error if we cant connect
if (!connect(SERVER, $paddr) ) {
	print "Can't connect to Squid at \'$opt_H:$opt_P\': $!\n";
	exit $ERRORS{'UNKNOWN'};
}

select(SERVER);
$| = 1;
select(STDOUT);

print SERVER $sendMsg;
my @servanswer = <SERVER>;
alarm(0);
close(SERVER);

if ($DEBUG) { print "Received [ ".join("\n",@servanswer)."]\n"; }

$servanswer[0] =~ s/\r\n//;

my $state = "UNKNOWN";
my $answer = "Unknown answer (".$servanswer[0].")";

if ($servanswer[0] =~ /^HTTP\/1.\d 200 OK/) {
	$state = "OK";
	$answer = "Squid cache ";
	my @infos;
	my $errmsg;
	foreach my $l (@servanswer) {
		if ($l =~ /HTTP requests per minute:\s+([\d\.\-]+)/) {
			push(@infos, $1." http/mn");
		}
		if ($l =~ /Available number of file descriptors:\s+(\d+)/) {
			push(@infos, $1." FreeFileDesc");
			if ($1 < $opt_c) {
				$state = "CRITICAL";
				$errmsg = "Low available file descriptors !";
			} elsif ($1 < $opt_w) {
				$state = "WARNING";
				$errmsg = "Low available file descriptors !";
			}
		}
	}
	$answer .= $state;
	if ($errmsg) {
		$answer .= " : ".$errmsg;
	}
	if (@infos) {
		$answer .= " (".join(", ",@infos).")";
	}
}


print $answer."\n";
exit $ERRORS{$state};

sub print_help {
	print "\n";
	print "Perl Check Squid plugin for Nagios\n";
	print "Copyright (c) 2003 Stéphane Urbanovski\n";
    print "Changed 2006 by Volker Uhrig\n";
	print "\n";
	print "Usage: $0 -H <remote host> -p <password> [-P <port>] [-w <warn>] [-c <crit>] [-t <timeout>]\n";
	print "\n";
	print "<remote host> = Host running Squid.\n";
	print "<warn>        = Minimum available number of file descriptors.\n	Defaults to $DEFAULT_FD_WARNING.\n";
	print "<crit>        = Minimum available number of file descriptors.\n	Defaults to $DEFAULT_FD_CRITICAL.\n";
	print "<port>        = Port that Squid is listenning on (http).\n	Defaults to $DEFAULT_PORT.\n";
	print "<password>    = Cache manager password.\n	Defaults to none.\n";
	exit $ERRORS{"UNKNOWN"};
}



sub nagiosconf {

	print "# 'check_squid' command definition\n";
	print "define command {\n";
	print "	command_name  check_squid\n";
	print '	command_line  $USER1$/check_squid.pl -H $HOSTADDRESS$ -P $ARG1$ -p $ARG2$\n';
	print "}\n";

}
__END__

=head1 NAME

Nagios plugins to check Squid cache

=head1 SYNOPSIS

B<check_squid.pl> S<-H I<remote host>> S<[-p I<password>]> S<[-P I<port>]> S<[-w I<warn>]> S<[-c I<crit>]> S<[-t I<timeout>]>

=head1 OPTIONS

=over 4

=item B<-H> I<remote host>

Host running Squid.

=item B<-p> I<password>

Cache manager password (see Squid configuration).

=item B<-P> I<port>

Port that Squid is listenning on (http)

=item B<-w> I<warn>

Minimum available number of file descriptors to trigger a WARNING level.

=item B<-c> I<crit>

Minimum available number of file descriptors to trigger a CRITICAL level.

=item B<-t> I<timeout>

Time to wait for a response, in seconds.

=back

=head1 NAGIOS CONGIGURATIONS

In F<checkcommands.cfg> you have to add :

	# 'check_squid' command definition
	define command {
	  command_name  check_squid
	  command_line  $USER1$/check_squid.pl -H $HOSTADDRESS$ -P $ARG1$ -p $ARG2$
	}

or 

	# 'check_squid' command definition
	define command {
	  command_name  check_squid
	  command_line  $USER1$/check_squid.pl -H $HOSTADDRESS$ -P $ARG1$ -p $ARG2$ -w $ARG3$ -c $ARG4$ -t $ARG5$
	}

if you want to be able to set warning and critical levels.

In F<services.cfg> you just have to add something like :

	define service {
	  name                  host-squid-service
	  host_name             cache.exemple.org
	  normal_check_interval 10
	  retry_check_interval  5
	  contact_groups        linux-admins
	  service_description   Squid
	  check_command         check_squid!3128!squidPassw0rd
	}

=head1 SQUID CONGIGURATIONS

This plugins is using Squid's cache_object protocol to get some informations from the Squid info page.

Here is a F<squid.conf> example :

	acl manager proto cache_object
	# Nagios host :
	acl racvision src 172.29.1.1/255.255.255.255
	
	# Deny access to everyone exept Nagios host :
	http_access deny manager !racvision
	
	# The cache manager password
	cachemgr_passwd squidPassw0rd info

=head1 AUTHOR

Stéphane Urbanovski <stephane.urbanovski@ac-nancy-metz.fr>
Changes by Volker Uhrig <volker@uhrig.eu.org>

Thanks to Charlie Cook & Nick Reinking and many others for writing modules that have been used as example for this one.

=cut 

###################################################################################
#                                                                                 #
# Title:     mon-nrpe-plugin - SPEC File                                          #
# Author:    Ryan Irujo                                                           #
#                                                                                 #
# Purpose:   Created a New SPEC File for RPM builds so that                       #
#            Administrators, other than Rocket Scientists, could manage it.       #
#                                                                                 #
#            Change Log is located at the bottom of the file.                     #
#                                                                                 #
###################################################################################

Summary:       Host/service/network monitoring agent for Nagios
Name:          mon-nrpe-plugin
Version:       2.12
Release:       3.4
License:       GPLv2
Group:         Application/System
URL:           http://www.nagios.org
Source0:       %{name}-%{version}-%{release}.tar.gz
BuildRoot:     %{_tmppath}/%{name}-%{version}-%{release}-root
Requires:      bash, grep
PreReq:        /usr/bin/logger, chkconfig, sh-utils, shadow-utils, sed, initscripts, fileutils, mktemp

%description 
NRPE is a system daemon that will execute various Nagios plugins locally on behalf of 
a remote (monitoring) host that uses the check_nrpe plugin.  Various plugins that can 
be executed by the daemon are available at:http://sourceforge.net/projects/nagiosplug
This package provides the core NRPE agent and the check_nrpe plugin.

%prep 
%setup -qn %{name}-%{version}

%build
./configure \
        --with-init-dir=/etc/init.d \
        --with-nrpe-port=5666 \
        --with-nrpe-user=howler \
        --with-nrpe-group=howler \
        --with-nagios-user=howler \
        --with-nagios-group=howler \
        --prefix=/usr \
        --exec-prefix=/usr/sbin \
        --bindir=/usr/sbin \
        --sbindir=/usr/lib/nagios/cgi \
        --libexecdir=/usr/lib64/nagios/plugins \
        --datadir=/usr/share/nagios \
        --sysconfdir=/etc/nagios \
        --localstatedir=/var/log/nagios \
        --enable-command-args
        make all


%install
%{__rm} -rf %{buildroot}
%{__mkdir} -p %{buildroot}
install -d -m 0755 %{buildroot}/etc/init.d
install -d -m 0755 %{buildroot}/etc/nagios
install -d -m 0755 %{buildroot}/usr/sbin
install -d -m 0755 %{buildroot}/usr/lib64/nagios/plugins

# Install templated Configuration Files
cp init-script %{buildroot}/etc/init.d/nrpe
cp src/nrpe %{buildroot}/usr/sbin

# Copying over custom 'nrpe.cfg' file.
cp monconfigs/nrpe.cfg %{buildroot}/etc/nagios/nrpe.cfg

# Copying over custom set of Nagios/Icinga Plugins including the custom 'check_nrpe' plugin.
cp monconfigs/nagios-plugins/* %{buildroot}/usr/lib64/nagios/plugins

# Copying over custom 'check_nrpe' plugin.
cp libexec/check_nrpe %{buildroot}/usr/lib64/nagios/plugins


%pre 
# Macro that prints mesages to syslog at when the NRPE RPM is being installed.
%define nnmmsg logger -t %{name}/rpm

# Stop the NRPE Daemon
/sbin/service nrpe stop

# Clean out old Nagios Plugins and NRPE Config File(s)
rm -rf /usr/lib64/nagios/plugins/* && echo "The Nagios Plugins Directory has been emptied."
rm -rf /etc/nagios/nrpe* && echo "Old nrpe.cfg file(s) have been removed."

# Create the Icinga Service group on the system if necessary
if grep ^howler: /etc/group; then
	: # group already exists
else
	/usr/sbin/groupadd howler || %nnmmsg Unexpected error adding group "howler". Aborting install process.
fi

# Create the Icinga Service user on the system if necessary
if id howler ; then
	: # user already exists
else
	/usr/sbin/useradd -r -d /var/log/nagios -s /bin/sh -c "howler" -g howler howler || \
		%nnmmsg Unexpected error adding user "howler". Aborting install process.
fi

# if LSB standard /etc/init.d does not exist, create it as a symlink to the first match we find.
if [ -d /etc/init.d -o -L /etc/init.d ]; then
  : # we're done
elif [ -d /etc/rc.d/init.d ]; then
  ln -s /etc/rc.d/init.d /etc/init.d
elif [ -d /usr/local/etc/rc.d ]; then
  ln -s  /usr/local/etc/rc.d /etc/init.d
elif [ -d /sbin/init.d ]; then
  ln -s /sbin/init.d /etc/init.d
fi


%post 
# Checking to ensure the Nagios Plugins and the 'nrpe.cfg' file have been updated successfully
PLUGIN_DIR=$(ls /usr/lib64/nagios/plugins/ | wc -l)
if [ $PLUGIN_DIR -eq 0 ]; then
	echo "The Nagios Plugins Directory is EMPTY!"
else
	echo "The Nagios Plugins Directory has been updated Successfully!"
fi

NRPE_CONFIG=$(ls /etc/nagios/nrpe.cfg | wc -l)
if [ $NRPE_CONFIG -eq 0 ]; then
        echo "The nrpe.cfg file is MISSING!"
else
        echo "The nrpe.cfg file has been updated Successfully!"
fi

# Adding NRPE Service to run at Startup and Restarting the NRPE Service after installation.
/sbin/chkconfig nrpe on && echo "The nrpe service has been set to run on startup!"
/sbin/service nrpe restart && echo "The nrpe service has been restarted."
/sbin/service nrpe status && echo "[%{name}-%{version}-%{release}] Installation Complete!"

%preun 
if [ "$1" = 0 ]; then
	/sbin/service nrpe stop > /dev/null 2>&1
	/sbin/chkconfig --del nrpe
fi


%postun 
if [ "$1" -ge "1" ]; then
	/sbin/service nrpe condrestart >/dev/null 2>&1 || :
fi

echo ""
echo "[%{name}-%{version}-%{release}] has been Uninstalled Successfully."
echo ""


%clean
rm -rf %{buildroot}


%files
%defattr(755,howler,howler)
/usr/sbin/nrpe
/usr/lib64/nagios/plugins
/etc/nagios/nrpe.cfg
/etc/init.d/nrpe
%doc


%changelog
* Thu May 10 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Reconfigured and cleaned up the SPEC File to match the other RPM Builds SPEC File format.
- Customized 'check_nrpe' plugin, built from source added to the build. The custom
  'check_nrpe' plugin is unique in that all Agent related communication issues return back
  as an UNKNOWN State instead of a CRITICAL State.

* Thu Feb 23 2012 Ryan Irujo <ryan.irujo@gmail.com>
- All Files and Folders have been assigned to the Service Icinga Account.
- monconfigs directory created.
- Custom NRPE-plugins have been placed in the nagios-plugins directory in monconfigs.
- Legacy NRPE-plugins have been placed in the nagios-plugins directory in monconfigs.
- Custom nrpe.cfg file has been placed in monconfigs.
- The nrpe Service has been configured to stop and restart on Installation.
- The /usr/lib64/nagios/plugins directory is set to empty on install to ensure environment consistency.
- The /etc/nagios/nrpe.cfg file is removed on install to ensure the latest configuration is in use.



#################################################################################
#										#
# OpCfg Icinga Configuration Application					#
#										#
# Author:    [R. Irujo]								#
# Inception: 04.14.2012								#
#										#
#################################################################################

%define name mon-opcfg
%define version 1.0
%define release 1.2.5

# Macro that print mesages to syslog at package (un)install time
%define nnmmsg logger -t %{name}/rpm

Summary: Web Front-End Configuration Tool for Icinga
Name: %{name}
Version: %{version}
Release: %{release}
License: GPL
Group: Application/System
Source0: %{name}-%{version}-%{release}.tar.gz
BuildRoot: %{_tmppath}/%{name}-buildroot
Requires: bash, grep
PreReq: /usr/bin/logger, chkconfig, sh-utils, shadow-utils, sed, initscripts, fileutils, mktemp, php

%description
OpCfg is a Web Front-End Configuration Tool originally designed for Nagios.
The Application has since been heavily modified to work with Icinga.

%prep
%setup -q

%pre 

# Make sure that OpCfg is not already installed.
OPCFG_DIR=$(ls /usr/local/icinga/share/opcfg | wc -l)
if [ $OPCFG_DIR -gt "0" ]; then
        echo "OpCfg already exists! You must uninstall the existing version before upgrading!"
        echo ""
        echo "The following command will remove the old installation - sudo rpm -e mon-opcfg";exit 2
else
        echo ""
	echo "Installing the OpCfg Directory for Icinga.... "
        echo ""
fi

# Changing Permissions to Icinga Files and Directories to allow OpCfg to push new
# Configurations to Icinga.

echo "Modifying Icinga File and Folder Permissions to work with OpCfg"
chown howler.apache -R /usr/local/icinga/bin/
chown howler.apache -R /usr/local/icinga/etc/
chown howler.apache -R /usr/local/icinga/libexec/
chown howler.apache -R /usr/local/icinga/var/spool/*
chmod 775 -R /usr/local/icinga/bin/
chmod 775 -R /usr/local/icinga/etc/
chmod 775 -R /usr/local/icinga/libexec/
echo "Modification of Icinga File and Folder Permissions Complete!"


%post 

echo "OpCfg Files have been successfully installed. --- YOU ARE NOT FINISHED YET!"
echo ""
echo "Complete the Instructions in the README_INSTALLATION_GUIDE in /usr/local/icinga/share/opcfg ."
echo ""

%preun 


%postun

echo ""
echo "%{name}-%{version}-%{release} has been successfully uninstalled."
echo ""


%build 


%install
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT
install -d -m 0755 ${RPM_BUILD_ROOT}/usr/local/icinga/share/opcfg
install -d -m 0755 ${RPM_BUILD_ROOT}/usr/local/icinga/share/images/logos
install -d -m 0755 ${RPM_BUILD_ROOT}/usr/local/icinga/share/opcfg/logos
install -d -m 0755 ${RPM_BUILD_ROOT}/usr/local/icinga/etc/OpCfg_Backup

# Installing OpCfg Application into the Icings Share Directory
cp  * -R  ${RPM_BUILD_ROOT}/usr/local/icinga/share/opcfg

# Install Vendor Icons to Display within the Icinga Web Interface
cp vendor_icons/* ${RPM_BUILD_ROOT}/usr/local/icinga/share/images/logos

# Install Logo Icons to Display OS Logos in the OpCfg UI.
cp logos/* ${RPM_BUILD_ROOT}/usr/local/icinga/share/opcfg/logos

# Install the OpCfg Backup Folder and placeholder files  in /usr/local/icnga/etc 
cp OpCfg_Backup/* ${RPM_BUILD_ROOT}/usr/local/icinga/etc/OpCfg_Backup


%files
%defattr(-,apache,apache,-)
/usr/local/icinga/share/opcfg
/usr/local/icinga/share/images/logos
/usr/local/icinga/etc/OpCfg_Backup

%clean
rm -rf $RPM_BUILD_ROOT


%changelog
* Wed May 30 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Added the logos folder to display OS Logo Icons in the OpCfg UI.

* Sun Apr 29 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Added in changes in the [%pre] section to modify File and Folder Permissions in Icinga
  to ensure that OpCfg works properly.

* Sat Apr 14 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Renamed the RPM to 'mon-opcfg'
- Added OpCfg_Backup Folder and placeholder files to Installation.

* Sat Apr 14 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Initial Configuration




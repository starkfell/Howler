#########################################################################################
#											#
# Title:      mon-pnp4nagios - [0.6.17] - SPEC File					#
# Author:     Ryan Irujo								#
# Inception:  04.29.2012								#
#											#
# Purpose:    Created a New SPEC File for RPM builds so that				#
#             Administrators, and not just Rocket Scientists, could manage it.		#
#											#
#             Change Log is located at the bottom of the file.				#
#											#
#########################################################################################

Summary:        PNP4Nagios is a Nagios/Icinga performance data graphing solution
Name:		mon-pnp4nagios
Version: 	0.6.17
Release:	1.7
Group:	 	Applications/System
License:	GPLv2
URL:		http://www.pnp4nagios.org/
Source: 	%{name}-%{version}-%{release}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
BuildRequires:	rrdtool-devel
BuildRequires:  perl-rrdtool
Requires:	mon-rrdtool
Requires:	perl-rrdtool
Obsoletes:	pnp

%description
PNP is an addon to Nagios/Icinga which analyzes performance data provided by plugins
and stores them automatically into RRD-databases.


%prep
%setup -qn %{name}-%{version}


%build
%configure \
	--prefix="/usr/local/pnp4nagios" \
	--with-nagios-user="howler" \
	--with-nagios-group="howler" \
	--sysconfdir="/usr/local/pnp4nagios/etc" \
	--with-kohana_system="/usr/local/pnp4nagios/lib/kohana/system" \
	--with-rrdtool="/usr/local/rrdtool/bin/rrdtool" \
	--with-perl_lib_path="/usr/local/rrdtool/lib/perl/5.8.8/x86_64-linux-thread-multi" \
	--with-perfdata-logfile="/usr/local/pnp4nagios/var/perfdata.log" \
	--with-perfdata-dir="/usr/local/pnp4nagios/var/perfdata" \
	--with-perfdata-spool-dir="/usr/local/pnp4nagios/var/spool"
%{__make} %{?_smp_mflags} all


%install
%{__rm} -rf %{buildroot}
%{__mkdir} -p %{buildroot}
%{__make} fullinstall \
    DESTDIR="%{buildroot}" \
    INSTALL_OPTS="" \
    INSTALL_OPTS_WEB="" \
    COMMAND_OPTS="" \
    INIT_OPTS=""

# Creating PNP4Nagios Directories for Installation Files.
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/bin
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/custom
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/etc
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/config
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/controllers
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/core
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/fonts
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/helpers
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/i18n
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Cache
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Captcha
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Database
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Image
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Session
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/views
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/views/kohana
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/views/pagination
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/libexec
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/man
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/share
install -d -m 0755 %{buildroot}/usr/local/pnp4nagios/var

# Moving over standard PNP4Nagios Installation Files.
mv %{buildroot}/usr/bin/npcd %{buildroot}/usr/local/pnp4nagios/bin/npcd
mv %{buildroot}/usr/lib64/npcdmod.o %{buildroot}/usr/local/pnp4nagios/lib/npcdmod.o
mv %{buildroot}/usr/libexec/check_pnp_rrds.pl %{buildroot}/usr/local/pnp4nagios/libexec/check_pnp_rrds.pl
mv %{buildroot}/usr/libexec/process_perfdata.pl %{buildroot}/usr/local/pnp4nagios/libexec/process_perfdata.pl
mv %{buildroot}/usr/libexec/rrd_convert.pl %{buildroot}/usr/local/pnp4nagios/libexec/rrd_convert.pl
mv %{buildroot}/usr/lib64/kohana/system/config/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/config
mv %{buildroot}/usr/lib64/kohana/system/controllers/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/controllers
mv %{buildroot}/usr/lib64/kohana/system/core/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/core
mv %{buildroot}/usr/lib64/kohana/system/fonts/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/fonts
mv %{buildroot}/usr/lib64/kohana/system/helpers/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/helpers
mv %{buildroot}/usr/lib64/kohana/system/i18n/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/i18n/
mv %{buildroot}/usr/lib64/kohana/system/views/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/views

# Moving over Kohana Libraries. KOHLIBS variable used to shrink code and for readability.
KOHLIBS="%{buildroot}/usr/lib64/kohana/system/libraries"
mv $KOHLIBS/*.php %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/
mv $KOHLIBS/drivers/*.php %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers
mv $KOHLIBS/drivers/Cache/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Cache
mv $KOHLIBS/drivers/Captcha/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Captcha
mv $KOHLIBS/drivers/Database/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Database
mv $KOHLIBS/drivers/Image/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Image
mv $KOHLIBS/drivers/Session/* %{buildroot}/usr/local/pnp4nagios/lib/kohana/system/libraries/drivers/Session

# Copying over customized 'pnp4nagios.conf' file.
cp monconfigs/pnp4nagios.conf %{buildroot}/etc/httpd/conf.d/pnp4nagios.conf

# Copying over customized 'config.php' file.
cp monconfigs/config.php %{buildroot}/usr/local/pnp4nagios/etc/config.php

# Copying over customized 'npcd.cfg' file.
cp monconfigs/npcd.cfg %{buildroot}/usr/local/pnp4nagios/etc/npcd.cfg

# Copying over 'status-header.ssi' file. This enables mouse-over reports in the Icinga Core GUI.
cp contrib/ssi/status-header.ssi %{buildroot}/usr/local/pnp4nagios/etc/status-header.ssi

# Copying over 'rrd_add_datasource' file to the '/usr/local/pnp4nagios/custom' directory.
cp monconfigs/rrd_add_datasource-1.1.tar.gz %{buildroot}/usr/local/pnp4nagios/custom/rrd_add_datasource-1.1.tar.gz

# Copying over customized PNP4Nagios Report Templates. RPTTMPLTE variable used to shrink code for readability.
RPTTMPLTE="%{buildroot}/usr/local/pnp4nagios/share/templates.dist"
cp monconfigs/graph_templates/* $RPTTMPLTE/


%clean
rm -rf %{buildroot}


%post
# Enabling mouse-over reports in the Icinga Core GUI.
cp /usr/local/pnp4nagios/etc/status-header.ssi /usr/local/icinga/share/ssi/ && echo "Icinga Core GUI mouse-over reports enabled."

# Extracting the rrd_add_datasource Perl Script to the '/usr/local/pnp4nagios/custom' directory.
echo "Extracting rrd_add_datasource Tarball."
tar -xzf /usr/local/pnp4nagios/custom/rrd_add_datasource-1.1.tar.gz -C /usr/local/pnp4nagios/custom && echo "rrd_add_datasource Script Extracted Successfully."
rm -rf /usr/local/pnp4nagios/custom/rrd_add_datasource-1.1.tar.gz && echo "Cleaning up rrd_add_datasource Tarball."



# Renaming the 'install.php' file located in /usr/local/pnp4nagios/share.
mv /usr/local/pnp4nagios/share/install.php /usr/local/pnp4nagios/share/install_completed.php

# Post Installation Check
INSTALLCHECK=$(ls /usr/local/pnp4nagios/bin | wc -l)
if [ $INSTALLCHECK -eq "0" ]; then
        /bin/echo -e "\nmon-pnp4nagios DID NOT Install Successfully! Files are missing in '/usr/local/pnp4nagios/bin'\n"
else
        /bin/echo -e "\nmon-pnp4nagios has been Successfully Installed!\n"
fi


%postun
# Post Removal Check
if [ $1 -eq 0 ]; then
        /bin/echo -e "\nmon-pnp4nagios has been Successfully Removed!\n"
fi


%files
%defattr(-,howler,howler,-)
/usr/local/pnp4nagios
/etc/httpd/conf.d/pnp4nagios.conf
/etc/rc.d/init.d/npcd
/etc/rc.d/init.d/pnp_gearman_worker


%changelog
* Fri May 25 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Condensed the section where files are copied over to the Report Templates directory by creating a new folder 
  to store all of the custom PHP Template Files called 'graph_templates'. This new folder resides within the
 'monconfigs' directory.

* Thu May 24 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Added the 'rrd_add_datasource' file to be added to the '/usr/local/pnp4nagios/custom' directory.

* Mon May 21 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Added the following PHP File entries to be added to the Report Templates directory:
  'icinga_check_port.php'
  'icinga_check_ps_service.php'
  'icinga_check_service.php'
  'icinga_check_swap_percent.php'
  'check_local_users.php'

* Mon Apr 29 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Started a Fresh and New PNP4Nagios Spec File.




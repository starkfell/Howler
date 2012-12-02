###################################################################################
#                                                                                 #
# Title:     mon-icinga - [1.8.1] - SPEC File                                     #
# Author:    Ryan Irujo                                                           #
# Inception: 11.06.12                                                             #
#                                                                                 #
# Purpose:   Modified the original Icinga SPEC File for RPM builds so that        #
#            Administrators, other than Rocket Scientists, could manage it.       #
#                                                                                 #
#            Change Log is located at the bottom of the file.                     #
#                                                                                 #
###################################################################################

%define logdir %{_localstatedir}/log/icinga

%define apacheconfdir  %{_sysconfdir}/httpd/conf.d
%define apacheuser apache

Summary: Open Source host, service and network monitoring program
Name:    mon-icinga
Version: 1.8.1
Release: 1.0
License: GPLv2
Group: Applications/System
URL: http://www.icinga.org/

Source0: %{name}-%{version}-%{release}.tar.gz
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root

BuildRequires: gcc
BuildRequires: gd-devel > 1.8
BuildRequires: httpd
BuildRequires: zlib-devel
BuildRequires: libpng-devel
BuildRequires: libjpeg-devel
BuildRequires: libdbi-devel
BuildRequires: perl(ExtUtils::Embed)

%description
Icinga is an application, system and network monitoring application.
It can escalate problems by email, pager or any other medium. It is
also useful for incident or SLA reporting.

Icinga is written in C and is designed as a background process,
intermittently running checks on various services that you specify.

The actual service checks are performed by separate "plugin" programs
which return the status of the checks to Icinga.

Icinga is a fork of the nagios project.

%package gui
Summary: Web content for %{name}
Group: Applications/System
Requires: %{name} = %{version}-%{release}
Requires: httpd
Requires: %{name}-doc

%description gui
This package contains the webgui (html,css,cgi etc.) for %{name}


%package idoutils-libdbi-mysql
Summary: database broker module for %{name}
Group: Applications/System
Requires: %{name} = %{version}-%{release}
Requires: libdbi-dbd-mysql
Conflicts: %{name}-idoutils-libdbi-pgsql

%description idoutils-libdbi-mysql
This package contains the idoutils broker module for %{name} which provides
database storage via libdbi.


%package doc
Summary: documentation %{name}
Group: Documentation

%description doc
Documentation for %{name}


%prep
%setup -qn %{name}-%{version}


%build
%configure \
    --prefix="/usr/local/icinga" \
    --datarootdir="/usr/local/icinga/share" \
    --libexecdir="/usr/local/icinga/libexec" \
    --localstatedir="/usr/local/icinga/var" \
    --with-checkresult-dir="/usr/local/icinga/var/spool/checkresults" \
    --sbindir="/usr/local/icinga/sbin" \
    --sysconfdir="/usr/local/icinga/etc" \
    --with-cgiurl="/icinga/cgi-bin" \
    --with-command-user="howler" \
    --with-command-group="howler" \
    --with-htmurl="/icinga" \
    --with-init-dir="/etc/init.d" \
    --with-mail="/bin/mail" \
    --with-icinga-user="howler" \
    --with-icinga-group="howler" \
    --with-template-objects \
    --with-template-extinfo \
    --enable-event-broker \
    --enable-embedded-perl \
    --enable-idoutils \
    --enable-cgi-log \
   --with-cgi-log-dir="/usr/local/icinga/share/log" \
    --with-httpd-conf=%{apacheconfdir} \
    --with-p1-file-dir="/usr/local/icinga/bin"
%{__make} %{?_smp_mflags} all


%install
%{__rm} -rf %{buildroot}
%{__mkdir} -p %{buildroot}/%{apacheconfdir}
%{__make} install-unstripped \
    install-init \
    install-commandmode \
    install-config \
    install-webconf \
    install-idoutils \
    DESTDIR="%{buildroot}" \
    INSTALL_OPTS="" \
    INSTALL_OPTS_WEB="" \
    COMMAND_OPTS="" \
    INIT_OPTS=""


### strip binaries
%{__strip} %{buildroot}/usr/bin/{icinga,icingastats,log2ido,ido2db}
%{__strip} %{buildroot}/usr/local/icinga/sbin/*.cgi

# ----- MODIFICATION ----- Moving icinga,icingastats,idomod.o,log2ido, and ido2db to /usr/local/icinga/bin
mv %{buildroot}/usr/bin/icinga %{buildroot}/usr/local/icinga/bin/icinga
mv %{buildroot}/usr/bin/icingastats %{buildroot}/usr/local/icinga/bin/icingastats
#mv %{buildroot}/usr/bin/idomod.so %{buildroot}/usr/local/icinga/bin/idomod.so
mv %{buildroot}/usr/lib64/idomod.so %{buildroot}/usr/local/icinga/bin/idomod.so
mv %{buildroot}/usr/bin/log2ido %{buildroot}/usr/local/icinga/bin/log2ido
mv %{buildroot}/usr/bin/ido2db %{buildroot}/usr/local/icinga/bin/ido2db

# ----- MODIFICATION ----- Renaming the resource.cfg file to resources.cfg
mv %{buildroot}/usr/local/icinga/etc/resource.cfg %{buildroot}/usr/local/icinga/etc/resources.cfg

### move idoutils sample configs to final name
mv %{buildroot}/usr/local/icinga/etc/ido2db.cfg-sample %{buildroot}/usr/local/icinga/etc/ido2db.cfg
mv %{buildroot}/usr/local/icinga/etc/idomod.cfg-sample %{buildroot}/usr/local/icinga/etc/idomod.cfg
mv %{buildroot}/usr/local/icinga/etc/modules/idoutils.cfg-sample %{buildroot}/usr/local/icinga/etc/idoutils.cfg


### copy idoutils db-script
cp -r module/idoutils/db %{buildroot}/usr/local/icinga/bin/idoutils

############## START! - Customized Changes to the Icinga Build ######################################
#
# Any and All Customized Files for the Icinga Build are inserted in this section.
#
#####################################################################################################


# Inserting the custom Icinga 'init.d' Script file. 
cp monconfigs/icinga %{buildroot}/etc/init.d/icinga

# Inserting an empty Icinga Log file so permissions can be set to the Service Icinga Account after Install.
cp monconfigs/icinga.log %{buildroot}/usr/local/icinga/var/icinga.log

# Inserting the custom Icinga 'init.d' Script file and base 'icinga.cfg' files.
cp monconfigs/ido2db %{buildroot}/etc/init.d/ido2db

# Inserting the custom Icinga Configuration 'icinga.cfg' file.
cp monconfigs/icinga.cfg %{buildroot}/usr/local/icinga/etc/icinga.cfg

# Inserting the customized IDOUTILS Files
cp monconfigs/ido2db.cfg %{buildroot}/usr/local/icinga/etc/ido2db.cfg
cp monconfigs/idomod.cfg %{buildroot}/usr/local/icinga/etc/idomod.cfg

# Inserting the monserver-plugins Tarball. The plugins are required to be in a tarball and extracted
# during the '%post' process as some of the plugins are compiled.
cp monconfigs/monserver-plugins.tar.gz %{buildroot}/usr/local/icinga

# Adding the Custom Notifications Scripts to the Icinga 'libexec' directory.
cp monconfigs/icinga-notify-host-by-email-html %{buildroot}/usr/local/icinga/libexec
cp monconfigs/icinga-notify-service-by-email-html %{buildroot}/usr/local/icinga/libexec

# Adding in default 'htpasswd.users' file to allow access to the Icinga GUI Interface.
cp monconfigs/htpasswd.users %{buildroot}/usr/local/icinga/etc

# Adding 'index.html' to redirect traffic to the Icinga GUI Interface.
cp monconfigs/index.html %{buildroot}/usr/local/icinga/etc

# Adding the 'sidebar.html' file to the Icinga GUI Interface.
cp monconfigs/sidebar.html %{buildroot}/usr/local/icinga/share

# Adding the 'custom-menu.html' file to the Icinga GUI Interface.
cp monconfigs/custom-menu.html %{buildroot}/usr/local/icinga/share

# Adding custom 'icinga.conf' file to Apache to grant access to the 'icinga-cgi.log' file(s).
cp monconfigs/icinga.conf %{buildroot}/etc/httpd/conf.d/icinga.conf

# Copying over 'status-header.ssi' file. This enables mouse-over reports in the Icinga Core GUI.
if [ -f /usr/local/icinga/share/ssi/status-header.ssi ]; then
	cp monconfigs/status-header.ssi %{buildroot}/usr/local/icinga/share/ssi
else
       echo "status-header.ssi file already exists in the /usr/local/icinga/share/ssi/ directory."
fi


############# END! - Customized Changes to the Icinga Build #########################################


%pre
# Add icinga user
/usr/sbin/groupadd icinga 2> /dev/null || :
/usr/sbin/groupadd icingacmd 2> /dev/null || :
/usr/sbin/useradd -c "howler" -s /sbin/nologin -r -d /var/icinga -G icingacmd -g icinga icinga 2> /dev/null || :

%post
/sbin/chkconfig --add icinga

echo "Extracting monserver-plugins Tarball."
tar -xzf /usr/local/icinga/monserver-plugins.tar.gz -C /usr/local/icinga
cp /usr/local/icinga/monserver-plugins/* /usr/local/icinga/libexec && echo "monserver-plugins added."
rm -rf /usr/local/icinga/monserver-plugins/ && echo "monserver-plugins Extract Directory Removed."
/bin/chown howler.howler -R /usr/local/icinga/libexec && echo "Icinga Service Account Permissions added to libexec directory."
/bin/chown howler.howler /usr/local/icinga/var/icinga.log && echo "Icinga Service Account Permission added to icinga.log file."
rm -rf /usr/local/icinga/monserver-plugins.tar.gz && echo "monserver-plugins Tarball Removed."
mv /usr/local/icinga/etc/index.html /var/www/html && echo "Icinga Redirect Page added to Apache Web Root Directory"

if [ -f /usr/lib64/nagios/plugins/check_nrpe ]; then
	cp /usr/lib64/nagios/plugins/check_nrpe /usr/local/icinga/libexec/check_nrpe &&
	echo "Custom 'check_nrpe' Plugin Successfully copied to '/usr/local/icinga/libexec'"
else
	echo "Custom 'check_nrpe' Plugin was not found in '/usr/lib64/nagios/plugins' directory."
	echo "'check_nrpe' Plugin v2.12  in '/usr/local/icinga/libexec' will be used instead."
	echo ""
fi

# Importing IDOUTILS Tables into the Icinga Database if it isn't already installed.
echo "Checking to see if the Icinga Database exists."
if [ -d /var/lib/mysql/icinga ]; then
        echo "Icinga Database Found. Checking for preexisting IDOUTILS Tables."
        echo "Attempting to Connect to Icinga Database"

        # Attempting to Connect to the Icinga Database.
        CONNECT=$(mysql -u howler -phowler -D icinga -e "exit" 2>&1 )
        FAIL="ERROR"
        if [[ $CONNECT =~ $FAIL ]]; then
                echo "Failed to Connect to Icinga Database. Invalid Credentials!"
                echo "The IDOUtils Tables will have to be imported manually using the documentation at https://github.com/starkfell/Howler"
        else
                echo "Successfully Connected to Icinga Database."
                echo "Searching for Tables in the Icinga Database"

                # Looking for preexisting Tables in the Icinga Database.
                QUERY=$(mysql -u howler -phowler -D icinga -e "show tables" | wc -l )
                if [[ $QUERY -eq "0" ]];then
                        echo "Installing IDOUTILS Tables into the Icinga Database."
                        mysql -u howler -phowler icinga < /usr/local/icinga/bin/idoutils/mysql/mysql.sql &&
                        echo "The IDOUTILS Tables have been successfully imported into the Icinga Database."
                elif [[ $QUERY -gt "0" ]];then
                        echo "There are existing IDOUTILS Tables in the Icinga Database. Skipping IDOUTILS Tables install."
                fi
        fi
else
        echo "The Icinga Database needs to be created before IDOUtils can be installed."
        echo "The IDOUtils Tables will have to be imported manually using the documentation at https://github.com/starkfell/Howler";
fi


echo ""
echo "%{name}-%{version}-%{release} successfully installed."
echo ""


%preun
if [ $1 -eq 0 ]; then
    /sbin/service icinga stop &>/dev/null || :
    /sbin/chkconfig --del icinga
fi


%post doc
echo ""
echo "%{name}-doc-%{version}-%{release} successfully installed."
echo ""


%pre gui
# Add Apache User to the icingacmd group
  /usr/sbin/usermod -a -G howler %{apacheuser}

%post gui
cp /usr/local/icinga/share/custom-menu.html /usr/local/icinga/share/menu.html && echo "custom 'menu.html' file added to Icinga GUI."
rm -rf /usr/local/icinga/share/log/.htaccess && echo "Removed '.htaccess' file from the Icinga CGI Log Directory."
echo ""
echo "%{name}-gui-%{version}-%{release} successfully installed."
echo ""


%post idoutils-libdbi-mysql
/sbin/chkconfig --add ido2db

# delete old bindir/idomod.o if it exists
if [ -f %{_bindir}/idomod.o ]
then
    rm -f %{_bindir}/idomod.o
fi

echo ""
echo "%{name}-idoutils-libdbi-mysql-%{version}-%{release} successfully installed."
echo ""

%preun idoutils-libdbi-mysql
if [ $1 -eq 0 ]; then
    /sbin/service ido2db stop &>/dev/null || :
    /sbin/chkconfig --del ido2db
fi


%clean
%{__rm} -rf %{buildroot}


%files
%defattr(-,howler,howler,-)
%attr(755,howler,howler) /etc/init.d/icinga
%dir /usr/local/icinga
/usr/local/icinga
%config(noreplace) /usr/local/icinga/etc/icinga.cfg
%dir /usr/local/icinga/etc
%config(noreplace) /usr/local/icinga/etc/objects/commands.cfg
%config(noreplace) /usr/local/icinga/etc/objects/contacts.cfg
%config(noreplace) /usr/local/icinga/etc/objects/notifications.cfg
%config(noreplace) /usr/local/icinga/etc/objects/localhost.cfg
%config(noreplace) /usr/local/icinga/etc/objects/printer.cfg
%config(noreplace) /usr/local/icinga/etc/objects/switch.cfg
%config(noreplace) /usr/local/icinga/etc/objects/templates.cfg
%config(noreplace) /usr/local/icinga/etc/objects/timeperiods.cfg
%config(noreplace) /usr/local/icinga/etc/objects/windows.cfg
%config(noreplace) /usr/local/icinga/etc/resources.cfg
/usr/local/icinga/bin/icinga
/usr/local/icinga/bin/icingastats
/usr/local/icinga/bin/p1.pl
/usr/local/icinga/sbin/trends.cgi
/usr/local/icinga/sbin/avail.cgi
/usr/local/icinga/sbin/cmd.cgi
/usr/local/icinga/sbin/config.cgi
/usr/local/icinga/sbin/extinfo.cgi
/usr/local/icinga/sbin/histogram.cgi
/usr/local/icinga/sbin/history.cgi
/usr/local/icinga/sbin/notifications.cgi
/usr/local/icinga/sbin/outages.cgi
/usr/local/icinga/sbin/showlog.cgi
/usr/local/icinga/sbin/status.cgi
/usr/local/icinga/sbin/statusmap.cgi
#/usr/local/icinga/sbin/statuswml.cgi
#/usr/local/icinga/sbin/statuswrl.cgi
/usr/local/icinga/sbin/summary.cgi
/usr/local/icinga/sbin/tac.cgi
%dir /usr/local/icinga/var/spool/checkresults
/usr/local/icinga/libexec
%attr(2755,howler,howler) /usr/local/icinga/var/rw/
/usr/local/icinga/var
/usr/local/icinga/var/archives


%files doc
%defattr(-,howler,howler,-)
/usr/local/icinga/share/docs


%files gui
%defattr(-,howler,howler,-)
%config(noreplace) %attr(-,howler,howler) %{apacheconfdir}/icinga.conf
%config(noreplace) /usr/local/icinga/etc/cgi.cfg
%config(noreplace) /usr/local/icinga/etc/cgiauth.cfg
/usr/local/icinga/share
%dir /usr/local/icinga/share
#/usr/local/icinga/share/contexthelp
/usr/local/icinga/share/images
/usr/local/icinga/share/index.html
/usr/local/icinga/share/js
/usr/local/icinga/share/main.html
/usr/local/icinga/share/media
/usr/local/icinga/share/menu.html
/usr/local/icinga/share/robots.txt
/usr/local/icinga/share/sidebar.html
/usr/local/icinga/share/ssi
/usr/local/icinga/share/stylesheets


%files idoutils-libdbi-mysql
%defattr(-,howler,howler,-)
%attr(755,howler,howler) /etc/init.d/ido2db
%config(noreplace) /usr/local/icinga/etc/ido2db.cfg
%config(noreplace) /usr/local/icinga/etc/idomod.cfg
%config(noreplace) /usr/local/icinga/etc/idoutils.cfg
/usr/local/icinga/bin/idoutils
/usr/local/icinga/bin/ido2db
/usr/local/icinga/bin/log2ido
/usr/local/icinga/bin/idomod.so


%changelog

* Mon May 07 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Inception of Customized Icinga SPEC File. Previous changes to the SPEC File
  are included in the 'README_MON_CHANGES' file.



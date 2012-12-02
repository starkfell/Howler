#########################################################################################
# Title:     [mon-rrdtool] - SPEC File						  	#
# Author:    Ryan Irujo								  	#
#										  	#
# Purpose:   Created a New SPEC File for RPM builds so that 				#
#            Administrators, other Rocket Scientists, could manage it. 			#
#											#
#            Change Log is located at the bottom of the file. 				#
#											#
#########################################################################################

Summary:       Round Robin Database Tool to store and display time-series data
Name:          mon-rrdtool
Version:       1.4.7
Release:       1.1
License:       GPLv2+ with exceptions
Group:         Applications/Databases
URL:           http://oss.oetiker.ch/rrdtool/
Source0:       %{name}-%{version}-%{release}.tar.gz
BuildRoot:     %{_tmppath}/%{name}-%{version}-%{release}-root
Requires:      dejavu-lgc-fonts
BuildRequires: gcc-c++, openssl-devel, freetype-devel
BuildRequires: libpng-devel, zlib-devel, intltool >= 0.35.0
BuildRequires: cairo-devel >= 1.2, pango-devel >= 1.14
BuildRequires: libtool, groff
BuildRequires: gettext, libxml2-devel


%description
RRD is the Acronym for Round Robin Database. RRD is a system to store and
display time-series data (i.e. network bandwidth, machine-room temperature,
server load average). It stores the data in a very compact way that will not
expand over time, and it presents useful graphs by processing the data to
enforce a certain data density. It can be used either via simple wrapper
scripts (from shell or Perl) or via frontends that poll network devices and
put a friendly user interface on it.


%prep
%setup -qn %{name}-%{version}


%build
%configure \
	--prefix="/usr/local/rrdtool" \
%{__make} %{?_smp_mflags} all


%install
%{__rm} -rf %{buildroot}
%{__mkdir} -p %{buildroot}
%{__make} install \
DESTDIR="%{buildroot}"

install -d -m 0755 %{buildroot}/usr/local/rrdtool/lib
install -d -m 0755 %{buildroot}/usr/local/rrdtool/lib/pkgconfig
install -d -m 0755 %{buildroot}/usr/local/rrdtool/include
install -d -m 0755 %{buildroot}/usr/local/rrdtool/bin
install -d -m 0755 %{buildroot}/usr/local/rrdtool/share/doc/rrdtool-1.4.7/html
install -d -m 0755 %{buildroot}/usr/local/rrdtool/share/doc/rrdtool-1.4.7/txt
install -d -m 0755 %{buildroot}/usr/local/rrdtool/share/man/man1
install -d -m 0755 %{buildroot}/usr/local/rrdtool/share/man/man3
install -d -m 0755 %{buildroot}/usr/local/rrdtool/share/rrdtool/examples


# Moving all RRDTool Library Files under /usr/local/rrdtool
mv %{buildroot}/usr/lib64/librrd* %{buildroot}/usr/local/rrdtool/lib
mv %{buildroot}/usr/lib64/pkgconfig/librrd.pc %{buildroot}/usr/local/rrdtool/lib/pkgconfig/librrd.pc
mv %{buildroot}/usr/include/* %{buildroot}/usr/local/rrdtool/include
mv %{buildroot}/usr/bin/* %{buildroot}/usr/local/rrdtool/bin
mv %{buildroot}/usr/share/doc/rrdtool-1.4.7/html/* %{buildroot}/usr/local/rrdtool/share/doc/rrdtool-1.4.7/html
mv %{buildroot}/usr/share/doc/rrdtool-1.4.7/txt/* %{buildroot}/usr/local/rrdtool/share/doc/rrdtool-1.4.7/txt
mv %{buildroot}/usr/share/man/man1/* %{buildroot}/usr/local/rrdtool/share/man/man1
mv %{buildroot}/usr/share/man/man3/* %{buildroot}/usr/local/rrdtool/share/man/man3
mv %{buildroot}/usr/share/rrdtool/examples/* %{buildroot}/usr/local/rrdtool/share/rrdtool/examples


%clean
%{__rm} -rf %{buildroot}


%post
# Copying over 'librrd' to the '/usr/lib64' directory as the RRDTool Binaries look in that location by default.
cp /usr/local/rrdtool/lib/librrd* /usr/lib64 && /bin/echo -e "\n'librrd' Files Successfully Copied to /usr/lib64'\n"

# Post Installation Check
RRDTOOL_CHECK=$(ls /usr/local/rrdtool/bin | wc -l)
if [ $RRDTOOL_CHECK -eq 0 ]; then
        /bin/echo -e "\nmon-rrdtool DID NOT Install Successfully! Files are missing in '/usr/local/rrdtool/bin'\n"
else
        /bin/echo -e "\nmon-rrdtool has been Successfully Installed!\n"
fi


%postun
# Removing 'librrd' files from the '/usr/lib64' directory.
rm -f /usr/lib64/librrd.a && /bin/echo -e "\n'librrd.a' Successfully Removed from /usr/lib64"
rm -f /usr/lib64/librrd.la && /bin/echo "'librrd.la' Successfully Removed from /usr/lib64"
rm -f /usr/lib64/librrd.so.4 && /bin/echo "'librrd.so.4' Successfully Removed from /usr/lib64"
rm -f /usr/lib64/librrd.so.4.2.0 && /bin/echo "'librrd.so.4.2.0' Successfully Removed from /usr/lib64"
rm -f /usr/lib64/librrd_th.a && /bin/echo "'librrd_th.a' Files Successfully Removed from /usr/lib64"
rm -f /usr/lib64/librrd_th.la && /bin/echo "'librrd_th.la' Successfully Removed from /usr/lib64/"
rm -f /usr/lib64/librrd_th.so.4 && /bin/echo "'librrd_th.so.4' Files Successfully Removed from /usr/lib64"
rm -f /usr/lib64/librrd_th.so.4.2.0 && /bin/echo -e "'librrd_th.so.4.2.0' Successfully Removed from /usr/lib64/n"


# Post Removal Check
if [ $1 -eq 0 ]; then
	/bin/echo -e "\nmon-rrdtool has been Successfully Removed!\n"
fi


%files
%defattr(-,howler,howler,-)
/usr/local/rrdtool
/usr/lib64/python2.4/site-packages/rrdtoolmodule.so

%changelog
* Wed May 02 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Created a section to add 'librrd' files in the '/usr/local/rrdtool/bin' directory  to the '/usr/lib64'
  directory after the base install because the RRDTool Binaries look in the '/usr/lib64' directory by
  default.

* Tue May 01 2012 Ryan Irujo <ryan.irujo@gmail.com>
- All RRDTool related files have been configured to install under '/usr/local/rrdtool/'.
  Exception: 'rrdtoolmodule.so' is set to be installed under '/usr/lib64/python2.4/site-packages/'

* Mon Apr 29 2012 Ryan Irujo <ryan.irujo@gmail.com>
- Started a Fresh and New RRDTool Spec File as the original one was too complicated to maintain.





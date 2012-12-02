###############################
# Makefile for Nagios
#
# Last Modified: 05-30-2006
###############################


# Source code directories
SRC_BASE=./base
SRC_CGI=./cgi
SRC_HTM=./html
SRC_MODULE=./module
SRC_INCLUDE=./include
SRC_COMMON=./common
SRC_XDATA=./xdata
SRC_CONTRIB=./contrib

CC=gcc
CFLAGS=-g -O2 -DHAVE_CONFIG_H
LDFLAGS= 

prefix=/usr/local/opmon
exec_prefix=${prefix}
LOGDIR=${prefix}/var
CFGDIR=${prefix}/etc
BINDIR=${exec_prefix}/bin
CGIDIR=${exec_prefix}/sbin
HTMLDIR=${prefix}/share
INSTALL=/usr/bin/install -c
INSTALL_OPTS=-o opuser -g opuser
COMMAND_OPTS=-o opuser -g apache
INIT_DIR=/etc/rc.d/init.d
INIT_OPTS=-o root -g root
CGICFGDIR=$(CGIDIR)
PERLDIR=

INSTALLPERLSTUFF=no

CGIEXTRAS= statuswrl.cgi statusmap.cgi trends.cgi histogram.cgi

SNPRINTF_O=

CP=@CP@

install:
	$(INSTALL) -m 775 $(INSTALL_OPTS) -d $(DESTDIR)$(prefix)
	$(INSTALL) -m 775 $(INSTALL_OPTS) -d $(DESTDIR)$(prefix)/libexec/templates
	$(INSTALL) -m 775 $(INSTALL_OPTS) -d $(DESTDIR)$(prefix)/db
	$(INSTALL) -m 775 $(INSTALL_OPTS) -d $(DESTDIR)$(prefix)/share/opcfg
	/bin/cp -fpr * $(DESTDIR)$(prefix)/share/opcfg
	/bin/cp -fp sqldata/opcfg.sql $(DESTDIR)$(prefix)/db/
	/bin/cp -fp sqldata/opcfg.sql.start $(DESTDIR)$(prefix)/db/
	/bin/chown -R opuser:apache $(DESTDIR)$(prefix)/share/opcfg
	/bin/rm -f $(DESTDIR)$(prefix)/share/opcfg/Makefile
	/bin/rm -f $(DESTDIR)$(prefix)/share/opcfg/*.spec
	/bin/rm -fr `/usr/bin/find $(DESTDIR)$(prefix)/share/opcfg/ -name "CVS"`
	

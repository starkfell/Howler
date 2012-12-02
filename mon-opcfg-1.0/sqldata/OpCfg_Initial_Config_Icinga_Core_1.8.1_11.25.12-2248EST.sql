-- MySQL dump 10.11
--
-- Host: localhost    Database: opcfg
-- ------------------------------------------------------
-- Server version	5.0.95

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `changes`
--

DROP TABLE IF EXISTS `changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changes` (
  `type` varchar(10) NOT NULL default '',
  `to_change` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changes`
--

LOCK TABLES `changes` WRITE;
/*!40000 ALTER TABLE `changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dbauth_users`
--

DROP TABLE IF EXISTS `dbauth_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbauth_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `password` text NOT NULL,
  `firstname` text,
  `lastname` text,
  `initials` varchar(5) default NULL,
  `displayname` text,
  `email` text NOT NULL,
  `pager` text,
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbauth_users`
--

LOCK TABLES `dbauth_users` WRITE;
/*!40000 ALTER TABLE `dbauth_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `dbauth_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `query` text NOT NULL,
  `ctime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10976 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_broker_modules`
--

DROP TABLE IF EXISTS `nagios_broker_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_broker_modules` (
  `module_id` int(11) NOT NULL auto_increment,
  `module_line` text NOT NULL,
  PRIMARY KEY  (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_broker_modules`
--

LOCK TABLES `nagios_broker_modules` WRITE;
/*!40000 ALTER TABLE `nagios_broker_modules` DISABLE KEYS */;
INSERT INTO `nagios_broker_modules` VALUES (2,'/usr/local/icinga/bin/idomod.so config_file=/usr/local/icinga/etc/idomod.cfg');
/*!40000 ALTER TABLE `nagios_broker_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_cgi`
--

DROP TABLE IF EXISTS `nagios_cgi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_cgi` (
  `physical_html_path` varchar(255) default NULL,
  `url_html_path` varchar(255) default NULL,
  `use_authentication` enum('0','1') default NULL,
  `use_ssl_authentication` int(20) default '0',
  `default_user_name` varchar(255) default NULL,
  `authorized_for_full_command_resolution` varchar(255) default 'icingaadmin',
  `authorized_for_system_information` varchar(255) default NULL,
  `authorized_for_system_commands` varchar(255) default NULL,
  `authorized_for_configuration_information` varchar(255) default NULL,
  `authorized_for_all_hosts` varchar(255) default NULL,
  `authorized_for_all_host_commands` varchar(255) default NULL,
  `authorized_for_all_services` varchar(255) default NULL,
  `authorized_for_all_service_commands` varchar(255) default NULL,
  `authorized_for_read_only` varchar(255) default NULL,
  `authorized_contactgroup_for_read_only` varchar(255) default NULL,
  `authorized_for_comments_read_only` varchar(255) default NULL,
  `authorized_contactgroup_for_comments_read_only` varchar(255) default NULL,
  `authorized_for_downtimes_read_only` varchar(255) default NULL,
  `authorized_contactgroup_for_downtimes_read_only` varchar(255) default NULL,
  `statusmap_background_image` varchar(255) default NULL,
  `default_statusmap_layout` enum('0','1','2','3','4','5','6') default NULL,
  `statuswrl_include` varchar(255) default NULL,
  `default_statuswrl_layout` enum('0','1','2','3','4') default NULL,
  `refresh_rate` int(5) default NULL,
  `default_expiring_disabled_notifications_duration` int(20) default '86400',
  `lock_author_names` enum('0','1') default NULL,
  `action_url_target` varchar(255) default NULL,
  `notes_url_target` varchar(255) default NULL,
  `use_pending_states` enum('0','1') default NULL,
  `host_unreachable_sound` varchar(255) default NULL,
  `host_down_sound` varchar(255) default NULL,
  `service_critical_sound` varchar(255) default NULL,
  `service_warning_sound` varchar(255) default NULL,
  `service_unknown_sound` varchar(255) default NULL,
  `normal_sound` varchar(255) default NULL,
  `nagios_check_command` varchar(255) default NULL,
  `show_context_help` enum('0','1') default NULL,
  `escape_html_tags` enum('0','1') default NULL,
  `ping_syntax` varchar(255) default NULL,
  `show_all_services_host_is_authorized_for` int(20) default '1',
  `status_show_long_plugin_output` int(20) default '0',
  `color_transparency_index_r` int(20) default '255',
  `color_transparency_index_g` int(20) default '255',
  `color_transparency_index_b` int(20) default '255',
  `refresh_type` int(20) default '1',
  `tac_show_only_hard_state` int(20) default '0',
  `enable_splunk_integration` int(20) default '0',
  `splunk_url` varchar(255) default NULL,
  `persistent_ack_comments` int(20) default '0',
  `csv_delimiter` varchar(255) default ';',
  `csv_data_enclosure` varchar(255) default '''',
  `showlog_initial_states` int(20) default '0',
  `showlog_current_states` int(20) default '0',
  `tab_friendly_titles` int(20) default '1',
  `add_notif_num_hard` int(20) default '0',
  `add_notif_num_soft` int(20) default '0',
  `http_charset` varchar(255) default 'utf-8',
  `first_day_of_week` int(20) default '0',
  `use_logging` int(20) default '1',
  `cgi_log_file` varchar(255) default '/usr/local/icinga/share/log/icinga-cgi.log',
  `cgi_log_rotation_method` varchar(255) default 'd',
  `cgi_log_archive_path` varchar(255) default '/usr/local/icinga/share/log',
  `enforce_comments_on_actions` int(20) default '1',
  `show_tac_header` int(20) default '1',
  `show_tac_header_pending` int(20) default '0',
  `default_downtime_duration` int(20) default '7200',
  `suppress_maintenance_downtime` int(20) default '1',
  `show_partial_hostgroups` int(20) default '0',
  `highlight_table_rows` int(20) default '0',
  `default_expiring_acknowledgement_duration` int(20) default '86400',
  `extinfo_show_child_hosts` int(20) default '0',
  `display_status_totals` int(20) default '0',
  `result_limit` int(20) default '50',
  `lowercase_user_name` int(20) default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_cgi`
--

LOCK TABLES `nagios_cgi` WRITE;
/*!40000 ALTER TABLE `nagios_cgi` DISABLE KEYS */;
INSERT INTO `nagios_cgi` VALUES ('/usr/local/icinga/share','/icinga','1',0,'guest','icingaadmin','icingaadmin','icingaadmin','icingaadmin','icingaadmin','icingaadmin','icingaadmin','icingaadmin',NULL,NULL,NULL,NULL,NULL,NULL,'smbackground.gd2','5','myworld.wrl','4',30,86400,'1','_blank','_blank','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0','0',NULL,1,0,255,255,255,1,0,0,NULL,0,';','\\\'',0,0,1,0,0,'utf-8',0,1,'/usr/local/icinga/share/log/icinga-cgi.log','d','/usr/local/icinga/share/log',1,1,0,7200,1,0,0,86400,0,0,500,1);
/*!40000 ALTER TABLE `nagios_cgi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_cgi_desc`
--

DROP TABLE IF EXISTS `nagios_cgi_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_cgi_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_cgi_desc`
--

LOCK TABLES `nagios_cgi_desc` WRITE;
/*!40000 ALTER TABLE `nagios_cgi_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_cgi_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_commands`
--

DROP TABLE IF EXISTS `nagios_commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_commands` (
  `command_id` int(11) unsigned NOT NULL auto_increment,
  `network_id` int(11) unsigned NOT NULL default '0',
  `command_name` text NOT NULL,
  `command_line` text NOT NULL,
  `command_desc` text,
  PRIMARY KEY  (`command_id`),
  KEY `name` (`command_name`(128))
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1 COMMENT='Nagios Commands';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_commands`
--

LOCK TABLES `nagios_commands` WRITE;
/*!40000 ALTER TABLE `nagios_commands` DISABLE KEYS */;
INSERT INTO `nagios_commands` VALUES (1,0,'notify-host-by-email','/usr/bin/printf \\\"%b\\\" \\\"***** Icinga - [Bedford] - QA Windows Environment*****\\\\n\\\\nNotification Type: $NOTIFICATIONTYPE$\\\\nHost: $HOSTNAME$\\\\nState: $HOSTSTATE$\\\\nAddress: $HOSTADDRESS$\\\\nInfo: $HOSTOUTPUT$\\\\n\\\\nDate/Time: $LONGDATETIME$\\\\n\\\" | /bin/mail -s \\\"** $NOTIFICATIONTYPE$ Host Alert: $HOSTNAME$ is $HOSTSTATE$ **\\\" $CONTACTEMAIL$','Built-in Nagios/Icinga command to send out Host Event Notifications via E-mail'),(2,0,'notify-service-by-email','/usr/bin/printf \\\"%b\\\" \\\"***** Icinga - [Bedford] - QA Windows Environment*****\\\\n\\\\nNotification Type: $NOTIFICATIONTYPE$\\\\n\\\\nService: $SERVICEDESC$\\\\nHost: $HOSTALIAS$\\\\nAddress: $HOSTADDRESS$\\\\nState: $SERVICESTATE$\\\\n\\\\nDate/Time: $LONGDATETIME$\\\\n\\\\nStandard Information:\\\\n\\\\n$SERVICEOUTPUT$\\\\n\\\\n\\\\nDetailed Information:\\\\n\\\\n$LONGSERVICEOUTPUT$\\\\n\\\" | /bin/mail -s \\\"** $NOTIFICATIONTYPE$ Service Alert: $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$ **\\\" $CONTACTEMAIL$','Built-in Nagios/Icinga command to send out Service Event Notifications via E-mail'),(3,0,'check-host-alive','$USER1$/check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 5',NULL),(4,0,'check_disk','$USER1$/check_disk -w $ARG1$ -c $ARG2$ -p $ARG3$',NULL),(5,0,'check_load','$USER1$/check_load -w $ARG1$ -c $ARG2$',NULL),(6,0,'check_procs','$USER1$/check_procs -w $ARG1$ -c $ARG2$ -s $ARG3$',NULL),(7,0,'check_users','$USER1$/check_users -w $ARG1$ -c $ARG2$',NULL),(8,0,'check_swap','$USER1$/check_swap -w $ARG1$ -c $ARG2$',NULL),(9,0,'check_mrtgtraf','$USER1$/check_mrtgtraf -F $ARG1$ -a $ARG2$ -w $ARG3$ -c $ARG4$ -e $ARG5$',NULL),(10,0,'check_ftp','$USER1$/check_ftp -H $HOSTADDRESS$ $ARG1$',NULL),(12,0,'check_snmp','$USER1$/check_snmp -H $HOSTADDRESS$ $ARG1$',NULL),(13,0,'check_http','$USER1$/check_http -I $HOSTADDRESS$ $ARG1$',NULL),(14,0,'check_ssh','$USER1$/check_ssh $ARG1$ $HOSTADDRESS$',NULL),(15,0,'check_dhcp','$USER1$/check_dhcp $ARG1$',NULL),(16,0,'check_ping','$USER1$/check_ping -H $HOSTADDRESS$ -w $ARG1$ -c $ARG2$ -p 5',NULL),(17,0,'check_pop','$USER1$/check_pop -H $HOSTADDRESS$ $ARG1$',NULL),(18,0,'check_imap','$USER1$/check_imap -H $HOSTADDRESS$ $ARG1$',NULL),(19,0,'check_smtp','$USER1$/check_smtp -H $HOSTADDRESS$ $ARG1$',NULL),(20,0,'check_tcp','$USER1$/check_tcp -H $HOSTADDRESS$ -p $ARG1$ $ARG2$',NULL),(21,0,'check_udp','$USER1$/check_udp -H $HOSTADDRESS$ -p $ARG1$ $ARG2$',NULL),(22,0,'check_nt','$USER1$/check_nt -H $HOSTADDRESS$ -p 12489 -v $ARG1$ $ARG2$',NULL),(23,0,'process-host-perfdata','/usr/bin/perl /usr/local/pnp4nagios/libexec/process_perfdata.pl -d HOSTPERFDATA','Built-in Nagios/Icinga command that passes Host Performance Data statistics into a Database'),(24,0,'process-service-perfdata','/usr/bin/perl /usr/local/pnp4nagios/libexec/process_perfdata.pl','Built-in Nagios/Icinga command that passes Service Performance Data statistics into a Database'),(34,0,'NSClient++ CheckCPU','$USER1$/check_nrpe -H $HOSTNAME$ -c CheckCPU -a warn=$ARG1$ crit=$ARG2$ time=$ARG3$ time=$ARG4$ time=$ARG5$','Built-in NSClient++ Agent CPU Check on Windows Based Hosts.'),(35,0,'NSClient++ CheckDriveSize - [All]','$USER1$/check_nrpe -H $HOSTNAME$ -c CheckDriveSize -a ShowAll MinWarnFree=$ARG1$ MinCritFree=$ARG2$ CheckAll=volumes','Built-in NSClient++ Agent Drive Space Check on Windows Based Hosts for ALL drives on a particular host.'),(36,0,'NSClient++ CheckDriveSize - [Single]','$USER1$/check_nrpe -H $HOSTNAME$ -c CheckDriveSize -a ShowAll MinWarnFree=$ARG1$ MinCritFree=$ARG2$ Drive=$ARG3%','Built-in NSClient++ Agent Drive Space Check on Windows Based Hosts for a single drive on a particular host.'),(37,0,'NSClient++ CheckMEM','$USER1$/check_nrpe -H $HOSTNAME$ -c CheckMEM -a MaxWarn=$ARG1$ MaxCrit=$ARG2$ ShowAll type=physical type=page','Built-in NSClient++ Agent Memory Check on Windows Based Hosts.'),(38,0,'NSClient++ CheckSysUptime','$USER1$/check_nrpe -H $HOSTNAME$ -c CheckUpTime -a MinWarn=$ARG1$ MinCrit=$ARG2$','Built-in NSClient++ Agent System UpTime Check on Windows Based Hosts.'),(39,0,'NSClient++ CheckAgent','$USER1$/check_nrpe -H $HOSTNAME$ -c CheckVersion','Built-in NSClient++ Agent Check to return the current version of NSClient++ running on Windows Based Hosts.'),(42,0,'icinga_check_service','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_service -a $ARG1$','Icinga - Check that verifies that a Service is running on a Linux Host from the /sbin/service directory.'),(43,0,'icinga_check_port','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_port -a $ARG1$ $ARG2$ $ARG3$','Icinga - Check that returns back the state of a Network Port on a Linux Host.'),(44,0,'check_nrpe_agent_version','$USER1$/check_nrpe -H $HOSTNAME$','Returns back the check_nrpe version running on a Linux Host.'),(45,0,'icinga_check_dns','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_dns -a $ARG1$','Icinga - Check that validates DNS using the Nagios Plugin - check_dns'),(46,0,'icinga_check_file_count','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_file_count -a $ARG1$ $ARG2$','Icinga - Check that verifies that a Directory exists and can alert based upon the number of files within the Directory.'),(47,0,'icinga_check_file_size','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_file_size -a $ARG1$ $ARG2$','Icinga - Check that verifies that a File exists and can alert based upon the size of the File.'),(48,0,'icinga_check_ip_rules','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_ip_rules -a $ARG1$ $ARG2$','Icinga - Custom Check that verifies that specific IP Rules exist within a Linux Hosts\' Routing Policy Database.'),(49,0,'icinga_check_ldap','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_ldap -a $ARG1$ $ARG2$','Icinga - Check that runs a standard UNIX LDAP Query.'),(50,0,'icinga_check_ldap_sync','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_ldap_sync -a $ARG1$ $ARG2$','Icinga - Check that verifies if LDAP is in sync or not.'),(52,0,'icinga_check_ntp','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_ntp -a $ARG1$ $AGR2$','Icinga - Check that keeps track of the Network Time Protocol latency values on a Linux Host.'),(53,0,'icinga_check_ntp_time','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_ntp_time -a $ARG1$','Icinga - Check that keeps track of the Network Time Protocol latency values on a Linux Host.'),(54,0,'icinga_check_powerpath','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_powerpath -a $ARG1$','Icinga - Check that verifies the status of EMC PowerPath Storage Devices attached to a Server.'),(56,0,'icinga_check_ps_service','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_ps_service -a $ARG1$ $ARG2$','Icinga - Check that verifes that a Service is running on a Linux Host by finding its process using the ps-command.'),(57,0,'icinga_check_squid_stats','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_squid_stats -a $ARG1$','Icinga - Check that runs a customized script to return back Statistics on the Squid Proxy Service.'),(59,0,'icinga_check_sys_uptime','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_sys_uptime -a $ARG1$','Icinga - Check that returns back the amount of time that a Linux Host has been Up.'),(61,0,'icinga_check_vol_rw','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_vol_rw','Icinga - Check that returns back whether a mount point is in a Read-Write or Read-Only State'),(63,0,'icinga_check_zombie_procs','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_zombie_procs -a $ARG1$ $ARG2$','Icinga - Check that returns back the number of Zombie Processes on a Linux Host.'),(64,0,'url_content_check','$USER1$/check_http -H $ARG1$ -p $ARG2$ -u $ARG3$ $ARG4$ $ARG5$','Standard URL Check that can do content matching based on a Regular Expression.'),(65,0,'icinga_check_clamav','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_clamav -a $ARG1$','Icinga - Check that verifies the current AntiVirus Defintions of Clam AntiVirus'),(66,0,'icinga_check_bonding','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_bonding -a $ARG1$','Icinga - Check that returns the status of a RAID Controller on a Linux Host using the check_bonding.pl script.'),(68,0,'icinga-notify-service-by-email-html','$USER1$/icinga-notify-service-by-email-html -f graph -u ','Icinga - Custom Service Notification Command that sends out an e-mail in HTML Format.'),(69,0,'icinga-notify-host-by-email-html','$USER1$/icinga-notify-host-by-email-html -f graph -u ','Icinga - Custom Host Notification Command that sends out an e-mail in HTML Format.'),(70,0,'icinga_check_disk','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_disk -a $ARG1$ $ARG2$','Icinga - Disk Space Check on a Linux Host. Includes additional Performance Data on Plugin Performance.'),(71,0,'icinga_check_swap_percent','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_swap_percent -a $ARG1$ $ARG2$','Icinga - Check that returns back the Percentage of Space used on the Linux Hosts Swap File. Includes additional Performance Data on Plugin Performance.'),(72,0,'icinga_check_load','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_load -a $ARG1$ $ARG2$','Icinga - Check that returns the 5,10, and 15 minute CPU Load Averages on a Linux Host.  Includes additional Performance Data on Plugin Performance.'),(73,0,'icinga_check_process_count','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_process_count -a $ARG1$','Icinga - Check that returns back the number of running processes on a Linux Host. Includes additional Performance Data on Plugin Performance.'),(74,0,'icinga_check_users','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_users -a $ARG1$ $ARG2$','Icinga - Check that returns back the number of users currently logged in on a Linux Host. Includes additional Performance Data on Plugin Performance.'),(75,0,'icinga_check_mem_used','$USER1$/check_nrpe -H $HOSTNAME$ -c icinga_check_mem_used -a $ARG1$ $ARG2$','Icinga - Check that returns back the current amount of Memory Used on a Linux Host. Includes additional Performance Data on Plugin Performance.');
/*!40000 ALTER TABLE `nagios_commands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_commands_desc`
--

DROP TABLE IF EXISTS `nagios_commands_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_commands_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_commands_desc`
--

LOCK TABLES `nagios_commands_desc` WRITE;
/*!40000 ALTER TABLE `nagios_commands_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_commands_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contact_addresses`
--

DROP TABLE IF EXISTS `nagios_contact_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contact_addresses` (
  `contactaddress_id` int(11) unsigned NOT NULL auto_increment,
  `contact_id` int(11) unsigned NOT NULL default '0',
  `address` text NOT NULL,
  PRIMARY KEY  (`contactaddress_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contact_addresses`
--

LOCK TABLES `nagios_contact_addresses` WRITE;
/*!40000 ALTER TABLE `nagios_contact_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_contact_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contactgroup_membership`
--

DROP TABLE IF EXISTS `nagios_contactgroup_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactgroup_membership` (
  `contactgroup_id` int(11) unsigned NOT NULL default '0',
  `contact_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`contactgroup_id`,`contact_id`),
  KEY `contactgroup_contact` (`contactgroup_id`,`contact_id`),
  KEY `contact_contactgroup` (`contact_id`,`contactgroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contactgroup_membership`
--

LOCK TABLES `nagios_contactgroup_membership` WRITE;
/*!40000 ALTER TABLE `nagios_contactgroup_membership` DISABLE KEYS */;
INSERT INTO `nagios_contactgroup_membership` VALUES (1,1);
/*!40000 ALTER TABLE `nagios_contactgroup_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contactgroups`
--

DROP TABLE IF EXISTS `nagios_contactgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactgroups` (
  `contactgroup_id` int(11) unsigned NOT NULL auto_increment,
  `network_id` int(11) unsigned NOT NULL default '0',
  `contactgroup_name` text NOT NULL,
  `alias` text NOT NULL,
  PRIMARY KEY  (`contactgroup_id`),
  KEY `name` (`contactgroup_name`(128))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contactgroups`
--

LOCK TABLES `nagios_contactgroups` WRITE;
/*!40000 ALTER TABLE `nagios_contactgroups` DISABLE KEYS */;
INSERT INTO `nagios_contactgroups` VALUES (1,0,'Icinga-Admins','Icinga Administrators/Monitoring Team');
/*!40000 ALTER TABLE `nagios_contactgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contactgroups_desc`
--

DROP TABLE IF EXISTS `nagios_contactgroups_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contactgroups_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contactgroups_desc`
--

LOCK TABLES `nagios_contactgroups_desc` WRITE;
/*!40000 ALTER TABLE `nagios_contactgroups_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_contactgroups_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contacts`
--

DROP TABLE IF EXISTS `nagios_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contacts` (
  `contact_id` int(11) unsigned NOT NULL auto_increment,
  `alias` text,
  `host_notification_period` int(11) unsigned NOT NULL default '0',
  `service_notification_period` int(11) unsigned NOT NULL default '0',
  `email` text,
  `pager` text,
  `host_notification_options_down` enum('0','1') NOT NULL default '0',
  `host_notification_options_unreachable` enum('0','1') NOT NULL default '0',
  `host_notification_options_recovery` enum('0','1') NOT NULL default '0',
  `service_notification_options_warning` enum('0','1') NOT NULL default '0',
  `service_notification_options_unknown` enum('0','1') NOT NULL default '0',
  `service_notification_options_critical` enum('0','1') NOT NULL default '0',
  `service_notification_options_recovery` enum('0','1') NOT NULL default '0',
  `host_notification_options_flapping` enum('0','1') NOT NULL default '0',
  `service_notification_options_flapping` enum('0','1') NOT NULL default '0',
  `contact_name` text NOT NULL,
  `command_execution` int(11) NOT NULL default '0',
  PRIMARY KEY  (`contact_id`),
  KEY `name` (`contact_name`(128))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contacts`
--

LOCK TABLES `nagios_contacts` WRITE;
/*!40000 ALTER TABLE `nagios_contacts` DISABLE KEYS */;
INSERT INTO `nagios_contacts` VALUES (1,'icingaadmin - Default Icinga Admin Account',1,1,'icingaadmin@localhost.local',NULL,'1','1','1','1','1','1','1','','','icingaadmin',0);
/*!40000 ALTER TABLE `nagios_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contacts_desc`
--

DROP TABLE IF EXISTS `nagios_contacts_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contacts_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contacts_desc`
--

LOCK TABLES `nagios_contacts_desc` WRITE;
/*!40000 ALTER TABLE `nagios_contacts_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_contacts_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_contacts_notification_commands`
--

DROP TABLE IF EXISTS `nagios_contacts_notification_commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_contacts_notification_commands` (
  `contact_notification_command_id` int(11) unsigned NOT NULL auto_increment,
  `contact_id` int(11) unsigned NOT NULL default '0',
  `command_id` int(11) unsigned NOT NULL default '0',
  `notification_type` enum('host','service') NOT NULL default 'host',
  PRIMARY KEY  (`contact_notification_command_id`),
  KEY `contact_id` (`contact_id`),
  KEY `command_id` (`command_id`,`contact_id`,`notification_type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_contacts_notification_commands`
--

LOCK TABLES `nagios_contacts_notification_commands` WRITE;
/*!40000 ALTER TABLE `nagios_contacts_notification_commands` DISABLE KEYS */;
INSERT INTO `nagios_contacts_notification_commands` VALUES (5,2,1,'host'),(6,2,2,'service'),(8,1,68,'service'),(7,1,69,'host');
/*!40000 ALTER TABLE `nagios_contacts_notification_commands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_dependencies`
--

DROP TABLE IF EXISTS `nagios_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_dependencies` (
  `dependency_id` int(11) unsigned NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `host_template_id` int(11) unsigned default NULL,
  `service_template_id` int(11) unsigned default NULL,
  `service_id` int(11) unsigned default NULL,
  `target_host_id` int(11) unsigned default NULL,
  `target_service_id` int(11) unsigned default NULL,
  `inherits_parent` enum('0','1') default NULL,
  `execution_failure_criteria_up` enum('0','1') default NULL,
  `execution_failure_criteria_down` enum('0','1') default NULL,
  `execution_failure_criteria_unreachable` enum('0','1') default NULL,
  `execution_failure_criteria_pending` enum('0','1') default NULL,
  `execution_failure_criteria_ok` enum('0','1') default NULL,
  `execution_failure_criteria_warning` enum('0','1') default NULL,
  `execution_failure_criteria_unknown` enum('0','1') default NULL,
  `execution_failure_criteria_critical` enum('0','1') default NULL,
  `notification_failure_criteria_ok` enum('0','1') default NULL,
  `notification_failure_criteria_warning` enum('0','1') default NULL,
  `notification_failure_criteria_unknown` enum('0','1') default NULL,
  `notification_failure_criteria_critical` enum('0','1') default NULL,
  `notification_failure_criteria_pending` enum('0','1') default NULL,
  `notification_failure_criteria_up` enum('0','1') default NULL,
  `notification_failure_criteria_down` enum('0','1') default NULL,
  `notification_failure_criteria_unreachable` enum('0','1') default NULL,
  PRIMARY KEY  (`dependency_id`),
  KEY `host_template_id` (`host_template_id`,`service_id`),
  KEY `service_template_id` (`service_template_id`),
  KEY `host_id` (`host_id`,`target_host_id`),
  KEY `service_id` (`service_id`,`target_host_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='Depdendencies for services, hosts, and templates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_dependencies`
--

LOCK TABLES `nagios_dependencies` WRITE;
/*!40000 ALTER TABLE `nagios_dependencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_dependencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_dependency_desc`
--

DROP TABLE IF EXISTS `nagios_dependency_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_dependency_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_dependency_desc`
--

LOCK TABLES `nagios_dependency_desc` WRITE;
/*!40000 ALTER TABLE `nagios_dependency_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_dependency_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_escalation_contactgroups`
--

DROP TABLE IF EXISTS `nagios_escalation_contactgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_escalation_contactgroups` (
  `escalation_id` int(11) unsigned NOT NULL default '0',
  `contactgroup_id` int(11) unsigned NOT NULL default '0',
  KEY `escalation_id` (`escalation_id`),
  KEY `contactgroup_id` (`contactgroup_id`,`escalation_id`),
  CONSTRAINT `nagios_escalation_contactgroups_ibfk_1` FOREIGN KEY (`escalation_id`) REFERENCES `nagios_escalations` (`escalation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_escalation_contactgroups`
--

LOCK TABLES `nagios_escalation_contactgroups` WRITE;
/*!40000 ALTER TABLE `nagios_escalation_contactgroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_escalation_contactgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_escalations`
--

DROP TABLE IF EXISTS `nagios_escalations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_escalations` (
  `escalation_id` int(11) unsigned NOT NULL auto_increment,
  `escalation_description` text NOT NULL,
  `host_id` int(11) default NULL,
  `host_template_id` int(11) unsigned default NULL,
  `service_template_id` int(11) unsigned default NULL,
  `service_id` int(11) unsigned default NULL,
  `first_notification` int(4) default NULL,
  `last_notification` varchar(4) default NULL,
  `notification_interval` int(8) default NULL,
  `escalation_period` int(11) unsigned default NULL,
  `escalation_options_up` enum('0','1') default NULL,
  `escalation_options_down` enum('0','1') default NULL,
  `escalation_options_unreachable` enum('0','1') default NULL,
  `escalation_options_ok` enum('0','1') default NULL,
  `escalation_options_warning` enum('0','1') default NULL,
  `escalation_options_unknown` enum('0','1') default NULL,
  `escalation_options_critical` enum('0','1') default NULL,
  PRIMARY KEY  (`escalation_id`),
  KEY `service_template_id` (`service_template_id`,`escalation_description`(128),`service_id`),
  KEY `host_template_id` (`host_template_id`,`escalation_description`(128),`service_id`),
  KEY `host_id` (`host_id`,`escalation_description`(128),`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Depdendencies for services, hosts, and templates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_escalations`
--

LOCK TABLES `nagios_escalations` WRITE;
/*!40000 ALTER TABLE `nagios_escalations` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_escalations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_escalations_desc`
--

DROP TABLE IF EXISTS `nagios_escalations_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_escalations_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_escalations_desc`
--

LOCK TABLES `nagios_escalations_desc` WRITE;
/*!40000 ALTER TABLE `nagios_escalations_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_escalations_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_host_contactgroups`
--

DROP TABLE IF EXISTS `nagios_host_contactgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_contactgroups` (
  `host_id` int(11) unsigned NOT NULL default '0',
  `contactgroup_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`host_id`,`contactgroup_id`),
  KEY `contactgroup_id` (`contactgroup_id`,`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_host_contactgroups`
--

LOCK TABLES `nagios_host_contactgroups` WRITE;
/*!40000 ALTER TABLE `nagios_host_contactgroups` DISABLE KEYS */;
INSERT INTO `nagios_host_contactgroups` VALUES (1,1),(2,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(52,1),(53,1);
/*!40000 ALTER TABLE `nagios_host_contactgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_host_parents`
--

DROP TABLE IF EXISTS `nagios_host_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_parents` (
  `child_id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  KEY `child_parent` (`child_id`,`parent_id`),
  KEY `parent_child` (`parent_id`,`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_host_parents`
--

LOCK TABLES `nagios_host_parents` WRITE;
/*!40000 ALTER TABLE `nagios_host_parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_host_parents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_host_template_contactgroups`
--

DROP TABLE IF EXISTS `nagios_host_template_contactgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_template_contactgroups` (
  `host_template_id` int(11) unsigned NOT NULL default '0',
  `contactgroup_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`host_template_id`,`contactgroup_id`),
  KEY `host_template_id` (`host_template_id`),
  KEY `contactgroup_id` (`contactgroup_id`,`host_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_host_template_contactgroups`
--

LOCK TABLES `nagios_host_template_contactgroups` WRITE;
/*!40000 ALTER TABLE `nagios_host_template_contactgroups` DISABLE KEYS */;
INSERT INTO `nagios_host_template_contactgroups` VALUES (2,1),(3,1),(4,1),(5,1);
/*!40000 ALTER TABLE `nagios_host_template_contactgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_host_template_extended_info`
--

DROP TABLE IF EXISTS `nagios_host_template_extended_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_template_extended_info` (
  `host_template_id` int(11) unsigned NOT NULL default '0',
  `notes` text,
  `notes_url` text,
  `action_url` text,
  `icon_image` text,
  `icon_image_alt` text,
  `vrml_image` text,
  `statusmap_image` text,
  `two_d_coords` text,
  `three_d_coords` text,
  PRIMARY KEY  (`host_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_host_template_extended_info`
--

LOCK TABLES `nagios_host_template_extended_info` WRITE;
/*!40000 ALTER TABLE `nagios_host_template_extended_info` DISABLE KEYS */;
INSERT INTO `nagios_host_template_extended_info` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `nagios_host_template_extended_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_host_templates`
--

DROP TABLE IF EXISTS `nagios_host_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_host_templates` (
  `host_template_id` int(11) unsigned NOT NULL auto_increment,
  `use_template_id` int(11) unsigned default NULL,
  `template_name` text NOT NULL,
  `template_description` text NOT NULL,
  `check_command` int(11) unsigned default NULL,
  `max_check_attempts` int(2) default NULL,
  `check_interval` int(8) default NULL,
  `passive_checks_enabled` enum('0','1') default NULL,
  `check_period` int(11) unsigned default NULL,
  `obsess_over_host` enum('0','1') default NULL,
  `check_freshness` enum('0','1') default NULL,
  `freshness_threshold` int(8) default NULL,
  `active_checks_enabled` enum('0','1') default NULL,
  `checks_enabled` enum('0','1') default NULL,
  `event_handler` int(11) default NULL,
  `event_handler_enabled` enum('0','1') default NULL,
  `low_flap_threshold` int(6) default NULL,
  `high_flap_threshold` int(6) default NULL,
  `flap_detection_enabled` enum('0','1') default NULL,
  `process_perf_data` enum('0','1') default NULL,
  `retain_status_information` enum('0','1') default NULL,
  `retain_nonstatus_information` enum('0','1') default NULL,
  `notification_interval` int(8) default NULL,
  `notification_period` int(11) unsigned default NULL,
  `notifications_enabled` enum('0','1') default NULL,
  `notification_options_down` enum('0','1') default NULL,
  `notification_options_unreachable` enum('0','1') default NULL,
  `notification_options_recovery` enum('0','1') default NULL,
  `notification_options_flapping` enum('0','1') default NULL,
  `stalking_options_up` enum('0','1') default NULL,
  `stalking_options_down` enum('0','1') default NULL,
  `stalking_options_unreachable` enum('0','1') default NULL,
  `failure_prediction_enabled` enum('0','1') default NULL,
  `retry_interval` int(8) default NULL,
  PRIMARY KEY  (`host_template_id`),
  KEY `name` (`template_name`(128)),
  KEY `id` (`use_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_host_templates`
--

LOCK TABLES `nagios_host_templates` WRITE;
/*!40000 ALTER TABLE `nagios_host_templates` DISABLE KEYS */;
INSERT INTO `nagios_host_templates` VALUES (1,NULL,'generic-host','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1',NULL,NULL,'1','1','1','1',NULL,1,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'1',NULL),(2,1,'linux-server','',3,10,5,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,120,2,NULL,'1','1','1',NULL,NULL,NULL,NULL,NULL,1),(3,1,'windows-server','',3,10,5,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,30,1,NULL,'1',NULL,'1',NULL,NULL,NULL,NULL,NULL,1),(4,1,'generic-printer','',3,10,5,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,30,2,NULL,'1',NULL,'1',NULL,NULL,NULL,NULL,NULL,1),(5,1,'generic-switch','',3,10,5,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,30,1,NULL,'1',NULL,'1',NULL,NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `nagios_host_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hostgroup_membership`
--

DROP TABLE IF EXISTS `nagios_hostgroup_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostgroup_membership` (
  `hostgroup_id` int(11) unsigned NOT NULL default '0',
  `host_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`hostgroup_id`,`host_id`),
  KEY `host_id` (`host_id`,`hostgroup_id`),
  KEY `hostgroup_id` (`hostgroup_id`,`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hostgroup_membership`
--

LOCK TABLES `nagios_hostgroup_membership` WRITE;
/*!40000 ALTER TABLE `nagios_hostgroup_membership` DISABLE KEYS */;
INSERT INTO `nagios_hostgroup_membership` VALUES (2,1),(4,14);
/*!40000 ALTER TABLE `nagios_hostgroup_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hostgroup_template_membership`
--

DROP TABLE IF EXISTS `nagios_hostgroup_template_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostgroup_template_membership` (
  `hostgroup_id` int(11) unsigned NOT NULL default '0',
  `host_template_id` int(11) unsigned NOT NULL default '0',
  KEY `host_template_id` (`host_template_id`,`hostgroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hostgroup_template_membership`
--

LOCK TABLES `nagios_hostgroup_template_membership` WRITE;
/*!40000 ALTER TABLE `nagios_hostgroup_template_membership` DISABLE KEYS */;
INSERT INTO `nagios_hostgroup_template_membership` VALUES (0,3);
/*!40000 ALTER TABLE `nagios_hostgroup_template_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hostgroups`
--

DROP TABLE IF EXISTS `nagios_hostgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostgroups` (
  `hostgroup_id` int(11) unsigned NOT NULL auto_increment,
  `network_id` int(11) unsigned NOT NULL default '0',
  `hostgroup_name` text NOT NULL,
  `alias` text NOT NULL,
  PRIMARY KEY  (`hostgroup_id`),
  KEY `name` (`hostgroup_name`(128))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hostgroups`
--

LOCK TABLES `nagios_hostgroups` WRITE;
/*!40000 ALTER TABLE `nagios_hostgroups` DISABLE KEYS */;
INSERT INTO `nagios_hostgroups` VALUES (2,0,'Linux Servers','Linux Servers'),(4,0,'Windows Servers','Windows Servers');
/*!40000 ALTER TABLE `nagios_hostgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hostgroups_desc`
--

DROP TABLE IF EXISTS `nagios_hostgroups_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hostgroups_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hostgroups_desc`
--

LOCK TABLES `nagios_hostgroups_desc` WRITE;
/*!40000 ALTER TABLE `nagios_hostgroups_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_hostgroups_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hosts`
--

DROP TABLE IF EXISTS `nagios_hosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hosts` (
  `host_id` int(11) unsigned NOT NULL auto_increment,
  `use_template_id` int(11) unsigned default NULL,
  `host_name` text NOT NULL,
  `alias` text NOT NULL,
  `address` text NOT NULL,
  `parents` int(11) unsigned default NULL,
  `check_command` int(11) unsigned default NULL,
  `max_check_attempts` int(2) default NULL,
  `check_interval` int(8) default NULL,
  `passive_checks_enabled` enum('0','1') default NULL,
  `check_period` int(11) unsigned default NULL,
  `obsess_over_host` enum('0','1') default NULL,
  `check_freshness` enum('0','1') default NULL,
  `freshness_threshold` int(8) default NULL,
  `active_checks_enabled` enum('0','1') default NULL,
  `checks_enabled` enum('0','1') default NULL,
  `event_handler` int(11) default NULL,
  `event_handler_enabled` enum('0','1') default NULL,
  `low_flap_threshold` int(6) default NULL,
  `high_flap_threshold` int(6) default NULL,
  `flap_detection_enabled` enum('0','1') default NULL,
  `process_perf_data` enum('0','1') default NULL,
  `retain_status_information` enum('0','1') default NULL,
  `retain_nonstatus_information` enum('0','1') default NULL,
  `notification_interval` int(8) default NULL,
  `notification_period` int(11) unsigned default NULL,
  `notifications_enabled` enum('0','1') default NULL,
  `notification_options_down` enum('0','1') default NULL,
  `notification_options_unreachable` enum('0','1') default NULL,
  `notification_options_recovery` enum('0','1') default NULL,
  `notification_options_flapping` enum('0','1') default NULL,
  `stalking_options_up` enum('0','1') default NULL,
  `stalking_options_down` enum('0','1') default NULL,
  `stalking_options_unreachable` enum('0','1') default NULL,
  `failure_prediction_enabled` enum('0','1') default NULL,
  `community` varchar(64) default NULL,
  `snmp_port` int(6) default NULL,
  `retry_interval` int(8) default NULL,
  PRIMARY KEY  (`host_id`),
  KEY `hosts_parents` (`host_name`(128),`parents`),
  KEY `parent_host` (`parents`,`host_name`(128)),
  KEY `use_template_id` (`use_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hosts`
--

LOCK TABLES `nagios_hosts` WRITE;
/*!40000 ALTER TABLE `nagios_hosts` DISABLE KEYS */;
INSERT INTO `nagios_hosts` VALUES (1,NULL,'localhost','Icinga Monitoring Server','127.0.0.1',NULL,3,5,10,'1',1,NULL,'1',86400,'1',NULL,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1',NULL,NULL,NULL,'1',NULL,NULL,2),(14,NULL,'testserver101.fabrikam.com','Sample Windows Server','1.1.1.1',NULL,3,2,10,'1',1,NULL,'1',86400,'1',NULL,3,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1',NULL,NULL,NULL,'1',NULL,NULL,5);
/*!40000 ALTER TABLE `nagios_hosts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hosts_check_command_parameters`
--

DROP TABLE IF EXISTS `nagios_hosts_check_command_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hosts_check_command_parameters` (
  `checkcommandparameter_id` int(11) unsigned NOT NULL auto_increment,
  `host_id` int(11) unsigned default NULL,
  `host_template_id` int(11) unsigned default NULL,
  `parameter` text NOT NULL,
  PRIMARY KEY  (`checkcommandparameter_id`),
  KEY `host_id` (`host_id`,`checkcommandparameter_id`),
  KEY `host_template_id` (`host_template_id`,`checkcommandparameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hosts_check_command_parameters`
--

LOCK TABLES `nagios_hosts_check_command_parameters` WRITE;
/*!40000 ALTER TABLE `nagios_hosts_check_command_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_hosts_check_command_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hosts_desc`
--

DROP TABLE IF EXISTS `nagios_hosts_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hosts_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hosts_desc`
--

LOCK TABLES `nagios_hosts_desc` WRITE;
/*!40000 ALTER TABLE `nagios_hosts_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_hosts_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hosts_extended_info`
--

DROP TABLE IF EXISTS `nagios_hosts_extended_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hosts_extended_info` (
  `host_id` int(11) unsigned NOT NULL default '0',
  `notes` text,
  `notes_url` text,
  `action_url` text,
  `icon_image` text,
  `icon_image_alt` text,
  `vrml_image` text,
  `statusmap_image` text,
  `two_d_coords` text NOT NULL,
  `three_d_coords` text,
  PRIMARY KEY  (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hosts_extended_info`
--

LOCK TABLES `nagios_hosts_extended_info` WRITE;
/*!40000 ALTER TABLE `nagios_hosts_extended_info` DISABLE KEYS */;
INSERT INTO `nagios_hosts_extended_info` VALUES (1,'CentOS 5.5 - x64',NULL,NULL,'centos.gif',NULL,NULL,'centos.gd2','',NULL),(14,'Windows Server 2008 - Test Server',NULL,NULL,'vista.gif',NULL,NULL,'vista.gd2','',NULL);
/*!40000 ALTER TABLE `nagios_hosts_extended_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_hosts_extended_info_desc`
--

DROP TABLE IF EXISTS `nagios_hosts_extended_info_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_hosts_extended_info_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_hosts_extended_info_desc`
--

LOCK TABLES `nagios_hosts_extended_info_desc` WRITE;
/*!40000 ALTER TABLE `nagios_hosts_extended_info_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_hosts_extended_info_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_main`
--

DROP TABLE IF EXISTS `nagios_main`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_main` (
  `config_dir` varchar(255) default NULL,
  `log_file` varchar(255) default NULL,
  `object_cache_file` varchar(255) default NULL,
  `temp_file` varchar(255) default NULL,
  `status_file` varchar(255) default NULL,
  `log_archive_path` varchar(255) default NULL,
  `command_file` varchar(255) default NULL,
  `lock_file` varchar(255) default NULL,
  `state_retention_file` varchar(255) default NULL,
  `sync_retention_file` varchar(255) default '/usr/local/icinga/var/sync.dat',
  `check_result_path` varchar(255) default NULL,
  `temp_path` varchar(255) default NULL,
  `p1_file` varchar(255) default NULL,
  `status_update_interval` int(20) default NULL,
  `use_large_installation_tweaks` enum('0','1') default NULL,
  `enable_environment_macros` enum('0','1') default NULL,
  `free_child_process_memory` enum('0','1') default NULL,
  `check_result_reaper_frequency` int(20) default NULL,
  `max_check_result_reaper_time` int(20) default NULL,
  `max_check_result_file_age` int(20) default NULL,
  `enable_predictive_host_dependency_checks` enum('0','1') default NULL,
  `enable_predictive_service_dependency_checks` enum('0','1') default NULL,
  `cached_host_check_horizon` int(20) default NULL,
  `cached_service_check_horizon` int(20) default NULL,
  `check_for_orphaned_hosts` enum('0','1') default NULL,
  `enable_embedded_perl` enum('0','1') default NULL,
  `use_embedded_perl_implicitly` enum('0','1') default NULL,
  `nagios_user` varchar(255) default NULL,
  `icinga_user` varchar(255) default NULL,
  `nagios_group` varchar(255) default NULL,
  `icinga_group` varchar(255) default NULL,
  `enable_notifications` enum('0','1') default NULL,
  `execute_service_checks` enum('0','1') default NULL,
  `accept_passive_service_checks` enum('0','1') default NULL,
  `execute_host_checks` enum('0','1') default NULL,
  `accept_passive_host_checks` enum('0','1') default NULL,
  `translate_passive_host_checks` enum('0','1') default NULL,
  `passive_host_checks_are_soft` enum('0','1') default NULL,
  `enable_event_handlers` enum('0','1') default NULL,
  `log_rotation_method` enum('n','h','d','w','m') default NULL,
  `use_syslog` enum('0','1') default NULL,
  `log_notifications` enum('0','1') default NULL,
  `log_service_retries` enum('0','1') default NULL,
  `log_host_retries` enum('0','1') default NULL,
  `log_event_handlers` enum('0','1') default NULL,
  `log_initial_states` enum('0','1') default NULL,
  `log_external_commands` enum('0','1') default NULL,
  `log_passive_checks` enum('0','1') default NULL,
  `check_external_commands` enum('0','1') default NULL,
  `command_check_interval` int(20) default NULL,
  `external_command_buffer_slots` int(20) default NULL,
  `precached_object_file` enum('0','1') default NULL,
  `retain_state_information` enum('0','1') default NULL,
  `retention_update_interval` int(20) default NULL,
  `use_retained_program_state` enum('0','1') default NULL,
  `use_retained_scheduling_info` enum('0','1') default NULL,
  `retained_host_attribute_mask` varchar(255) default NULL,
  `retained_service_attribute_mask` varchar(255) default NULL,
  `retained_process_host_attribute_mask` varchar(255) default NULL,
  `retained_process_service_attribute_mask` varchar(255) default NULL,
  `retained_contact_host_attribute_mask` varchar(255) default NULL,
  `retained_contact_service_attribute_mask` varchar(255) default NULL,
  `global_host_event_handler` int(20) default NULL,
  `global_service_event_handler` int(20) default NULL,
  `sleep_time` decimal(2,2) default NULL,
  `service_inter_check_delay_method` varchar(255) default NULL,
  `max_service_check_spread` int(20) default NULL,
  `host_inter_check_delay_method` varchar(255) default NULL,
  `max_host_check_spread` int(20) default NULL,
  `service_interleave_factor` varchar(255) default NULL,
  `max_concurrent_checks` int(20) default NULL,
  `interval_length` int(20) default NULL,
  `auto_reschedule_checks` enum('0','1') default NULL,
  `auto_rescheduling_interval` int(20) default NULL,
  `auto_rescheduling_window` int(20) default NULL,
  `use_aggressive_host_checking` enum('0','1') default NULL,
  `enable_flap_detection` enum('0','1') default NULL,
  `low_service_flap_threshold` decimal(5,1) default NULL,
  `high_service_flap_threshold` decimal(5,1) default NULL,
  `low_host_flap_threshold` decimal(5,1) default NULL,
  `high_host_flap_threshold` decimal(5,1) default NULL,
  `service_check_timeout` int(20) default NULL,
  `host_check_timeout` int(20) default NULL,
  `event_handler_timeout` int(20) default NULL,
  `notification_timeout` int(20) default NULL,
  `ocsp_timeout` int(20) default NULL,
  `ochp_timeout` int(20) default NULL,
  `perfdata_timeout` int(20) default NULL,
  `obsess_over_services` enum('0','1') default NULL,
  `ocsp_command` enum('0','1') default NULL,
  `obsess_over_hosts` enum('0','1') default NULL,
  `ochp_command` enum('0','1') default NULL,
  `check_service_freshness` enum('0','1') default NULL,
  `check_host_freshness` enum('0','1') default NULL,
  `host_freshness_check_interval` int(20) default NULL,
  `additional_freshness_latency` int(20) default NULL,
  `event_broker_options` enum('0','-1') default NULL,
  `module_line` varchar(255) default NULL,
  `soft_state_dependencies` enum('0','1') default NULL,
  `process_performance_data` enum('0','1') default NULL,
  `host_perfdata_command` int(11) unsigned default NULL,
  `host_perfdata_file` varchar(255) default NULL,
  `host_perfdata_file_mode` enum('a','w') default NULL,
  `host_perfdata_template` varchar(255) default NULL,
  `host_perfdata_file_processing_interval` int(20) default NULL,
  `host_perfdata_file_processing_command` int(11) unsigned default NULL,
  `service_perfdata_command` int(11) unsigned default NULL,
  `service_perfdata_file` varchar(255) default NULL,
  `service_perfdata_file_mode` enum('a','w') default NULL,
  `service_perfdata_template` varchar(255) default NULL,
  `service_perfdata_file_processing_interval` int(20) default NULL,
  `service_perfdata_file_processing_command` int(11) unsigned default NULL,
  `check_for_orphaned_services` enum('0','1') default NULL,
  `date_format` enum('us','euro','iso8601','strict-iso8601') default NULL,
  `illegal_object_name_chars` varchar(255) default NULL,
  `illegal_macro_output_chars` varchar(255) default NULL,
  `use_regexp_matching` enum('0','1') default NULL,
  `use_true_regexp_matching` enum('0','1') default NULL,
  `admin_email` varchar(255) default NULL,
  `admin_pager` varchar(255) default NULL,
  `daemon_dumps_core` enum('0','1') default NULL,
  `debug_file` varchar(255) default NULL,
  `debug_level` int(20) default NULL,
  `max_debug_file_size` int(20) default NULL,
  `debug_verbosity` varchar(255) default NULL,
  `use_daemon_log` enum('0','1') default NULL,
  `use_syslog_local_facility` enum('0','1') default NULL,
  `syslog_local_facility` int(20) default NULL,
  `log_current_states` enum('0','1') default NULL,
  `log_external_commands_user` enum('0','1') default NULL,
  `log_long_plugin_output` enum('0','1') default NULL,
  `dump_retained_host_service_states_to_neb` enum('0','1') default NULL,
  `host_perfdata_process_empty_results` enum('0','1') default NULL,
  `service_perfdata_process_empty_results` enum('0','1') default NULL,
  `allow_empty_hostgroup_assignment` enum('0','1') default NULL,
  `service_check_timeout_state` enum('c','u','w','o') default NULL,
  `service_freshness_check_interval` int(20) default NULL,
  `stalking_event_handlers_for_hosts` enum('0','1') default NULL,
  `stalking_event_handlers_for_services` enum('0','1') default NULL,
  `child_processes_fork_twice` enum('0','1') default NULL,
  `stalking_notifications_for_hosts` int(20) default '2',
  `stalking_notifications_for_services` int(20) default '2',
  `time_change_threshold` int(20) default '900',
  `host_perfdata_file_template` varchar(255) default NULL,
  `service_perfdata_file_template` varchar(255) default NULL,
  `use_timezone` varchar(255) default NULL,
  `keep_unknown_macros` int(20) default '2',
  `event_profiling_enabled` int(20) default '1',
  `max_check_result_list_items` int(2) default '-1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_main`
--

LOCK TABLES `nagios_main` WRITE;
/*!40000 ALTER TABLE `nagios_main` DISABLE KEYS */;
INSERT INTO `nagios_main` VALUES ('/usr/local/icinga/etc/','/usr/local/icinga/var/icinga.log','/usr/local/icinga/var/objects.cache','/usr/local/icinga/var/icinga.tmp','/usr/local/icinga/var/status.dat','/usr/local/icinga/var/archives','/usr/local/icinga/var/rw/icinga.cmd','/usr/local/icinga/var/icinga.lock','/usr/local/icinga/var/retention.dat','/usr/local/icinga/var/sync.dat','/usr/local/icinga/var/spool/checkresults','/tmp','/usr/local/icinga/bin/p1.pl',10,'0','1','1',10,30,3600,'1','1',15,15,'1','1','1','nagios','howler','nagios','howler','1','1','1','1','1','1','0','1','d','0','1','1','1','1','1','1','1','1',-1,4096,'1','1',60,'1','1','0','0','0','0','0','0',NULL,NULL,'0.25','s',30,'s',30,'s',0,60,'0',30,180,'0','1','5.0','20.0','5.0','20.0',300,30,30,30,5,5,5,'0','0','0','0','1','0',60,60,'-1',NULL,'0','1',23,NULL,NULL,NULL,NULL,0,24,NULL,NULL,NULL,NULL,0,'1','us','`~\\\\!$\\\\%^&*|\\\'\\\"<>?,()','`~$&|\\\'\\\"<>','0','0','icinga@localhost.com','pageicinga@localhost.com','0','/usr/local/icinga/var/icinga.debug',0,1000000,NULL,'1','0',5,'1','1','0','0','0','0','0','u',60,'0','0','0',2,2,900,NULL,NULL,NULL,2,1,-1);
/*!40000 ALTER TABLE `nagios_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_main_desc`
--

DROP TABLE IF EXISTS `nagios_main_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_main_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Nagios Main Configuration Table Description Elements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_main_desc`
--

LOCK TABLES `nagios_main_desc` WRITE;
/*!40000 ALTER TABLE `nagios_main_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_main_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_resources`
--

DROP TABLE IF EXISTS `nagios_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_resources` (
  `user1` text NOT NULL,
  `user2` text NOT NULL,
  `user3` text NOT NULL,
  `user4` text NOT NULL,
  `user5` text NOT NULL,
  `user6` text NOT NULL,
  `user7` text NOT NULL,
  `user8` text NOT NULL,
  `user9` text NOT NULL,
  `user10` text NOT NULL,
  `user11` text NOT NULL,
  `user12` text NOT NULL,
  `user13` text NOT NULL,
  `user14` text NOT NULL,
  `user15` text NOT NULL,
  `user16` text NOT NULL,
  `user17` text NOT NULL,
  `user18` text NOT NULL,
  `user19` text NOT NULL,
  `user20` text NOT NULL,
  `user21` text NOT NULL,
  `user22` text NOT NULL,
  `user23` text NOT NULL,
  `user24` text NOT NULL,
  `user25` text NOT NULL,
  `user26` text NOT NULL,
  `user27` text NOT NULL,
  `user28` text NOT NULL,
  `user29` text NOT NULL,
  `user30` text NOT NULL,
  `user31` text NOT NULL,
  `user32` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_resources`
--

LOCK TABLES `nagios_resources` WRITE;
/*!40000 ALTER TABLE `nagios_resources` DISABLE KEYS */;
INSERT INTO `nagios_resources` VALUES ('/usr/local/icinga/libexec','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','');
/*!40000 ALTER TABLE `nagios_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_service_contactgroups`
--

DROP TABLE IF EXISTS `nagios_service_contactgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_service_contactgroups` (
  `service_id` int(11) unsigned NOT NULL default '0',
  `contactgroup_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`service_id`,`contactgroup_id`),
  KEY `service_id` (`service_id`),
  KEY `contactgroup_id` (`contactgroup_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_service_contactgroups`
--

LOCK TABLES `nagios_service_contactgroups` WRITE;
/*!40000 ALTER TABLE `nagios_service_contactgroups` DISABLE KEYS */;
INSERT INTO `nagios_service_contactgroups` VALUES (1,1),(2,1),(3,1),(3,4),(4,1),(4,4),(5,1),(5,2),(5,3),(5,4),(6,1),(6,4),(7,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1),(145,1),(146,1),(147,1),(148,1),(149,1),(150,1),(151,1),(152,1),(153,1),(154,1),(155,1),(156,1),(157,1),(158,1),(159,1),(160,1),(161,1),(162,1),(163,1),(164,1),(165,1),(166,1),(167,1),(168,1),(169,1),(170,1),(171,1),(172,1),(173,1),(174,1),(175,1),(176,1),(177,1),(178,1),(179,1),(180,1),(181,1),(182,1),(183,1),(184,1),(185,1),(186,1),(187,1),(188,1),(189,1),(190,1),(191,1),(192,1),(193,1),(194,1),(195,1),(196,1),(197,1),(198,1),(199,1),(200,1),(201,1),(202,1),(203,1),(204,1),(205,1),(206,1),(207,1),(208,1),(209,1),(210,1),(211,1),(212,1),(213,1),(214,1),(215,1),(216,1),(217,1),(218,1),(219,1),(220,1),(221,1),(222,1),(223,1),(224,1),(225,1),(226,1),(227,1),(228,1),(229,1),(230,1),(231,1),(232,1),(233,1),(234,1),(235,1),(236,1),(237,1),(238,1),(239,1),(240,1),(241,1),(242,1),(243,1),(244,1),(245,1),(246,1),(247,1),(248,1),(249,1),(250,1),(251,1),(252,1),(253,1),(254,1),(255,1),(256,1),(257,1),(258,1),(259,1),(260,1),(261,1),(262,1),(263,1),(264,1),(265,1),(266,1),(267,1),(268,1),(269,1),(270,1),(271,1),(272,1),(273,1),(274,1),(275,1),(276,1),(277,1),(278,1),(279,1),(280,1),(281,1),(282,1),(283,1),(284,1),(285,1),(286,1),(287,1),(288,1),(289,1),(290,1),(291,1),(292,1),(293,1),(294,1),(295,1),(296,1),(297,1),(298,1),(299,1),(300,1),(301,1),(302,1),(303,1),(304,1),(305,1),(306,1),(307,1),(308,1),(309,1),(310,1),(311,1),(312,1),(313,1),(314,1),(315,1),(316,1),(317,1),(318,1),(319,1),(320,1),(321,1),(322,1),(323,1),(324,1),(325,1),(326,1),(327,1),(328,1),(329,1),(330,1),(331,1),(332,1),(333,1),(334,1),(335,1),(336,1),(337,1),(338,1),(339,1),(340,1),(341,1),(342,1),(343,1),(344,1),(345,1),(346,1),(347,1),(348,1),(349,1),(350,1),(351,1),(352,1),(353,1),(354,1),(355,1),(356,1),(357,1),(358,1),(359,1),(360,1),(361,1),(362,1),(363,1),(364,1),(365,1),(366,1),(367,1),(368,1),(369,1),(370,1),(371,1),(372,1),(373,1),(374,1),(375,1),(376,1),(377,1),(378,1),(379,1),(380,1),(381,1),(382,1),(383,1),(384,1),(385,1),(386,1),(387,1),(388,1),(389,1),(390,1),(391,1),(392,1),(393,1),(394,1),(395,1),(396,1),(397,1),(398,1),(399,1),(400,1),(401,1),(402,1),(403,1),(404,1),(405,1),(406,1),(407,1),(408,1),(409,1),(410,1),(411,1),(412,1),(413,1),(414,1),(415,1),(416,1),(417,1),(418,1),(419,1),(420,1),(421,1),(422,1),(423,1),(424,1),(425,1),(426,1),(427,1),(428,1),(429,1),(430,1),(431,1),(432,1),(433,1),(434,1),(435,1),(436,1),(437,1),(438,1),(439,1),(440,1),(441,1),(442,1),(443,1),(444,1),(445,1),(446,1),(447,1),(448,1),(449,1),(450,1),(451,1),(452,1),(453,1),(454,1),(455,1),(456,1),(457,1),(458,1),(459,1),(460,1),(461,1),(462,1),(463,1),(464,1),(465,1),(466,1),(467,1),(468,1),(469,1),(470,1),(471,1),(472,1),(473,1),(474,1),(475,1),(476,1),(477,1),(478,1),(479,1),(480,1),(481,1),(482,1),(483,1),(484,1),(485,1),(486,1),(487,1),(488,1),(489,1),(490,1),(491,1),(492,1),(493,1),(494,1),(495,1),(496,1),(497,1),(498,1),(499,1),(500,1),(501,1),(502,1),(503,1),(504,1),(505,1),(506,1),(507,1),(508,1),(509,1),(510,1),(511,1),(512,1),(513,1),(514,1),(515,1),(516,1),(517,1),(518,1),(519,1),(520,1),(521,1),(522,1),(523,1),(524,1),(525,1),(526,1),(527,1),(528,1),(529,1),(530,1),(531,1),(532,1),(533,1),(534,1),(535,1),(536,1),(537,1),(538,1),(539,1),(540,1),(541,1),(542,1),(543,1),(544,1),(545,1),(546,1),(547,1),(548,1),(549,1),(550,1),(551,1),(552,1),(553,1),(554,1),(555,1),(556,1),(557,1),(558,1),(559,1),(560,1),(561,1),(562,1),(563,1),(564,1),(565,1),(566,1),(567,1),(568,1),(569,1),(570,1),(571,1),(572,1),(573,1),(574,1),(575,1),(576,1),(577,1),(578,1),(579,1),(580,1),(581,1),(582,1),(583,1),(584,1),(585,1),(586,1),(587,1),(588,1),(589,1),(590,1),(591,1),(592,1),(593,1),(594,1),(595,1),(596,1),(597,1),(598,1),(599,1),(600,1),(601,1),(602,1),(603,1),(604,1),(605,1),(606,1),(607,1),(608,1),(609,1),(610,1),(611,1),(612,1),(613,1),(614,1),(615,1),(616,1),(617,1),(618,1),(619,1),(620,1),(621,1),(622,1),(623,1),(624,1),(625,1),(626,1),(627,1),(628,1),(629,1),(630,1),(631,1),(632,1),(633,1),(634,1),(635,1),(636,1),(637,1),(638,1),(639,1),(640,1),(641,1),(642,1),(643,1),(644,1),(645,1),(646,1),(647,1),(648,1),(649,1),(650,1),(651,1),(652,1),(653,1),(654,1),(655,1),(656,1),(657,1),(658,1),(659,1),(660,1),(661,1),(662,1),(663,1),(664,1),(665,1),(666,1),(667,1),(668,1),(669,1),(670,1),(671,1),(672,1),(673,1),(674,1),(675,1),(676,1),(677,1),(678,1),(679,1),(680,1),(681,1),(682,1),(683,1),(684,1),(685,1),(686,1),(687,1),(688,1),(689,1),(690,1),(691,1),(692,1),(693,1),(694,1),(695,1),(696,1),(697,1),(698,1),(699,1),(700,1),(701,1),(702,1),(703,1),(704,1),(705,1),(706,1),(707,1),(708,1),(709,1),(710,1),(711,1),(712,1),(713,1),(714,1),(715,1),(716,1),(717,1),(718,1),(719,1),(720,1),(721,1),(722,1),(723,1),(724,1),(725,1),(726,1),(727,1),(728,1),(729,1),(730,1),(731,1),(732,1),(733,1),(734,1),(735,1),(736,1),(737,1),(738,1),(739,1),(740,1),(741,1),(742,1),(743,1),(744,1),(745,1),(746,1),(747,1),(748,1),(749,1),(750,1),(751,1),(752,1),(753,1),(754,1),(755,1),(756,1),(757,1),(758,1),(759,1),(760,1),(761,1),(762,1),(763,1),(764,1),(765,1),(766,1),(767,1),(768,1),(769,1),(770,1),(771,1),(772,1),(773,1),(774,1),(775,1),(776,1),(777,1),(778,1),(779,1),(780,1),(781,1),(782,1),(783,1),(784,1),(785,1),(786,1),(787,1),(788,1),(789,1),(790,1),(791,1),(792,1),(793,1),(794,1),(795,1),(796,1),(797,1),(798,1),(799,1),(800,1),(801,1),(802,1),(803,1),(804,1),(805,1),(806,1),(807,1),(808,1),(809,1),(811,1),(812,1),(812,4),(813,1),(813,4),(814,1),(814,4),(815,1),(815,4),(816,1),(816,4),(817,1),(817,4),(818,1),(818,4),(819,1),(819,4),(820,1),(820,4),(821,1),(821,4),(822,1),(822,4),(823,1),(823,4),(824,1),(825,1),(825,4),(826,1),(826,4),(827,1),(827,4),(828,1),(828,4),(829,1),(830,1),(830,4),(831,1),(832,1),(833,1),(834,1),(835,1),(836,1),(837,1),(838,1),(839,1),(840,1),(841,1),(842,1),(843,1),(844,1),(845,1),(846,1),(847,1),(848,1),(849,1),(850,1),(851,1),(852,1),(853,1),(854,1),(855,1),(856,1),(857,1),(858,1),(858,2),(858,3),(859,1),(860,1),(861,1),(862,1),(863,1),(864,1),(865,1),(866,1),(867,1),(868,1),(868,2),(868,3),(869,1),(870,1),(871,1),(872,1),(873,1),(874,1),(875,1),(876,1),(877,1),(878,1),(879,1),(880,1),(881,1),(882,1),(883,1),(884,1),(885,1),(886,1),(887,1),(888,1),(889,1),(889,4),(890,1),(890,4),(891,1),(891,2),(891,3),(891,4),(892,1),(892,4),(893,1),(893,4),(894,1),(894,4),(895,1),(895,4),(896,1),(896,4),(897,1),(897,4),(898,1),(898,4),(899,1),(899,4),(900,1),(900,4),(901,1),(901,4),(902,1),(902,4),(903,1),(903,4),(904,1),(904,4),(905,1),(905,4),(906,1),(906,4),(907,1),(907,4),(908,1),(908,4),(909,1),(909,4),(910,1),(910,4),(911,1),(911,4),(912,1),(912,4),(913,1),(913,4),(914,1);
/*!40000 ALTER TABLE `nagios_service_contactgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_service_template_contactgroups`
--

DROP TABLE IF EXISTS `nagios_service_template_contactgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_service_template_contactgroups` (
  `service_template_id` int(11) unsigned NOT NULL default '0',
  `contactgroup_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`service_template_id`,`contactgroup_id`),
  KEY `service_template_id` (`service_template_id`),
  KEY `contactgroup_id` (`contactgroup_id`,`service_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_service_template_contactgroups`
--

LOCK TABLES `nagios_service_template_contactgroups` WRITE;
/*!40000 ALTER TABLE `nagios_service_template_contactgroups` DISABLE KEYS */;
INSERT INTO `nagios_service_template_contactgroups` VALUES (1,1);
/*!40000 ALTER TABLE `nagios_service_template_contactgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_service_template_extended_info`
--

DROP TABLE IF EXISTS `nagios_service_template_extended_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_service_template_extended_info` (
  `service_template_id` int(11) unsigned NOT NULL default '0',
  `notes` text,
  `notes_url` text,
  `action_url` text,
  `icon_image` text,
  `icon_image_alt` text,
  PRIMARY KEY  (`service_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_service_template_extended_info`
--

LOCK TABLES `nagios_service_template_extended_info` WRITE;
/*!40000 ALTER TABLE `nagios_service_template_extended_info` DISABLE KEYS */;
INSERT INTO `nagios_service_template_extended_info` VALUES (1,NULL,NULL,NULL,NULL,NULL),(2,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `nagios_service_template_extended_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_service_templates`
--

DROP TABLE IF EXISTS `nagios_service_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_service_templates` (
  `service_template_id` int(11) unsigned NOT NULL auto_increment,
  `use_template_id` int(11) unsigned default NULL,
  `template_name` text NOT NULL,
  `template_description` text NOT NULL,
  `is_volatile` enum('0','1') default NULL,
  `check_command` int(11) default NULL,
  `max_check_attempts` int(2) default NULL,
  `normal_check_interval` int(8) default NULL,
  `retry_check_interval` int(2) default NULL,
  `active_checks_enabled` enum('0','1') default NULL,
  `passive_checks_enabled` enum('0','1') default NULL,
  `check_period` int(11) unsigned default NULL,
  `parallelize_check` enum('0','1') default NULL,
  `obsess_over_service` enum('0','1') default NULL,
  `check_freshness` enum('0','1') default NULL,
  `freshness_threshold` int(8) default NULL,
  `event_handler` int(11) unsigned default NULL,
  `event_handler_enabled` enum('0','1') default NULL,
  `low_flap_threshold` int(2) default NULL,
  `high_flap_threshold` int(2) default NULL,
  `flap_detection_enabled` enum('0','1') default NULL,
  `process_perf_data` enum('0','1') default NULL,
  `retain_status_information` enum('0','1') default NULL,
  `retain_nonstatus_information` enum('0','1') default NULL,
  `notification_interval` int(8) default NULL,
  `notification_period` int(11) unsigned default NULL,
  `notification_options_warning` enum('0','1') default NULL,
  `notification_options_unknown` enum('0','1') default NULL,
  `notification_options_critical` enum('0','1') default NULL,
  `notification_options_recovery` enum('0','1') default NULL,
  `notification_options_flapping` enum('0','1') default NULL,
  `notifications_enabled` enum('0','1') default NULL,
  `stalking_options_ok` enum('0','1') default NULL,
  `stalking_options_warning` enum('0','1') default NULL,
  `stalking_options_unknown` enum('0','1') default NULL,
  `stalking_options_critical` enum('0','1') default NULL,
  `failure_prediction_enabled` enum('0','1') default NULL,
  `action_url` varchar(255) default NULL,
  PRIMARY KEY  (`service_template_id`),
  KEY `name` (`template_name`(128)),
  KEY `id` (`use_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_service_templates`
--

LOCK TABLES `nagios_service_templates` WRITE;
/*!40000 ALTER TABLE `nagios_service_templates` DISABLE KEYS */;
INSERT INTO `nagios_service_templates` VALUES (1,NULL,'generic-service','','0',NULL,3,10,2,'1','1',1,'1','1','0',NULL,NULL,'1',NULL,NULL,'1','1','1','1',60,1,'1','1','1','1',NULL,'1',NULL,NULL,NULL,NULL,'1',NULL),(2,1,'local-service','',NULL,NULL,4,5,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `nagios_service_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_servicegroup_membership`
--

DROP TABLE IF EXISTS `nagios_servicegroup_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicegroup_membership` (
  `service_id` int(11) unsigned NOT NULL default '0',
  `servicegroup_id` int(11) unsigned NOT NULL default '0',
  KEY `service_id` (`service_id`),
  KEY `servicegroup_id` (`servicegroup_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_servicegroup_membership`
--

LOCK TABLES `nagios_servicegroup_membership` WRITE;
/*!40000 ALTER TABLE `nagios_servicegroup_membership` DISABLE KEYS */;
INSERT INTO `nagios_servicegroup_membership` VALUES (830,1),(877,1),(901,1);
/*!40000 ALTER TABLE `nagios_servicegroup_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_servicegroup_template_membership`
--

DROP TABLE IF EXISTS `nagios_servicegroup_template_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicegroup_template_membership` (
  `service_template_id` int(11) unsigned NOT NULL default '0',
  `servicegroup_id` int(11) unsigned NOT NULL default '0',
  KEY `service_template_id` (`service_template_id`),
  KEY `servicegroup_id` (`servicegroup_id`,`service_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_servicegroup_template_membership`
--

LOCK TABLES `nagios_servicegroup_template_membership` WRITE;
/*!40000 ALTER TABLE `nagios_servicegroup_template_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_servicegroup_template_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_servicegroups`
--

DROP TABLE IF EXISTS `nagios_servicegroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicegroups` (
  `servicegroup_id` int(11) unsigned NOT NULL auto_increment,
  `servicegroup_name` text NOT NULL,
  `alias` text NOT NULL,
  PRIMARY KEY  (`servicegroup_id`),
  KEY `name` (`servicegroup_name`(128))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_servicegroups`
--

LOCK TABLES `nagios_servicegroups` WRITE;
/*!40000 ALTER TABLE `nagios_servicegroups` DISABLE KEYS */;
INSERT INTO `nagios_servicegroups` VALUES (1,'UNIX NRPE Agents','UNIX NRPE Agents');
/*!40000 ALTER TABLE `nagios_servicegroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_servicegroups_desc`
--

DROP TABLE IF EXISTS `nagios_servicegroups_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_servicegroups_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_servicegroups_desc`
--

LOCK TABLES `nagios_servicegroups_desc` WRITE;
/*!40000 ALTER TABLE `nagios_servicegroups_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_servicegroups_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_services`
--

DROP TABLE IF EXISTS `nagios_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_services` (
  `service_id` int(11) unsigned NOT NULL auto_increment,
  `use_template_id` int(11) default NULL,
  `service_description` text NOT NULL,
  `is_volatile` enum('0','1') default NULL,
  `check_command` int(11) default NULL,
  `max_check_attempts` int(2) default NULL,
  `normal_check_interval` int(8) default NULL,
  `retry_check_interval` int(2) default NULL,
  `active_checks_enabled` enum('0','1') default NULL,
  `passive_checks_enabled` enum('0','1') default NULL,
  `check_period` int(11) unsigned default NULL,
  `parallelize_check` enum('0','1') default NULL,
  `obsess_over_service` enum('0','1') default NULL,
  `check_freshness` enum('0','1') default NULL,
  `freshness_threshold` int(8) default NULL,
  `event_handler` int(11) unsigned default NULL,
  `event_handler_enabled` enum('0','1') default NULL,
  `low_flap_threshold` int(2) default NULL,
  `high_flap_threshold` int(2) default NULL,
  `flap_detection_enabled` enum('0','1') default NULL,
  `process_perf_data` enum('0','1') default NULL,
  `retain_status_information` enum('0','1') default NULL,
  `retain_nonstatus_information` enum('0','1') default NULL,
  `notification_interval` int(8) default NULL,
  `notification_period` int(11) unsigned default NULL,
  `notification_options_warning` enum('0','1') default NULL,
  `notification_options_unknown` enum('0','1') default NULL,
  `notification_options_critical` enum('0','1') default NULL,
  `notification_options_recovery` enum('0','1') default NULL,
  `notification_options_flapping` enum('0','1') default NULL,
  `notifications_enabled` enum('0','1') default NULL,
  `stalking_options_ok` enum('0','1') default NULL,
  `stalking_options_warning` enum('0','1') default NULL,
  `stalking_options_unknown` enum('0','1') default NULL,
  `stalking_options_critical` enum('0','1') default NULL,
  `host_id` int(11) default NULL,
  `host_template_id` int(11) default NULL,
  `hostgroup_id` int(11) unsigned default NULL,
  `failure_prediction_enabled` enum('0','1') default NULL,
  `action_url` varchar(255) default NULL,
  PRIMARY KEY  (`service_id`),
  KEY `id_name` (`service_id`,`service_description`(128)),
  KEY `name_id` (`service_description`(128),`service_id`),
  KEY `template_id_name` (`host_template_id`,`service_description`(128)),
  KEY `name_template_id` (`service_description`(128),`host_template_id`),
  KEY `hostgroup_id_name` (`hostgroup_id`,`service_description`(128)),
  KEY `name_hostgroup_id` (`service_description`(128),`hostgroup_id`),
  KEY `use_id` (`use_template_id`),
  KEY `host_id` (`host_id`,`service_id`),
  KEY `host_template_id` (`host_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=915 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_services`
--

LOCK TABLES `nagios_services` WRITE;
/*!40000 ALTER TABLE `nagios_services` DISABLE KEYS */;
INSERT INTO `nagios_services` VALUES (3,NULL,'Base - Current Users',NULL,74,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(4,NULL,'Base - Process Count',NULL,73,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(5,NULL,'Base - CPU Load',NULL,72,2,5,5,'1','1',1,'1','1','1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(6,NULL,'Base - Swap Usage',NULL,71,2,5,5,'1','1',1,'0',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(801,NULL,'Base - NSClient++ Version',NULL,39,5,5,5,'1','1',1,NULL,NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1','1',NULL,NULL,NULL,NULL,14,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(802,NULL,'Base - CPU Load',NULL,34,5,5,5,'1','1',1,NULL,NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1','1',NULL,NULL,NULL,NULL,14,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(803,NULL,'Base - Drive Space - ALL',NULL,35,5,5,5,'1','1',1,NULL,NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1','1',NULL,NULL,NULL,NULL,14,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(804,NULL,'Base - Memory Usage',NULL,37,5,5,5,'1','1',1,NULL,NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1','1',NULL,NULL,NULL,NULL,14,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(805,NULL,'Base - Uptime',NULL,38,5,5,5,'1','1',1,NULL,NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','1','1',NULL,NULL,NULL,NULL,14,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(812,NULL,'Base - Disk Space - /',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(813,NULL,'Service - httpd',NULL,42,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(814,NULL,'Service - icinga',NULL,42,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(815,NULL,'Service - ido2db',NULL,42,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(816,NULL,'Base - Disk Space - /boot',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(817,NULL,'Base - Disk Space - /home',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(818,NULL,'Base - Disk Space - /tmp',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(819,NULL,'Base - Disk Space - /usr',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(820,NULL,'Base - Disk Space - /var',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(821,NULL,'Base - Disk Space - /var/log',NULL,70,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(822,NULL,'Service - crond',NULL,42,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(823,NULL,'Port - 80',NULL,43,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(825,NULL,'Port - 3306',NULL,43,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(826,NULL,'PS-Service - MySQL Server',NULL,56,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(827,NULL,'PS-Service - Sendmail Queue',NULL,56,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(828,NULL,'PS-Service - Sendmail Service',NULL,56,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(830,NULL,'Base - NRPE Agent Version',NULL,44,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(889,NULL,'Base - Memory Utilization',NULL,75,2,5,5,'1','1',1,'0',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$'),(890,NULL,'Base - Zombified',NULL,63,2,5,5,'1','1',1,'1',NULL,'1',86400,NULL,'1',5,20,'1','1','1','1',5,1,'1','1','1','1','0','1',NULL,NULL,NULL,NULL,1,NULL,NULL,'1','/pnp4nagios/index.php/graph?host=$HOSTNAME$&srv=$SERVICEDESC$\\\' class=\\\'tips\\\' rel=\\\'/pnp4nagios/index.php/popup?host=$HOSTNAME$&srv=$SERVICEDESC$');
/*!40000 ALTER TABLE `nagios_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_services_check_command_parameters`
--

DROP TABLE IF EXISTS `nagios_services_check_command_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_services_check_command_parameters` (
  `checkcommandparameter_id` int(11) unsigned NOT NULL auto_increment,
  `service_id` int(11) unsigned default NULL,
  `service_template_id` int(11) unsigned default NULL,
  `parameter` text NOT NULL,
  PRIMARY KEY  (`checkcommandparameter_id`),
  KEY `service_template_id` (`service_template_id`,`checkcommandparameter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2606 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_services_check_command_parameters`
--

LOCK TABLES `nagios_services_check_command_parameters` WRITE;
/*!40000 ALTER TABLE `nagios_services_check_command_parameters` DISABLE KEYS */;
INSERT INTO `nagios_services_check_command_parameters` VALUES (244,36,NULL,'80'),(245,36,NULL,'90'),(246,36,NULL,'5m'),(247,36,NULL,'10s'),(248,36,NULL,'2'),(249,37,NULL,'80'),(250,37,NULL,'90'),(251,37,NULL,'5m'),(252,37,NULL,'10s'),(253,37,NULL,'2'),(254,38,NULL,'10%'),(255,38,NULL,'5%'),(256,39,NULL,'90%'),(257,39,NULL,'95%'),(258,40,NULL,'$HOSTNAME$'),(259,41,NULL,'$HOSTNAME$'),(260,41,NULL,'_Total'),(261,41,NULL,'0.80'),(262,41,NULL,'0.90'),(263,42,NULL,'$HOSTNAME$'),(264,42,NULL,'30'),(265,43,NULL,'$HOSTNAME$'),(266,43,NULL,'30'),(267,44,NULL,'$HOSTNAME$'),(268,45,NULL,'80'),(269,45,NULL,'90'),(270,45,NULL,'5m'),(271,45,NULL,'10s'),(272,45,NULL,'2'),(273,46,NULL,'80'),(274,46,NULL,'90'),(275,46,NULL,'5m'),(276,46,NULL,'10s'),(277,46,NULL,'2'),(278,47,NULL,'10%'),(279,47,NULL,'5%'),(280,48,NULL,'90%'),(281,48,NULL,'95%'),(282,49,NULL,'$HOSTNAME$'),(283,50,NULL,'$HOSTNAME$'),(284,50,NULL,'_Total'),(285,50,NULL,'0.80'),(286,50,NULL,'0.90'),(287,51,NULL,'$HOSTNAME$'),(288,51,NULL,'30'),(289,52,NULL,'$HOSTNAME$'),(290,52,NULL,'30'),(291,53,NULL,'$HOSTNAME$'),(292,54,NULL,'80'),(293,54,NULL,'90'),(294,54,NULL,'5m'),(295,54,NULL,'10s'),(296,54,NULL,'2'),(297,55,NULL,'80'),(298,55,NULL,'90'),(299,55,NULL,'5m'),(300,55,NULL,'10s'),(301,55,NULL,'2'),(302,56,NULL,'10%'),(303,56,NULL,'5%'),(304,57,NULL,'90%'),(305,57,NULL,'95%'),(306,58,NULL,'$HOSTNAME$'),(307,59,NULL,'$HOSTNAME$'),(308,59,NULL,'_Total'),(309,59,NULL,'0.80'),(310,59,NULL,'0.90'),(311,60,NULL,'$HOSTNAME$'),(312,60,NULL,'30'),(313,61,NULL,'$HOSTNAME$'),(314,61,NULL,'30'),(315,62,NULL,'$HOSTNAME$'),(316,63,NULL,'80'),(317,63,NULL,'90'),(318,63,NULL,'5m'),(319,63,NULL,'10s'),(320,63,NULL,'2'),(321,64,NULL,'80'),(322,64,NULL,'90'),(323,64,NULL,'5m'),(324,64,NULL,'10s'),(325,64,NULL,'2'),(326,65,NULL,'10%'),(327,65,NULL,'5%'),(328,66,NULL,'90%'),(329,66,NULL,'95%'),(330,67,NULL,'$HOSTNAME$'),(331,68,NULL,'$HOSTNAME$'),(332,68,NULL,'_Total'),(333,68,NULL,'0.80'),(334,68,NULL,'0.90'),(335,69,NULL,'$HOSTNAME$'),(336,69,NULL,'30'),(337,70,NULL,'$HOSTNAME$'),(338,70,NULL,'30'),(339,71,NULL,'$HOSTNAME$'),(340,72,NULL,'80'),(341,72,NULL,'90'),(342,72,NULL,'5m'),(343,72,NULL,'10s'),(344,72,NULL,'2'),(345,73,NULL,'80'),(346,73,NULL,'90'),(347,73,NULL,'5m'),(348,73,NULL,'10s'),(349,73,NULL,'2'),(350,74,NULL,'10%'),(351,74,NULL,'5%'),(352,75,NULL,'90%'),(353,75,NULL,'95%'),(354,76,NULL,'$HOSTNAME$'),(355,77,NULL,'$HOSTNAME$'),(356,77,NULL,'_Total'),(357,77,NULL,'0.80'),(358,77,NULL,'0.90'),(359,78,NULL,'$HOSTNAME$'),(360,78,NULL,'30'),(361,79,NULL,'$HOSTNAME$'),(362,79,NULL,'30'),(363,80,NULL,'$HOSTNAME$'),(364,81,NULL,'80'),(365,81,NULL,'90'),(366,81,NULL,'5m'),(367,81,NULL,'10s'),(368,81,NULL,'2'),(369,82,NULL,'80'),(370,82,NULL,'90'),(371,82,NULL,'5m'),(372,82,NULL,'10s'),(373,82,NULL,'2'),(374,83,NULL,'10%'),(375,83,NULL,'5%'),(376,84,NULL,'90%'),(377,84,NULL,'95%'),(378,85,NULL,'$HOSTNAME$'),(379,86,NULL,'$HOSTNAME$'),(380,86,NULL,'_Total'),(381,86,NULL,'0.80'),(382,86,NULL,'0.90'),(383,87,NULL,'$HOSTNAME$'),(384,87,NULL,'30'),(385,88,NULL,'$HOSTNAME$'),(386,88,NULL,'30'),(387,89,NULL,'$HOSTNAME$'),(388,90,NULL,'80'),(389,90,NULL,'90'),(390,90,NULL,'5m'),(391,90,NULL,'10s'),(392,90,NULL,'2'),(393,91,NULL,'80'),(394,91,NULL,'90'),(395,91,NULL,'5m'),(396,91,NULL,'10s'),(397,91,NULL,'2'),(398,92,NULL,'10%'),(399,92,NULL,'5%'),(400,93,NULL,'90%'),(401,93,NULL,'95%'),(402,94,NULL,'$HOSTNAME$'),(403,95,NULL,'$HOSTNAME$'),(404,95,NULL,'_Total'),(405,95,NULL,'0.80'),(406,95,NULL,'0.90'),(407,96,NULL,'$HOSTNAME$'),(408,96,NULL,'30'),(409,97,NULL,'$HOSTNAME$'),(410,97,NULL,'30'),(411,98,NULL,'$HOSTNAME$'),(412,99,NULL,'80'),(413,99,NULL,'90'),(414,99,NULL,'5m'),(415,99,NULL,'10s'),(416,99,NULL,'2'),(417,100,NULL,'80'),(418,100,NULL,'90'),(419,100,NULL,'5m'),(420,100,NULL,'10s'),(421,100,NULL,'2'),(422,101,NULL,'10%'),(423,101,NULL,'5%'),(424,102,NULL,'90%'),(425,102,NULL,'95%'),(426,103,NULL,'$HOSTNAME$'),(427,104,NULL,'$HOSTNAME$'),(428,104,NULL,'_Total'),(429,104,NULL,'0.80'),(430,104,NULL,'0.90'),(431,105,NULL,'$HOSTNAME$'),(432,105,NULL,'30'),(433,106,NULL,'$HOSTNAME$'),(434,106,NULL,'30'),(435,107,NULL,'$HOSTNAME$'),(436,108,NULL,'80'),(437,108,NULL,'90'),(438,108,NULL,'5m'),(439,108,NULL,'10s'),(440,108,NULL,'2'),(441,109,NULL,'80'),(442,109,NULL,'90'),(443,109,NULL,'5m'),(444,109,NULL,'10s'),(445,109,NULL,'2'),(446,110,NULL,'10%'),(447,110,NULL,'5%'),(448,111,NULL,'90%'),(449,111,NULL,'95%'),(450,112,NULL,'$HOSTNAME$'),(451,113,NULL,'$HOSTNAME$'),(452,113,NULL,'_Total'),(453,113,NULL,'0.80'),(454,113,NULL,'0.90'),(455,114,NULL,'$HOSTNAME$'),(456,114,NULL,'30'),(457,115,NULL,'$HOSTNAME$'),(458,115,NULL,'30'),(459,116,NULL,'$HOSTNAME$'),(460,117,NULL,'80'),(461,117,NULL,'90'),(462,117,NULL,'5m'),(463,117,NULL,'10s'),(464,117,NULL,'2'),(465,118,NULL,'80'),(466,118,NULL,'90'),(467,118,NULL,'5m'),(468,118,NULL,'10s'),(469,118,NULL,'2'),(470,119,NULL,'10%'),(471,119,NULL,'5%'),(472,120,NULL,'90%'),(473,120,NULL,'95%'),(474,121,NULL,'$HOSTNAME$'),(475,122,NULL,'$HOSTNAME$'),(476,122,NULL,'_Total'),(477,122,NULL,'0.80'),(478,122,NULL,'0.90'),(479,123,NULL,'$HOSTNAME$'),(480,123,NULL,'30'),(481,124,NULL,'$HOSTNAME$'),(482,124,NULL,'30'),(483,125,NULL,'$HOSTNAME$'),(484,126,NULL,'80'),(485,126,NULL,'90'),(486,126,NULL,'5m'),(487,126,NULL,'10s'),(488,126,NULL,'2'),(489,127,NULL,'80'),(490,127,NULL,'90'),(491,127,NULL,'5m'),(492,127,NULL,'10s'),(493,127,NULL,'2'),(494,128,NULL,'10%'),(495,128,NULL,'5%'),(496,129,NULL,'90%'),(497,129,NULL,'95%'),(498,130,NULL,'$HOSTNAME$'),(499,131,NULL,'$HOSTNAME$'),(500,131,NULL,'_Total'),(501,131,NULL,'0.80'),(502,131,NULL,'0.90'),(503,132,NULL,'$HOSTNAME$'),(504,132,NULL,'30'),(505,133,NULL,'$HOSTNAME$'),(506,133,NULL,'30'),(507,134,NULL,'$HOSTNAME$'),(508,135,NULL,'80'),(509,135,NULL,'90'),(510,135,NULL,'5m'),(511,135,NULL,'10s'),(512,135,NULL,'2'),(513,136,NULL,'80'),(514,136,NULL,'90'),(515,136,NULL,'5m'),(516,136,NULL,'10s'),(517,136,NULL,'2'),(518,137,NULL,'10%'),(519,137,NULL,'5%'),(520,138,NULL,'90%'),(521,138,NULL,'95%'),(522,139,NULL,'$HOSTNAME$'),(523,140,NULL,'$HOSTNAME$'),(524,140,NULL,'_Total'),(525,140,NULL,'0.80'),(526,140,NULL,'0.90'),(527,141,NULL,'$HOSTNAME$'),(528,141,NULL,'30'),(529,142,NULL,'$HOSTNAME$'),(530,142,NULL,'30'),(531,143,NULL,'$HOSTNAME$'),(532,144,NULL,'80'),(533,144,NULL,'90'),(534,144,NULL,'5m'),(535,144,NULL,'10s'),(536,144,NULL,'2'),(537,145,NULL,'80'),(538,145,NULL,'90'),(539,145,NULL,'5m'),(540,145,NULL,'10s'),(541,145,NULL,'2'),(542,146,NULL,'10%'),(543,146,NULL,'5%'),(544,147,NULL,'90%'),(545,147,NULL,'95%'),(546,148,NULL,'$HOSTNAME$'),(547,149,NULL,'$HOSTNAME$'),(548,149,NULL,'_Total'),(549,149,NULL,'0.80'),(550,149,NULL,'0.90'),(551,150,NULL,'$HOSTNAME$'),(552,150,NULL,'30'),(553,151,NULL,'$HOSTNAME$'),(554,151,NULL,'30'),(555,152,NULL,'$HOSTNAME$'),(556,153,NULL,'80'),(557,153,NULL,'90'),(558,153,NULL,'5m'),(559,153,NULL,'10s'),(560,153,NULL,'2'),(561,154,NULL,'80'),(562,154,NULL,'90'),(563,154,NULL,'5m'),(564,154,NULL,'10s'),(565,154,NULL,'2'),(566,155,NULL,'10%'),(567,155,NULL,'5%'),(568,156,NULL,'90%'),(569,156,NULL,'95%'),(570,157,NULL,'$HOSTNAME$'),(571,158,NULL,'$HOSTNAME$'),(572,158,NULL,'_Total'),(573,158,NULL,'0.80'),(574,158,NULL,'0.90'),(575,159,NULL,'$HOSTNAME$'),(576,159,NULL,'30'),(577,160,NULL,'$HOSTNAME$'),(578,160,NULL,'30'),(579,161,NULL,'$HOSTNAME$'),(580,162,NULL,'80'),(581,162,NULL,'90'),(582,162,NULL,'5m'),(583,162,NULL,'10s'),(584,162,NULL,'2'),(585,163,NULL,'80'),(586,163,NULL,'90'),(587,163,NULL,'5m'),(588,163,NULL,'10s'),(589,163,NULL,'2'),(590,164,NULL,'10%'),(591,164,NULL,'5%'),(592,165,NULL,'90%'),(593,165,NULL,'95%'),(594,166,NULL,'$HOSTNAME$'),(595,167,NULL,'$HOSTNAME$'),(596,167,NULL,'_Total'),(597,167,NULL,'0.80'),(598,167,NULL,'0.90'),(599,168,NULL,'$HOSTNAME$'),(600,168,NULL,'30'),(601,169,NULL,'$HOSTNAME$'),(602,169,NULL,'30'),(603,170,NULL,'$HOSTNAME$'),(604,171,NULL,'80'),(605,171,NULL,'90'),(606,171,NULL,'5m'),(607,171,NULL,'10s'),(608,171,NULL,'2'),(609,172,NULL,'80'),(610,172,NULL,'90'),(611,172,NULL,'5m'),(612,172,NULL,'10s'),(613,172,NULL,'2'),(614,173,NULL,'10%'),(615,173,NULL,'5%'),(616,174,NULL,'90%'),(617,174,NULL,'95%'),(618,175,NULL,'$HOSTNAME$'),(619,176,NULL,'$HOSTNAME$'),(620,176,NULL,'_Total'),(621,176,NULL,'0.80'),(622,176,NULL,'0.90'),(623,177,NULL,'$HOSTNAME$'),(624,177,NULL,'30'),(625,178,NULL,'$HOSTNAME$'),(626,178,NULL,'30'),(627,179,NULL,'$HOSTNAME$'),(628,180,NULL,'80'),(629,180,NULL,'90'),(630,180,NULL,'5m'),(631,180,NULL,'10s'),(632,180,NULL,'2'),(633,181,NULL,'80'),(634,181,NULL,'90'),(635,181,NULL,'5m'),(636,181,NULL,'10s'),(637,181,NULL,'2'),(638,182,NULL,'10%'),(639,182,NULL,'5%'),(640,183,NULL,'90%'),(641,183,NULL,'95%'),(642,184,NULL,'$HOSTNAME$'),(643,185,NULL,'$HOSTNAME$'),(644,185,NULL,'_Total'),(645,185,NULL,'0.80'),(646,185,NULL,'0.90'),(647,186,NULL,'$HOSTNAME$'),(648,186,NULL,'30'),(649,187,NULL,'$HOSTNAME$'),(650,187,NULL,'30'),(651,188,NULL,'$HOSTNAME$'),(652,189,NULL,'80'),(653,189,NULL,'90'),(654,189,NULL,'5m'),(655,189,NULL,'10s'),(656,189,NULL,'2'),(657,190,NULL,'80'),(658,190,NULL,'90'),(659,190,NULL,'5m'),(660,190,NULL,'10s'),(661,190,NULL,'2'),(662,191,NULL,'10%'),(663,191,NULL,'5%'),(664,192,NULL,'90%'),(665,192,NULL,'95%'),(666,193,NULL,'$HOSTNAME$'),(667,194,NULL,'$HOSTNAME$'),(668,194,NULL,'_Total'),(669,194,NULL,'0.80'),(670,194,NULL,'0.90'),(671,195,NULL,'$HOSTNAME$'),(672,195,NULL,'30'),(673,196,NULL,'$HOSTNAME$'),(674,196,NULL,'30'),(675,197,NULL,'$HOSTNAME$'),(676,198,NULL,'80'),(677,198,NULL,'90'),(678,198,NULL,'5m'),(679,198,NULL,'10s'),(680,198,NULL,'2'),(681,199,NULL,'80'),(682,199,NULL,'90'),(683,199,NULL,'5m'),(684,199,NULL,'10s'),(685,199,NULL,'2'),(686,200,NULL,'10%'),(687,200,NULL,'5%'),(688,201,NULL,'90%'),(689,201,NULL,'95%'),(690,202,NULL,'$HOSTNAME$'),(691,203,NULL,'$HOSTNAME$'),(692,203,NULL,'_Total'),(693,203,NULL,'0.80'),(694,203,NULL,'0.90'),(695,204,NULL,'$HOSTNAME$'),(696,204,NULL,'30'),(697,205,NULL,'$HOSTNAME$'),(698,205,NULL,'30'),(699,206,NULL,'$HOSTNAME$'),(700,207,NULL,'80'),(701,207,NULL,'90'),(702,207,NULL,'5m'),(703,207,NULL,'10s'),(704,207,NULL,'2'),(705,208,NULL,'80'),(706,208,NULL,'90'),(707,208,NULL,'5m'),(708,208,NULL,'10s'),(709,208,NULL,'2'),(710,209,NULL,'10%'),(711,209,NULL,'5%'),(712,210,NULL,'90%'),(713,210,NULL,'95%'),(714,211,NULL,'$HOSTNAME$'),(715,212,NULL,'$HOSTNAME$'),(716,212,NULL,'_Total'),(717,212,NULL,'0.80'),(718,212,NULL,'0.90'),(719,213,NULL,'$HOSTNAME$'),(720,213,NULL,'30'),(721,214,NULL,'$HOSTNAME$'),(722,214,NULL,'30'),(723,215,NULL,'$HOSTNAME$'),(724,216,NULL,'80'),(725,216,NULL,'90'),(726,216,NULL,'5m'),(727,216,NULL,'10s'),(728,216,NULL,'2'),(729,217,NULL,'80'),(730,217,NULL,'90'),(731,217,NULL,'5m'),(732,217,NULL,'10s'),(733,217,NULL,'2'),(734,218,NULL,'10%'),(735,218,NULL,'5%'),(736,219,NULL,'90%'),(737,219,NULL,'95%'),(738,220,NULL,'$HOSTNAME$'),(739,221,NULL,'$HOSTNAME$'),(740,221,NULL,'_Total'),(741,221,NULL,'0.80'),(742,221,NULL,'0.90'),(743,222,NULL,'$HOSTNAME$'),(744,222,NULL,'30'),(745,223,NULL,'$HOSTNAME$'),(746,223,NULL,'30'),(747,224,NULL,'$HOSTNAME$'),(748,225,NULL,'80'),(749,225,NULL,'90'),(750,225,NULL,'5m'),(751,225,NULL,'10s'),(752,225,NULL,'2'),(753,226,NULL,'80'),(754,226,NULL,'90'),(755,226,NULL,'5m'),(756,226,NULL,'10s'),(757,226,NULL,'2'),(758,227,NULL,'10%'),(759,227,NULL,'5%'),(760,228,NULL,'90%'),(761,228,NULL,'95%'),(762,229,NULL,'$HOSTNAME$'),(763,230,NULL,'$HOSTNAME$'),(764,230,NULL,'_Total'),(765,230,NULL,'0.80'),(766,230,NULL,'0.90'),(767,231,NULL,'$HOSTNAME$'),(768,231,NULL,'30'),(769,232,NULL,'$HOSTNAME$'),(770,232,NULL,'30'),(771,233,NULL,'$HOSTNAME$'),(772,234,NULL,'80'),(773,234,NULL,'90'),(774,234,NULL,'5m'),(775,234,NULL,'10s'),(776,234,NULL,'2'),(777,235,NULL,'80'),(778,235,NULL,'90'),(779,235,NULL,'5m'),(780,235,NULL,'10s'),(781,235,NULL,'2'),(782,236,NULL,'10%'),(783,236,NULL,'5%'),(784,237,NULL,'90%'),(785,237,NULL,'95%'),(786,238,NULL,'$HOSTNAME$'),(787,239,NULL,'$HOSTNAME$'),(788,239,NULL,'_Total'),(789,239,NULL,'0.80'),(790,239,NULL,'0.90'),(791,240,NULL,'$HOSTNAME$'),(792,240,NULL,'30'),(793,241,NULL,'$HOSTNAME$'),(794,241,NULL,'30'),(795,242,NULL,'$HOSTNAME$'),(796,243,NULL,'80'),(797,243,NULL,'90'),(798,243,NULL,'5m'),(799,243,NULL,'10s'),(800,243,NULL,'2'),(801,244,NULL,'80'),(802,244,NULL,'90'),(803,244,NULL,'5m'),(804,244,NULL,'10s'),(805,244,NULL,'2'),(806,245,NULL,'10%'),(807,245,NULL,'5%'),(808,246,NULL,'90%'),(809,246,NULL,'95%'),(810,247,NULL,'$HOSTNAME$'),(811,248,NULL,'$HOSTNAME$'),(812,248,NULL,'_Total'),(813,248,NULL,'0.80'),(814,248,NULL,'0.90'),(815,249,NULL,'$HOSTNAME$'),(816,249,NULL,'30'),(817,250,NULL,'$HOSTNAME$'),(818,250,NULL,'30'),(819,251,NULL,'$HOSTNAME$'),(820,252,NULL,'80'),(821,252,NULL,'90'),(822,252,NULL,'5m'),(823,252,NULL,'10s'),(824,252,NULL,'2'),(825,253,NULL,'80'),(826,253,NULL,'90'),(827,253,NULL,'5m'),(828,253,NULL,'10s'),(829,253,NULL,'2'),(830,254,NULL,'10%'),(831,254,NULL,'5%'),(832,255,NULL,'90%'),(833,255,NULL,'95%'),(834,256,NULL,'$HOSTNAME$'),(835,257,NULL,'$HOSTNAME$'),(836,257,NULL,'_Total'),(837,257,NULL,'0.80'),(838,257,NULL,'0.90'),(839,258,NULL,'$HOSTNAME$'),(840,258,NULL,'30'),(841,259,NULL,'$HOSTNAME$'),(842,259,NULL,'30'),(843,260,NULL,'$HOSTNAME$'),(844,261,NULL,'80'),(845,261,NULL,'90'),(846,261,NULL,'5m'),(847,261,NULL,'10s'),(848,261,NULL,'2'),(849,262,NULL,'80'),(850,262,NULL,'90'),(851,262,NULL,'5m'),(852,262,NULL,'10s'),(853,262,NULL,'2'),(854,263,NULL,'10%'),(855,263,NULL,'5%'),(856,264,NULL,'90%'),(857,264,NULL,'95%'),(858,265,NULL,'$HOSTNAME$'),(859,266,NULL,'$HOSTNAME$'),(860,266,NULL,'_Total'),(861,266,NULL,'0.80'),(862,266,NULL,'0.90'),(863,267,NULL,'$HOSTNAME$'),(864,267,NULL,'30'),(865,268,NULL,'$HOSTNAME$'),(866,268,NULL,'30'),(867,269,NULL,'$HOSTNAME$'),(868,270,NULL,'80'),(869,270,NULL,'90'),(870,270,NULL,'5m'),(871,270,NULL,'10s'),(872,270,NULL,'2'),(873,271,NULL,'80'),(874,271,NULL,'90'),(875,271,NULL,'5m'),(876,271,NULL,'10s'),(877,271,NULL,'2'),(878,272,NULL,'10%'),(879,272,NULL,'5%'),(880,273,NULL,'90%'),(881,273,NULL,'95%'),(882,274,NULL,'$HOSTNAME$'),(883,275,NULL,'$HOSTNAME$'),(884,275,NULL,'_Total'),(885,275,NULL,'0.80'),(886,275,NULL,'0.90'),(887,276,NULL,'$HOSTNAME$'),(888,276,NULL,'30'),(889,277,NULL,'$HOSTNAME$'),(890,277,NULL,'30'),(891,278,NULL,'$HOSTNAME$'),(892,279,NULL,'80'),(893,279,NULL,'90'),(894,279,NULL,'5m'),(895,279,NULL,'10s'),(896,279,NULL,'2'),(897,280,NULL,'80'),(898,280,NULL,'90'),(899,280,NULL,'5m'),(900,280,NULL,'10s'),(901,280,NULL,'2'),(902,281,NULL,'10%'),(903,281,NULL,'5%'),(904,282,NULL,'90%'),(905,282,NULL,'95%'),(906,283,NULL,'$HOSTNAME$'),(907,284,NULL,'$HOSTNAME$'),(908,284,NULL,'_Total'),(909,284,NULL,'0.80'),(910,284,NULL,'0.90'),(911,285,NULL,'$HOSTNAME$'),(912,285,NULL,'30'),(913,286,NULL,'$HOSTNAME$'),(914,286,NULL,'30'),(915,287,NULL,'$HOSTNAME$'),(916,288,NULL,'80'),(917,288,NULL,'90'),(918,288,NULL,'5m'),(919,288,NULL,'10s'),(920,288,NULL,'2'),(921,289,NULL,'80'),(922,289,NULL,'90'),(923,289,NULL,'5m'),(924,289,NULL,'10s'),(925,289,NULL,'2'),(926,290,NULL,'10%'),(927,290,NULL,'5%'),(928,291,NULL,'90%'),(929,291,NULL,'95%'),(930,292,NULL,'$HOSTNAME$'),(931,293,NULL,'$HOSTNAME$'),(932,293,NULL,'_Total'),(933,293,NULL,'0.80'),(934,293,NULL,'0.90'),(935,294,NULL,'$HOSTNAME$'),(936,294,NULL,'30'),(937,295,NULL,'$HOSTNAME$'),(938,295,NULL,'30'),(939,296,NULL,'$HOSTNAME$'),(940,297,NULL,'80'),(941,297,NULL,'90'),(942,297,NULL,'5m'),(943,297,NULL,'10s'),(944,297,NULL,'2'),(945,298,NULL,'80'),(946,298,NULL,'90'),(947,298,NULL,'5m'),(948,298,NULL,'10s'),(949,298,NULL,'2'),(950,299,NULL,'10%'),(951,299,NULL,'5%'),(952,300,NULL,'90%'),(953,300,NULL,'95%'),(954,301,NULL,'$HOSTNAME$'),(955,302,NULL,'$HOSTNAME$'),(956,302,NULL,'_Total'),(957,302,NULL,'0.80'),(958,302,NULL,'0.90'),(959,303,NULL,'$HOSTNAME$'),(960,303,NULL,'30'),(961,304,NULL,'$HOSTNAME$'),(962,304,NULL,'30'),(963,305,NULL,'$HOSTNAME$'),(964,306,NULL,'80'),(965,306,NULL,'90'),(966,306,NULL,'5m'),(967,306,NULL,'10s'),(968,306,NULL,'2'),(969,307,NULL,'80'),(970,307,NULL,'90'),(971,307,NULL,'5m'),(972,307,NULL,'10s'),(973,307,NULL,'2'),(974,308,NULL,'10%'),(975,308,NULL,'5%'),(976,309,NULL,'90%'),(977,309,NULL,'95%'),(978,310,NULL,'$HOSTNAME$'),(979,311,NULL,'$HOSTNAME$'),(980,311,NULL,'_Total'),(981,311,NULL,'0.80'),(982,311,NULL,'0.90'),(983,312,NULL,'$HOSTNAME$'),(984,312,NULL,'30'),(985,313,NULL,'$HOSTNAME$'),(986,313,NULL,'30'),(987,314,NULL,'$HOSTNAME$'),(988,315,NULL,'80'),(989,315,NULL,'90'),(990,315,NULL,'5m'),(991,315,NULL,'10s'),(992,315,NULL,'2'),(993,316,NULL,'80'),(994,316,NULL,'90'),(995,316,NULL,'5m'),(996,316,NULL,'10s'),(997,316,NULL,'2'),(998,317,NULL,'10%'),(999,317,NULL,'5%'),(1000,318,NULL,'90%'),(1001,318,NULL,'95%'),(1002,319,NULL,'$HOSTNAME$'),(1003,320,NULL,'$HOSTNAME$'),(1004,320,NULL,'_Total'),(1005,320,NULL,'0.80'),(1006,320,NULL,'0.90'),(1007,321,NULL,'$HOSTNAME$'),(1008,321,NULL,'30'),(1009,322,NULL,'$HOSTNAME$'),(1010,322,NULL,'30'),(1011,323,NULL,'$HOSTNAME$'),(1012,324,NULL,'80'),(1013,324,NULL,'90'),(1014,324,NULL,'5m'),(1015,324,NULL,'10s'),(1016,324,NULL,'2'),(1017,325,NULL,'80'),(1018,325,NULL,'90'),(1019,325,NULL,'5m'),(1020,325,NULL,'10s'),(1021,325,NULL,'2'),(1022,326,NULL,'10%'),(1023,326,NULL,'5%'),(1024,327,NULL,'90%'),(1025,327,NULL,'95%'),(1026,328,NULL,'$HOSTNAME$'),(1027,329,NULL,'$HOSTNAME$'),(1028,329,NULL,'_Total'),(1029,329,NULL,'0.80'),(1030,329,NULL,'0.90'),(1031,330,NULL,'$HOSTNAME$'),(1032,330,NULL,'30'),(1033,331,NULL,'$HOSTNAME$'),(1034,331,NULL,'30'),(1035,332,NULL,'$HOSTNAME$'),(1036,333,NULL,'80'),(1037,333,NULL,'90'),(1038,333,NULL,'5m'),(1039,333,NULL,'10s'),(1040,333,NULL,'2'),(1041,334,NULL,'80'),(1042,334,NULL,'90'),(1043,334,NULL,'5m'),(1044,334,NULL,'10s'),(1045,334,NULL,'2'),(1046,335,NULL,'10%'),(1047,335,NULL,'5%'),(1048,336,NULL,'90%'),(1049,336,NULL,'95%'),(1050,337,NULL,'$HOSTNAME$'),(1051,338,NULL,'$HOSTNAME$'),(1052,338,NULL,'_Total'),(1053,338,NULL,'0.80'),(1054,338,NULL,'0.90'),(1055,339,NULL,'$HOSTNAME$'),(1056,339,NULL,'30'),(1057,340,NULL,'$HOSTNAME$'),(1058,340,NULL,'30'),(1059,341,NULL,'$HOSTNAME$'),(1060,342,NULL,'80'),(1061,342,NULL,'90'),(1062,342,NULL,'5m'),(1063,342,NULL,'10s'),(1064,342,NULL,'2'),(1065,343,NULL,'80'),(1066,343,NULL,'90'),(1067,343,NULL,'5m'),(1068,343,NULL,'10s'),(1069,343,NULL,'2'),(1070,344,NULL,'10%'),(1071,344,NULL,'5%'),(1072,345,NULL,'90%'),(1073,345,NULL,'95%'),(1074,346,NULL,'$HOSTNAME$'),(1075,347,NULL,'$HOSTNAME$'),(1076,347,NULL,'_Total'),(1077,347,NULL,'0.80'),(1078,347,NULL,'0.90'),(1079,348,NULL,'$HOSTNAME$'),(1080,348,NULL,'30'),(1081,349,NULL,'$HOSTNAME$'),(1082,349,NULL,'30'),(1083,350,NULL,'$HOSTNAME$'),(1084,351,NULL,'80'),(1085,351,NULL,'90'),(1086,351,NULL,'5m'),(1087,351,NULL,'10s'),(1088,351,NULL,'2'),(1089,352,NULL,'80'),(1090,352,NULL,'90'),(1091,352,NULL,'5m'),(1092,352,NULL,'10s'),(1093,352,NULL,'2'),(1094,353,NULL,'10%'),(1095,353,NULL,'5%'),(1096,354,NULL,'90%'),(1097,354,NULL,'95%'),(1098,355,NULL,'$HOSTNAME$'),(1099,356,NULL,'$HOSTNAME$'),(1100,356,NULL,'_Total'),(1101,356,NULL,'0.80'),(1102,356,NULL,'0.90'),(1103,357,NULL,'$HOSTNAME$'),(1104,357,NULL,'30'),(1105,358,NULL,'$HOSTNAME$'),(1106,358,NULL,'30'),(1107,359,NULL,'$HOSTNAME$'),(1108,360,NULL,'80'),(1109,360,NULL,'90'),(1110,360,NULL,'5m'),(1111,360,NULL,'10s'),(1112,360,NULL,'2'),(1113,361,NULL,'80'),(1114,361,NULL,'90'),(1115,361,NULL,'5m'),(1116,361,NULL,'10s'),(1117,361,NULL,'2'),(1118,362,NULL,'10%'),(1119,362,NULL,'5%'),(1120,363,NULL,'90%'),(1121,363,NULL,'95%'),(1122,364,NULL,'$HOSTNAME$'),(1123,365,NULL,'$HOSTNAME$'),(1124,365,NULL,'_Total'),(1125,365,NULL,'0.80'),(1126,365,NULL,'0.90'),(1127,366,NULL,'$HOSTNAME$'),(1128,366,NULL,'30'),(1129,367,NULL,'$HOSTNAME$'),(1130,367,NULL,'30'),(1131,368,NULL,'$HOSTNAME$'),(1132,369,NULL,'80'),(1133,369,NULL,'90'),(1134,369,NULL,'5m'),(1135,369,NULL,'10s'),(1136,369,NULL,'2'),(1137,370,NULL,'80'),(1138,370,NULL,'90'),(1139,370,NULL,'5m'),(1140,370,NULL,'10s'),(1141,370,NULL,'2'),(1142,371,NULL,'10%'),(1143,371,NULL,'5%'),(1144,372,NULL,'90%'),(1145,372,NULL,'95%'),(1146,373,NULL,'$HOSTNAME$'),(1147,374,NULL,'$HOSTNAME$'),(1148,374,NULL,'_Total'),(1149,374,NULL,'0.80'),(1150,374,NULL,'0.90'),(1151,375,NULL,'$HOSTNAME$'),(1152,375,NULL,'30'),(1153,376,NULL,'$HOSTNAME$'),(1154,376,NULL,'30'),(1155,377,NULL,'$HOSTNAME$'),(1156,378,NULL,'80'),(1157,378,NULL,'90'),(1158,378,NULL,'5m'),(1159,378,NULL,'10s'),(1160,378,NULL,'2'),(1161,379,NULL,'80'),(1162,379,NULL,'90'),(1163,379,NULL,'5m'),(1164,379,NULL,'10s'),(1165,379,NULL,'2'),(1166,380,NULL,'10%'),(1167,380,NULL,'5%'),(1168,381,NULL,'90%'),(1169,381,NULL,'95%'),(1170,382,NULL,'$HOSTNAME$'),(1171,383,NULL,'$HOSTNAME$'),(1172,383,NULL,'_Total'),(1173,383,NULL,'0.80'),(1174,383,NULL,'0.90'),(1175,384,NULL,'$HOSTNAME$'),(1176,384,NULL,'30'),(1177,385,NULL,'$HOSTNAME$'),(1178,385,NULL,'30'),(1179,386,NULL,'$HOSTNAME$'),(1180,387,NULL,'80'),(1181,387,NULL,'90'),(1182,387,NULL,'5m'),(1183,387,NULL,'10s'),(1184,387,NULL,'2'),(1185,388,NULL,'80'),(1186,388,NULL,'90'),(1187,388,NULL,'5m'),(1188,388,NULL,'10s'),(1189,388,NULL,'2'),(1190,389,NULL,'10%'),(1191,389,NULL,'5%'),(1192,390,NULL,'90%'),(1193,390,NULL,'95%'),(1194,391,NULL,'$HOSTNAME$'),(1195,392,NULL,'$HOSTNAME$'),(1196,392,NULL,'_Total'),(1197,392,NULL,'0.80'),(1198,392,NULL,'0.90'),(1199,393,NULL,'$HOSTNAME$'),(1200,393,NULL,'30'),(1201,394,NULL,'$HOSTNAME$'),(1202,394,NULL,'30'),(1203,395,NULL,'$HOSTNAME$'),(1204,396,NULL,'80'),(1205,396,NULL,'90'),(1206,396,NULL,'5m'),(1207,396,NULL,'10s'),(1208,396,NULL,'2'),(1209,397,NULL,'80'),(1210,397,NULL,'90'),(1211,397,NULL,'5m'),(1212,397,NULL,'10s'),(1213,397,NULL,'2'),(1214,398,NULL,'10%'),(1215,398,NULL,'5%'),(1216,399,NULL,'90%'),(1217,399,NULL,'95%'),(1218,400,NULL,'$HOSTNAME$'),(1219,401,NULL,'$HOSTNAME$'),(1220,401,NULL,'_Total'),(1221,401,NULL,'0.80'),(1222,401,NULL,'0.90'),(1223,402,NULL,'$HOSTNAME$'),(1224,402,NULL,'30'),(1225,403,NULL,'$HOSTNAME$'),(1226,403,NULL,'30'),(1227,404,NULL,'$HOSTNAME$'),(1228,405,NULL,'80'),(1229,405,NULL,'90'),(1230,405,NULL,'5m'),(1231,405,NULL,'10s'),(1232,405,NULL,'2'),(1233,406,NULL,'80'),(1234,406,NULL,'90'),(1235,406,NULL,'5m'),(1236,406,NULL,'10s'),(1237,406,NULL,'2'),(1238,407,NULL,'10%'),(1239,407,NULL,'5%'),(1240,408,NULL,'90%'),(1241,408,NULL,'95%'),(1242,409,NULL,'$HOSTNAME$'),(1243,410,NULL,'$HOSTNAME$'),(1244,410,NULL,'_Total'),(1245,410,NULL,'0.80'),(1246,410,NULL,'0.90'),(1247,411,NULL,'$HOSTNAME$'),(1248,411,NULL,'30'),(1249,412,NULL,'$HOSTNAME$'),(1250,412,NULL,'30'),(1251,413,NULL,'$HOSTNAME$'),(1252,414,NULL,'80'),(1253,414,NULL,'90'),(1254,414,NULL,'5m'),(1255,414,NULL,'10s'),(1256,414,NULL,'2'),(1257,415,NULL,'80'),(1258,415,NULL,'90'),(1259,415,NULL,'5m'),(1260,415,NULL,'10s'),(1261,415,NULL,'2'),(1262,416,NULL,'10%'),(1263,416,NULL,'5%'),(1264,417,NULL,'90%'),(1265,417,NULL,'95%'),(1266,418,NULL,'$HOSTNAME$'),(1267,419,NULL,'$HOSTNAME$'),(1268,419,NULL,'_Total'),(1269,419,NULL,'0.80'),(1270,419,NULL,'0.90'),(1271,420,NULL,'$HOSTNAME$'),(1272,420,NULL,'30'),(1273,421,NULL,'$HOSTNAME$'),(1274,421,NULL,'30'),(1275,422,NULL,'$HOSTNAME$'),(2284,801,NULL,'80'),(2285,801,NULL,'90'),(2286,801,NULL,'5m'),(2287,801,NULL,'10s'),(2288,801,NULL,'2'),(2289,802,NULL,'80'),(2290,802,NULL,'90'),(2291,802,NULL,'5m'),(2292,802,NULL,'10s'),(2293,802,NULL,'2'),(2294,803,NULL,'10%'),(2295,803,NULL,'5%'),(2296,804,NULL,'90%'),(2297,804,NULL,'95%'),(2298,805,NULL,'$HOSTNAME$'),(2326,813,NULL,'httpd'),(2328,814,NULL,'icinga'),(2330,815,NULL,'ido2db'),(2358,822,NULL,'crond'),(2360,823,NULL,'$HOSTNAME$'),(2361,823,NULL,'80'),(2362,823,NULL,'tcp'),(2368,825,NULL,'$HOSTNAME$'),(2369,825,NULL,'3306'),(2370,825,NULL,'tcp'),(2376,826,NULL,'\"\'MySQL Server\'\"'),(2377,826,NULL,'\"\'mysqld.pid\'\"'),(2384,820,NULL,'\"5%\"'),(2385,820,NULL,'\"/var\"'),(2386,821,NULL,'\"5%\"'),(2387,821,NULL,'\"/var/log\"'),(2388,819,NULL,'\"5%\"'),(2389,819,NULL,'\"/usr\"'),(2390,818,NULL,'\"5%\"'),(2391,818,NULL,'\"/tmp\"'),(2394,827,NULL,'\"\'Sendmail Queue\'\"'),(2395,827,NULL,'\"\'sendmail: Queue\'\"'),(2400,828,NULL,'\"\'Sendmail Service\'\"'),(2401,828,NULL,'\"\'sendmail: accepting connections\'\"'),(2404,6,NULL,'\"20%\"'),(2405,6,NULL,'\"10%\"'),(2406,812,NULL,'\"5%\"'),(2407,812,NULL,'\"/\"'),(2408,816,NULL,'\"5%\"'),(2409,816,NULL,'\"/boot\"'),(2410,817,NULL,'\"5%\"'),(2411,817,NULL,'\"/home\"'),(2412,5,NULL,'\"5.0,4.0,3.0\"'),(2413,5,NULL,'\"10.0,6.0,4.0\"'),(2414,3,NULL,'\"20\"'),(2415,3,NULL,'\"50\"'),(2416,4,NULL,'2000'),(2558,889,NULL,'90'),(2559,889,NULL,'100'),(2561,890,NULL,'50'),(2562,890,NULL,'75');
/*!40000 ALTER TABLE `nagios_services_check_command_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_services_desc`
--

DROP TABLE IF EXISTS `nagios_services_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_services_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_services_desc`
--

LOCK TABLES `nagios_services_desc` WRITE;
/*!40000 ALTER TABLE `nagios_services_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_services_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_services_extended_info`
--

DROP TABLE IF EXISTS `nagios_services_extended_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_services_extended_info` (
  `service_id` int(11) unsigned NOT NULL default '0',
  `notes` text,
  `notes_url` text,
  `action_url` text,
  `icon_image` text,
  `icon_image_alt` text,
  PRIMARY KEY  (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_services_extended_info`
--

LOCK TABLES `nagios_services_extended_info` WRITE;
/*!40000 ALTER TABLE `nagios_services_extended_info` DISABLE KEYS */;
INSERT INTO `nagios_services_extended_info` VALUES (3,NULL,NULL,NULL,NULL,NULL),(4,NULL,NULL,NULL,NULL,NULL),(5,'','','','',''),(6,NULL,NULL,NULL,NULL,NULL),(17,'Check is being done via [gwmi_single_cpu_check] Script.','','','',''),(19,'Check is being done via [gwmi_app_eventlog_check] Script.','','','',''),(20,'Check is being done via [gwmi_sys_eventlog_check] Script.','','','',''),(21,'Check is being done via [gwmi_eventlog_check] Script.','','','',''),(23,'Check is being done via [gwmi_uptime_check] Script.','','','',''),(24,'Check is being done via NSClient Agent','','','',''),(25,'Check is being done via NSClient Agent','','','',''),(26,'Check is being done via NSClient Agent','','','',''),(27,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(28,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(29,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(30,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.','','','',''),(31,'Check is being done via [gwmi_os-version_check] PowerShell Script.','','','',''),(32,'Check is being done via NSClient Agent','','','',''),(33,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(34,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.','','','',''),(35,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.','','','',''),(36,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(37,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(38,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(39,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(40,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(41,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(42,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(43,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(44,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(45,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(46,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(47,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(48,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(49,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(50,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(51,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(52,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(53,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(54,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(55,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(56,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(57,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(58,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(59,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(60,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(61,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(62,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(63,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(64,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(65,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(66,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(67,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(68,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(69,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(70,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(71,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(72,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(73,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(74,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(75,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(76,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(77,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(78,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(79,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(80,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(81,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(82,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(83,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(84,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(85,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(86,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(87,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(88,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(89,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(90,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(91,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(92,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(93,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(94,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(95,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(96,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(97,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(98,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(99,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(100,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(101,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(102,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(103,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(104,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(105,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(106,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(107,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(108,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(109,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(110,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(111,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(112,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(113,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(114,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(115,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(116,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(117,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(118,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(119,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(120,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(121,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(122,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(123,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(124,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(125,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(126,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(127,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(128,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(129,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(130,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(131,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(132,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(133,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(134,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(135,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(136,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(137,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(138,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(139,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(140,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(141,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(142,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(143,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(144,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(145,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(146,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(147,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(148,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(149,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(150,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(151,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(152,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(153,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(154,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(155,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(156,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(157,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(158,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(159,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(160,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(161,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(162,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(163,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(164,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(165,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(166,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(167,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(168,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(169,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(170,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(171,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(172,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(173,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(174,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(175,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(176,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(177,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(178,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(179,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(180,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(181,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(182,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(183,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(184,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(185,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(186,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(187,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(188,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(189,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(190,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(191,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(192,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(193,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(194,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(195,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(196,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(197,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(198,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(199,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(200,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(201,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(202,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(203,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(204,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(205,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(206,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(207,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(208,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(209,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(210,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(211,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(212,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(213,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(214,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(215,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(216,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(217,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(218,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(219,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(220,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(221,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(222,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(223,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(224,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(225,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(226,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(227,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(228,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(229,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(230,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(231,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(232,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(233,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(234,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(235,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(236,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(237,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(238,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(239,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(240,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(241,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(242,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(243,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(244,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(245,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(246,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(247,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(248,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(249,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(250,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(251,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(252,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(253,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(254,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(255,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(256,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(257,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(258,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(259,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(260,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(261,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(262,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(263,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(264,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(265,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(266,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(267,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(268,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(269,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(270,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(271,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(272,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(273,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(274,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(275,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(276,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(277,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(278,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(279,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(280,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(281,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(282,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(283,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(284,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(285,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(286,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(287,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(288,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(289,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(290,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(291,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(292,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(293,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(294,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(295,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(296,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(297,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(298,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(299,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(300,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(301,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(302,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(303,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(304,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(305,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(306,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(307,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(308,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(309,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(310,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(311,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(312,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(313,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(314,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(315,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(316,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(317,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(318,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(319,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(320,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(321,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(322,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(323,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(324,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(325,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(326,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(327,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(328,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(329,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(330,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(331,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(332,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(333,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(334,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(335,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(336,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(337,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(338,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(339,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(340,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(341,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(342,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(343,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(344,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(345,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(346,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(347,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(348,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(349,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(350,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(351,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(352,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(353,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(354,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(355,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(356,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(357,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(358,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(359,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(360,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(361,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(362,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(363,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(364,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(365,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(366,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(367,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(368,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(369,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(370,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(371,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(372,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(373,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(374,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(375,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(376,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(377,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(378,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(379,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(380,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(381,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(382,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(383,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(384,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(385,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(386,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(387,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(388,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(389,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(390,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(391,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(392,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(393,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(394,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(395,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(396,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(397,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(398,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(399,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(400,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(401,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(402,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(403,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(404,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(405,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(406,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(407,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(408,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(409,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(410,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(411,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(412,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(413,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(414,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(415,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(416,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(417,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(418,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(419,'Check is being done via [gwmi_single_cpu_check] Script.',NULL,NULL,NULL,NULL),(420,'Check is being done via [gwmi_app_eventlog_check] Script.',NULL,NULL,NULL,NULL),(421,'Check is being done via [gwmi_sys_eventlog_check] Script.',NULL,NULL,NULL,NULL),(422,'Check is being done via [gwmi_uptime_check] Script.',NULL,NULL,NULL,NULL),(423,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(424,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(425,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(426,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(427,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(428,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(429,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(430,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(431,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(432,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(433,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(434,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(435,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(436,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(437,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(438,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(439,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(440,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(441,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(442,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(443,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(444,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(445,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(446,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(447,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(448,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(449,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(450,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(451,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(452,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(453,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(454,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(455,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(456,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(457,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(458,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(459,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(460,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(461,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(462,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(463,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(464,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(465,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(466,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(467,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(468,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(469,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(470,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(471,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(472,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(473,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(474,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(475,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(476,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(477,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(478,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(479,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(480,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(481,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(482,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(483,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(484,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(485,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(486,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(487,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(488,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(489,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(490,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(491,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(492,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(493,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(494,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(495,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(496,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(497,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(498,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(499,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(500,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(501,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(502,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(503,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(504,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(505,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(506,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(507,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(508,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(509,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(510,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(511,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(512,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(513,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(514,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(515,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(516,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(517,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(518,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(519,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(520,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(521,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(522,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(523,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(524,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(525,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(526,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(527,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(528,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(529,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(530,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(531,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(532,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(533,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(534,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(535,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(536,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(537,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(538,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(539,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(540,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(541,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(542,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(543,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(544,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(545,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(546,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(547,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(548,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(549,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(550,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(551,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(552,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(553,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(554,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(555,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(556,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(557,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(558,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(559,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(560,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(561,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(562,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(563,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(564,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(565,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(566,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(567,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(568,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(569,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(570,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(571,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(572,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(573,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(574,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(575,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(576,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(577,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(578,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(579,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(580,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(581,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(582,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(583,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(584,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(585,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(586,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(587,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(588,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(589,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(590,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(591,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(592,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(593,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(594,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(595,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(596,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(597,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(598,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(599,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(600,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(601,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(602,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(603,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(604,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(605,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(606,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(607,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(608,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(609,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(610,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(611,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(612,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(613,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(614,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(615,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(616,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(617,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(618,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(619,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(620,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(621,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(622,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(623,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(624,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(625,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(626,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(627,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(628,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(629,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(630,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(631,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(632,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(633,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(634,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(635,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(636,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(637,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(638,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(639,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(640,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(641,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(642,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(643,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(644,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(645,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(646,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(647,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(648,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(649,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(650,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(651,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(652,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(653,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(654,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(655,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(656,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(657,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(658,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(659,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(660,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(661,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(662,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(663,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(664,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(665,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(666,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(667,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(668,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(669,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(670,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(671,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(672,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(673,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(674,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(675,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(676,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(677,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(678,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(679,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(680,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(681,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(682,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(683,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(684,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(685,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(686,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(687,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(688,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(689,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(690,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(691,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(692,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(693,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(694,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(695,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(696,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(697,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(698,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(699,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(700,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(701,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(702,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(703,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(704,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(705,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(706,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(707,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(708,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(709,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(710,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(711,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(712,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(713,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(714,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(715,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(716,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(717,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(718,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(719,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(720,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(721,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(722,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(723,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(724,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(725,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(726,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(727,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(728,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(729,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(730,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(731,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(732,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(733,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(734,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(735,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(736,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(737,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(738,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(739,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(740,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(741,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(742,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(743,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(744,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(745,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(746,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(747,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(748,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(749,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(750,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(751,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(752,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(753,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(754,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(755,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(756,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(757,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(758,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(759,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(760,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(761,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(762,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(763,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(764,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(765,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(766,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(767,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(768,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(769,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(770,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(771,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(772,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(773,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(774,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(775,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(776,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(777,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(778,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(779,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(780,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(781,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(782,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(783,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(784,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(785,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(786,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(787,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(788,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(789,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(790,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(791,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(792,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(793,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(794,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(795,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(796,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(797,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(798,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(799,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(800,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(801,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(802,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(803,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(804,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(805,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(812,NULL,NULL,NULL,NULL,NULL),(813,NULL,NULL,NULL,NULL,NULL),(814,NULL,NULL,NULL,NULL,NULL),(815,NULL,NULL,NULL,NULL,NULL),(816,NULL,NULL,NULL,NULL,NULL),(817,NULL,NULL,NULL,NULL,NULL),(818,NULL,NULL,NULL,NULL,NULL),(819,NULL,NULL,NULL,NULL,NULL),(820,NULL,NULL,NULL,NULL,NULL),(821,NULL,NULL,NULL,NULL,NULL),(822,NULL,NULL,NULL,NULL,NULL),(823,NULL,NULL,NULL,NULL,NULL),(825,NULL,NULL,NULL,NULL,NULL),(826,NULL,NULL,NULL,NULL,NULL),(827,NULL,NULL,NULL,NULL,NULL),(828,NULL,NULL,NULL,NULL,NULL),(830,NULL,NULL,NULL,NULL,NULL),(840,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(841,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(842,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(843,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(844,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(845,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(846,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(847,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(848,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(849,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(850,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(851,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(852,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(853,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(854,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(855,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(856,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(857,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(859,'Check is being done via [gwmi_single_cpu_check] PowerShell Script.',NULL,NULL,NULL,NULL),(860,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(861,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(862,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(863,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(864,'Check is being done via [gwmi_os-version_check] PowerShell Script.',NULL,NULL,NULL,NULL),(865,'Check is being done via NSClient Agent',NULL,NULL,NULL,NULL),(866,'Check is being done via [gwmi_app_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(867,'Check is being done via [gwmi_sys_eventlog_check] PowerShell Script.',NULL,NULL,NULL,NULL),(868,NULL,NULL,NULL,NULL,NULL),(869,NULL,NULL,NULL,NULL,NULL),(870,NULL,NULL,NULL,NULL,NULL),(871,NULL,NULL,NULL,NULL,NULL),(872,NULL,NULL,NULL,NULL,NULL),(873,NULL,NULL,NULL,NULL,NULL),(874,NULL,NULL,NULL,NULL,NULL),(875,NULL,NULL,NULL,NULL,NULL),(876,NULL,NULL,NULL,NULL,NULL),(877,NULL,NULL,NULL,NULL,NULL),(878,NULL,NULL,NULL,NULL,NULL),(879,NULL,NULL,NULL,NULL,NULL),(880,NULL,NULL,NULL,NULL,NULL),(881,NULL,NULL,NULL,NULL,NULL),(882,NULL,NULL,NULL,NULL,NULL),(883,NULL,NULL,NULL,NULL,NULL),(884,NULL,NULL,NULL,NULL,NULL),(885,NULL,NULL,NULL,NULL,NULL),(886,NULL,NULL,NULL,NULL,NULL),(887,NULL,NULL,NULL,NULL,NULL),(888,NULL,NULL,NULL,NULL,NULL),(889,NULL,NULL,NULL,NULL,NULL),(890,NULL,NULL,NULL,NULL,NULL),(891,NULL,NULL,NULL,NULL,NULL),(892,NULL,NULL,NULL,NULL,NULL),(893,NULL,NULL,NULL,NULL,NULL),(894,NULL,NULL,NULL,NULL,NULL),(895,NULL,NULL,NULL,NULL,NULL),(896,NULL,NULL,NULL,NULL,NULL),(897,NULL,NULL,NULL,NULL,NULL),(898,NULL,NULL,NULL,NULL,NULL),(899,NULL,NULL,NULL,NULL,NULL),(900,NULL,NULL,NULL,NULL,NULL),(901,NULL,NULL,NULL,NULL,NULL),(902,NULL,NULL,NULL,NULL,NULL),(903,NULL,NULL,NULL,NULL,NULL),(904,NULL,NULL,NULL,NULL,NULL),(905,NULL,NULL,NULL,NULL,NULL),(906,NULL,NULL,NULL,NULL,NULL),(907,NULL,NULL,NULL,NULL,NULL),(908,NULL,NULL,NULL,NULL,NULL),(909,NULL,NULL,NULL,NULL,NULL),(910,NULL,NULL,NULL,NULL,NULL),(911,NULL,NULL,NULL,NULL,NULL),(912,NULL,NULL,NULL,NULL,NULL),(913,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `nagios_services_extended_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_services_extended_info_desc`
--

DROP TABLE IF EXISTS `nagios_services_extended_info_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_services_extended_info_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_services_extended_info_desc`
--

LOCK TABLES `nagios_services_extended_info_desc` WRITE;
/*!40000 ALTER TABLE `nagios_services_extended_info_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_services_extended_info_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_timeperiods`
--

DROP TABLE IF EXISTS `nagios_timeperiods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_timeperiods` (
  `timeperiod_id` int(11) unsigned NOT NULL auto_increment,
  `network_id` int(11) unsigned NOT NULL default '0',
  `timeperiod_name` text NOT NULL,
  `alias` text NOT NULL,
  `sunday` text NOT NULL,
  `monday` text NOT NULL,
  `tuesday` text NOT NULL,
  `wednesday` text NOT NULL,
  `thursday` text NOT NULL,
  `friday` text NOT NULL,
  `saturday` text NOT NULL,
  PRIMARY KEY  (`timeperiod_id`),
  KEY `name` (`timeperiod_name`(128))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='Nagios Time Periods';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_timeperiods`
--

LOCK TABLES `nagios_timeperiods` WRITE;
/*!40000 ALTER TABLE `nagios_timeperiods` DISABLE KEYS */;
INSERT INTO `nagios_timeperiods` VALUES (1,0,'24x7','24 Hours A Day, 7 Days A Week','00:00-24:00','00:00-24:00','00:00-24:00','00:00-24:00','00:00-24:00','00:00-24:00','00:00-24:00'),(2,0,'workhours','Normal Work Hours','','09:00-17:00','09:00-17:00','09:00-17:00','09:00-17:00','09:00-17:00',''),(3,0,'none','No Time Is A Good Time','','','','','','','');
/*!40000 ALTER TABLE `nagios_timeperiods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nagios_timeperiods_desc`
--

DROP TABLE IF EXISTS `nagios_timeperiods_desc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_timeperiods_desc` (
  `field_name` text NOT NULL,
  `field_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nagios_timeperiods_desc`
--

LOCK TABLES `nagios_timeperiods_desc` WRITE;
/*!40000 ALTER TABLE `nagios_timeperiods_desc` DISABLE KEYS */;
/*!40000 ALTER TABLE `nagios_timeperiods_desc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `SESSKEY` varchar(32) NOT NULL default '',
  `EXPIRY` int(11) unsigned NOT NULL default '0',
  `SESSIONDATA` binary(1) NOT NULL default '\0',
  PRIMARY KEY  (`SESSKEY`),
  KEY `EXPIRY` (`EXPIRY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-11-25 22:48:58

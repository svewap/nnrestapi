
#
# Table structure for table 'fe_groups'
#
#CREATE TABLE fe_groups (
#	nnrestapi_flexform text DEFAULT '' NOT NULL
#);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	nnrestapi_apikey varchar(255) DEFAULT '' NOT NULL,
	nnrestapi_admin tinyint(4) unsigned DEFAULT 0 NOT NULL
);

#
# Table structure for table 'nnrestapi_sessions'
#
CREATE TABLE nnrestapi_sessions (
	uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
	token varchar(4096) DEFAULT '' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	data mediumblob,
	vars mediumblob,

	PRIMARY KEY (uid)
);

#
# Table structure for table 'nnrestapi_security'
#
CREATE TABLE nnrestapi_security (
	uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
	identifier varchar(32) DEFAULT '' NOT NULL,
	iphash varchar(32) DEFAULT '' NOT NULL,
	feuser int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	expires int(11) unsigned DEFAULT '0' NOT NULL,
	data mediumblob,

	PRIMARY KEY (uid),
	KEY idtype (identifier),
	KEY ip (iphash)
);

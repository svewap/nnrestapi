
#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
	nnrestapi_flexform text DEFAULT '' NOT NULL
);


#
# Table structure for table 'tx_nnrestapi_domain_model_apitest'
#
CREATE TABLE tx_nnrestapi_domain_model_apitest (
	uid int(11) unsigned DEFAULT 0 NOT NULL auto_increment,
	pid int(11) DEFAULT 0 NOT NULL,

	title varchar(255) NOT NULL,
	image int(11) NOT NULL,
	files int(11) NOT NULL,
	children int(11) NOT NULL,
	child int(11) NOT NULL,
	parentid int(11) NOT NULL,
	parenttable VARCHAR(255) NOT NULL,
	categories int(11) NOT NULL,

	tstamp int(11) unsigned DEFAULT 0 NOT NULL,
	crdate int(11) unsigned DEFAULT 0 NOT NULL,
	starttime int(11) unsigned DEFAULT 0 NOT NULL,
	endtime int(11) unsigned DEFAULT 0 NOT NULL,
	cruser_id int(11) unsigned DEFAULT 0 NOT NULL,
	deleted tinyint(4) unsigned DEFAULT 0 NOT NULL,
	sorting tinyint(4) unsigned DEFAULT 0 NOT NULL,
	hidden tinyint(4) unsigned DEFAULT 0 NOT NULL,
	sys_language_uid int(11) DEFAULT 0 NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT 0 NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
);

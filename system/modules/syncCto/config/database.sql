-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************
 
--
-- Table `tl_synccto_clients`
--

CREATE TABLE `tl_synccto_clients` (
    `id` int(10) unsigned NOT NULL auto_increment,    
    `tstamp` int(10) unsigned NOT NULL default '0',
    `title` varchar(64) NOT NULL default '',
    `apikey` varchar(64) NOT NULL default '',
    `description` text NULL,
    `address` text NOT NULL,
    `path` varchar(255) NOT NULL default '',
    `port` int(10) unsigned NOT NULL default '0',
    `codifyengine` text NOT NULL,
    `cookie` longtext NULL,
    `syncTo_user` int(10) unsigned NOT NULL default '0',
    `syncFrom_user` int(10) unsigned NOT NULL default '0',
    `syncTo_tstamp` int(10) unsigned NOT NULL default '0',
    `syncFrom_tstamp` int(10) unsigned NOT NULL default '0',
    `http_auth` char(1) NOT NULL default '',
    `http_username` varchar(128) NOT NULL default '',
    `http_password` varchar(128) NOT NULL default '',
    `client_timestamp` blob NULL,
    `server_timestamp` blob NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_synccto_stats`
--

CREATE TABLE `tl_synccto_stats` (
    `id` int(10) unsigned NOT NULL auto_increment,    
    `tstamp` int(10) unsigned NOT NULL default '0',
    `client_id` varchar(64) NOT NULL default '',
    `sync_user` varchar(64) NOT NULL default '',
    `sync_start` int(10) unsigned NOT NULL default '0',
    `sync_end` int(10) unsigned NOT NULL default '0',  
    `sync_abort` int(10) unsigned NOT NULL default '0',  
    `sync_abort_step` varchar(10) NOT NULL default '',  
    `sync_direction` int(10) unsigned NOT NULL default '0', 
    `sync_options` blob NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
  `syncCto_clients` blob NULL,
  `syncCto_clients_p` blob NULL,
  `syncCto_sync_options` blob NULL,
  `syncCto_tables` blob NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user_group`
--

CREATE TABLE `tl_user` (
  `syncCto_clients` blob NULL,
  `syncCto_clients_p` blob NULL,
  `syncCto_sync_options` blob NULL,
  `syncCto_tables` blob NULL
  `syncCto_useTranslatedNames` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

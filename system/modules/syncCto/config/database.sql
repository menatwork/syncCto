-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************
 
CREATE TABLE `tl_synccto_clients` (
    `id` int(10) unsigned NOT NULL auto_increment,    
    `tstamp` int(10) unsigned NOT NULL default '0',
    `title` varchar(64) NOT NULL default '',
    `seckey` varchar(64) NOT NULL default '',
    `description` text NULL,
    `address` text NOT NULL,
    `path` varchar(255) NOT NULL default 'syncCto.php',
    `port` text NULL,
    `codifyengine` text NOT NULL,
    `transmission` int(10) unsigned NOT NULL default '0',
    `cookie` longtext NULL,
    `syncTo_user` int(10) unsigned NOT NULL default '0',
    `syncFrom_user` int(10) unsigned NOT NULL default '0',
    `syncTo_tstamp` int(10) unsigned NOT NULL default '0',
    `syncFrom_tstamp` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
  `syncCto_clients` blob NULL,
  `syncCto_clients_p` blob NULL,
  `syncCto_tables` blob NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user_group`
--

CREATE TABLE `tl_user` (
  `syncCto_clients` blob NULL,
  `syncCto_clients_p` blob NULL,
  `syncCto_tables` blob NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
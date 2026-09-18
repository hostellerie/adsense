<?php

/* AdSense Plugin 1.0.0 - MySQL installation */

if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own.');
}

$_SQL[] = "CREATE TABLE {$_TABLES['adsense_placements']} (
  placement_id int(10) unsigned NOT NULL auto_increment,
  name varchar(64) NOT NULL default '',
  slot_id varchar(32) NOT NULL default '',
  format varchar(32) NOT NULL default 'auto',
  responsive tinyint(1) unsigned NOT NULL default '1',
  enabled tinyint(1) unsigned NOT NULL default '1',
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (placement_id),
  UNIQUE KEY placement_name (name)
) ENGINE=MyISAM;";

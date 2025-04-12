<?php
$cfg['Servers'][1]['auth_type'] = 'cookie';  // Forces login screen
$cfg['Servers'][1]['host'] = 'db';  // Matches the Docker database container name
$cfg['Servers'][1]['compress'] = false;
$cfg['Servers'][1]['AllowNoPassword'] = false;  // Requires password login

// Security settings
$cfg['blowfish_secret'] = 'randomsecretkeyhere'; 

// Show advanced features
$cfg['ShowDatabasesNavigationAsTree'] = true;
$cfg['MaxNavigationItems'] = 100;
$cfg['Servers'][1]['controluser'] = 'root';
$cfg['Servers'][1]['controlpass'] = 'root';
$cfg['Servers'][$i]['AllowLocalInfile'] = true;

?>
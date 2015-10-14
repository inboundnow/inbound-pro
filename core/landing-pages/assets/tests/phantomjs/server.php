<?php
//echo shell_exec( '/usr/bin/phantomjs /vagrant/www/wordpress-default/wp-content/plugins/landing-pages/tests/phantomjs/server.js' );
//sleep(10);

//$output = shell_exec('/usr/bin/phantomjs server.js');
//echo "<pre>$output</pre>";
error_reporting(E_ALL);
session_write_close();
$result = shell_exec('phantomjs  --web-security=false --ssl-protocol=any server.js ' . $_GET['url'] ); 
echo $result;
<?php 
ini_set('soap.wsdl_cache_enabled', 0); // Delete cache
ini_set('default_socket_timeout', 180);
class wsServer {
  function getVersion($parm) {
    return 'V1.5.0';
  }
}

try {
  $server = new SoapServer('monFormat.wsdl',  array('trace' => 1,'encoding'    => 'UTF-8'));
  $server -> setclass('wsServer');
  $server->setPersistence(SOAP_PERSISTENCE_REQUEST);
} catch (Exception $e) {
  echo 'WS Error '.$e;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  try {
    $server -> handle();}
  catch (Exception $e) {
    echo 'WS Error '.$e;
  }
} else {
  echo '<strong>This SOAP server can handle following functions : </strong>';
  echo '<ul>';
  foreach($server -> getFunctions() as $func) {
    echo '<li>' , $func , '</li>';
  }
  echo '</ul>';
}
?>
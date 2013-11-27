<?php
/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
$idVersion=$_REQUEST['idVersion'];

$vers=new Version($idVersion);
echo $vers->idProduct;

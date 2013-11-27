<?php
/** ===========================================================================
 * This script stores checkboxes' states
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveCheckboxes.php');
header("Content-Type: text/plain"); 
$toStore = (isset($_REQUEST["toStore"])) ? $_REQUEST["toStore"] : NULL;
$toStore=explode(";",$toStore);
$objClass = (isset($_REQUEST["objectClass"])) ? $_REQUEST["objectClass"] : NULL;
$user=$_SESSION['user'];
$idUser = $user->id;
$obj=new $objClass();
Sql::beginTransaction();
$cs=new ColumnSelector();
$cs->purge("scope='export' and idUser=$idUser and objectClass='$objClass'");
foreach ($toStore as $store) {
	if (trim($store)) {
	  $cs=new ColumnSelector();
		$cs->scope='export';
		$cs->idUser=$idUser;
		$cs->objectClass=$objClass;
		$cs->field=$store;
		$cs->name=$obj->getColCaption($store);
		$cs->hidden=1;
		$res=$cs->save();
	}
}
Sql::commitTransaction();

?>

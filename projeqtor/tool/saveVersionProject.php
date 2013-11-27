<?php
/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveVersionProject.php');
// Get the info
if (! array_key_exists('versionProjectId',$_REQUEST)) {
  throwError('versionProjectId parameter not found in REQUEST');
}
$id=($_REQUEST['versionProjectId']);

if (! array_key_exists('versionProjectProject',$_REQUEST)) {
  throwError('versionProjectProject parameter not found in REQUEST');
}
$project=($_REQUEST['versionProjectProject']);

if (! array_key_exists('versionProjectVersion',$_REQUEST)) {
  throwError('versionProjectVersion parameter not found in REQUEST');
}
$version=($_REQUEST['versionProjectVersion']);

if (! array_key_exists('versionProjectStartDate',$_REQUEST)) {
  throwError('versionProjectStartDate parameter not found in REQUEST');
}
$startDate=($_REQUEST['versionProjectStartDate']);

if (! array_key_exists('versionProjectEndDate',$_REQUEST)) {
  throwError('versionProjectEndDate parameter not found in REQUEST');
}
$endDate=($_REQUEST['versionProjectEndDate']);

$idle=false;
if (array_key_exists('versionProjectIdle',$_REQUEST)) {
  $idle=true;
}
Sql::beginTransaction();
$versionProject=new VersionProject($id);

$versionProject->idProject=$project;
$versionProject->idVersion=$version;
$versionProject->idle=$idle;
$versionProject->startDate=$startDate;
$versionProject->endDate=$endDate;

$result=$versionProject->save();

// Message of correct saving
if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
	Sql::rollbackTransaction();
  echo '<span class="messageERROR" >' . $result . '</span>';
} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	Sql::commitTransaction();
  echo '<span class="messageOK" >' . $result . '</span>';
} else { 
	Sql::rollbackTransaction();
  echo '<span class="messageWARNING" >' . $result . '</span>';
}
?>
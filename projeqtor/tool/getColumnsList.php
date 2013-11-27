<?PHP
/** ===========================================================================
 * Get the list of columns for object
 */
require_once "../tool/projeqtor.php"; 
scriptLog('   ->/tool/getColumnsList.php');
$objectClass=$_REQUEST['objectClass'];
$list=ColumnSelector::getColumnsList($objectClass);
$res="";
foreach ($list as $cs) {
	if (! $cs->hidden) {
    $res.=$cs->attribute.';';
	}
}
echo $res;
?>

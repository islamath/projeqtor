<?PHP
/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
  require_once "../tool/projeqtor.php";
  include_once('../tool/formatter.php');
//echo "costPlan.php";
  $objectClass='PlanningElement';
  $obj=new $objectClass();
  $table=$obj->getDatabaseTableName();
  $print=false;
  if ( array_key_exists('print',$_REQUEST) ) {
    $print=true;
  }
  
  
  // Header
  $headerParameters="";
  if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $_REQUEST['idProject'])) . '<br/>';
  }
  include "header.php";

  $accessRightRead=securityGetAccessRight('menuProject', 'read');

  $querySelect = '';
  $queryFrom='';
  $queryWhere='';
  $queryOrderBy='';
  $idTab=0;
  if (! array_key_exists('idle',$_REQUEST) ) {
    $queryWhere= $table . ".idle=0 ";
  }
  $queryWhere.= ($queryWhere=='')?'':' and ';
  $queryWhere.=getAccesResctictionClause('Activity',$table);
  if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(true, $_REQUEST['idProject']) ;
  }

  $querySelect .= $table . ".* ";
  $queryFrom .= $table;
  $queryOrderBy .= $table . ".wbsSortable ";

  // constitute query and execute
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query='select ' . $querySelect
       . ' from ' . $queryFrom
       . ' where ' . $queryWhere
       . ' order by ' . $queryOrderBy;
  $result=Sql::query($query);
//echo $query;
  $test=array();
  if (Sql::$lastQueryNbRows > 0) $test[]="OK";
  if (checkNoData($test))  exit;

  if (Sql::$lastQueryNbRows > 0) {
    // Header
    echo '<table>';
    echo '<TR>';
    echo '  <TD class="reportTableHeader" style="width:10px; border-right: 0px;"></TD>';
    echo '  <TD class="reportTableHeader" style="width:200px; border-left:0px; text-align: left;">' . i18n('colTask') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colValidated') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colAssigned') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colPlanned') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colReal') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:60px" nowrap>' . i18n('colLeft') . '</TD>' ;
    echo '  <TD class="reportTableHeader" style="width:70px" nowrap>' . i18n('progress') . '</TD>' ;
    echo '</TR>';
    // Treat each line
    while ($line = Sql::fetchLine($result)) {
    	$line=array_change_key_case($line,CASE_LOWER);
      $validatedCost=$line['validatedcost'];
      $assignedCost=$line['assignedcost'];
      $plannedCost=$line['plannedcost'];
      $realCost=$line['realcost'];
      $leftCost=$line['leftcost'];
      $progress=' 0';
      if ($plannedCost>0) {
        $progress=round(100*$realCost/$plannedCost);
      } else {
        if ($line['done']) {
          $progress=100;
        }
      }
      // pGroup : is the tack a group one ?
          $pGroup=($line['elementary']=='0')?1:0;
      $compStyle="";
      if( $pGroup) {
        $rowType = "group";
        $compStyle="font-weight: bold; background: #E8E8E8 ;";
      } else if( $line['reftype']=='Milestone'){
        $rowType  = "mile";
        $compStyle="font-weight: light; font-style:italic;";
      } else {
        $rowType  = "row";
      }
      $wbs=$line['wbssortable'];
      $level=(strlen($wbs)+1)/4;
      $tab="";
      for ($i=1;$i<$level;$i++) {
        $tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
      }
      echo '<TR>';
      echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '"><img style="width:16px" src="../view/css/images/icon' . $line['reftype'] . '16.png" /></TD>';
      echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '" nowrap>' . $tab . htmlEncode($line['refname']) . '</TD>';
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($validatedCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($assignedCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($plannedCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($realCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">' . htmlDisplayCurrency($leftCost,true)  . '</TD>' ;
      echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . percentFormatter($progress) . '</TD>' ;
      echo '</TR>';
    }
  }
  echo "</table>";
?>
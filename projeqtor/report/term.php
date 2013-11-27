<?php
include_once '../tool/projeqtor.php';

//Si l'idProject est défini dans les paramètres du rapport
$idProject = "";
if (array_key_exists('idProject', $_REQUEST)){
    $idProject=trim($_REQUEST['idProject']);
}

$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
    $paramYear=$_REQUEST['yearSpinner'];    
}
  
$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
    $paramMonth=$_REQUEST['monthSpinner'];
}

$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
    $paramWeek=$_REQUEST['weekSpinner'];
}

 if (array_key_exists('periodType',$_REQUEST)) {
    $periodType=$_REQUEST['periodType'];
    //$periodValue=$_REQUEST['periodValue'];
}
//On construit la clause where 
$where = '1=1';
if ($idProject) $where .= ' AND term.idProject = '.$idProject;
if ($periodType) {
    $start=date('Y-m-d');
    $end=date('Y-m-d');
    if ($periodType=='year') {
        $start=$paramYear . '-01-01';
        $end=$paramYear . '-12-31';
    } else if ($periodType=='month') {
        $start=$paramYear . '-' . (($paramMonth<10)?'0':'') . $paramMonth . '-01';
        $end=$paramYear . '-' . (($paramMonth<10)?'0':'') . $paramMonth . '-' . date('t',mktime(0,0,0,$paramMonth,1,$paramYear));  
    } if ($periodType=='week') {
        $start=date('Y-m-d', firstDayofWeek($paramWeek, $paramYear));
        $end=addDaysToDate($start,6);
    }
  $where.=" AND (  term.date >= '" . $start . "'";
  $where.="        and term.date <='" . $end . "' )";
}

$term = new Term();
$termList = $term->getSqlElementsFromCriteria(null,false, $where);

//En-tete du tableau
        echo '<div style="page-break-before:always;"></div>';
        echo '<h3>'.i18n("reportTermTitle").' '.$start.' -> '.$end.'</h3>';
        echo '
        <table style="width: 100%;">
            <tr>
                <th class="reportTableHeader">'.i18n("colDate").'</th>
                <th class="reportTableHeader">'.i18n("Term").'</th>
                <th class="reportTableHeader">'.i18n("colUntaxedAmount").'</th>
                <th class="reportTableHeader">'.i18n("colProjectCode").'</th>
                <th class="reportTableHeader">'.i18n("colProjectName").'</th>
                <th class="reportTableHeader">'.i18n("Bill").'</th>
                <th class="reportTableHeader">'.i18n("colIsBilled").'</th>
            </tr>';
        
//liste de toutes les échances correspondants aux paramètres
foreach ($termList as $term)
{
    $project=new Project($term->idProject);
    $bill=new Bill($term->idBill);
 
    echo '
            <tr>
                <td class="reportTableData">'.$term->date.'</td>
                <td class="reportTableData">'.$term->name.'</td>
                <td class="reportTableData">'.$term->amount.'</td>
                <td class="reportTableData">'.$project->projectCode.'</td>
                <td class="reportTableData">'.$project->name.'</td>
                <td class="reportTableData">'.$bill->name.'</td>';
                if($bill->id){
                    echo'<td class="reportTableData"><img src="./img/checkedOK.png" width="12" height="12" /></td>';
                }else{
                    echo'<td class="reportTableData"><img src="./img/checkedKO.png" width="12" height="12" /></td>';
                }
                
            echo'</tr>';
            
}
 echo '</table>';

?>

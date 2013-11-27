<?php
include_once '../tool/projeqtor.php';

include("../external/pChart/pData.class");  
include("../external/pChart/pChart.class");  

$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
  $paramYear=$_REQUEST['yearSpinner'];
};

$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
  $paramMonth=$_REQUEST['monthSpinner'];
};

$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
  $paramWeek=$_REQUEST['weekSpinner'];
};

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
};

$paramIssuer='';
if (array_key_exists('issuer',$_REQUEST)) {
  $paramIssuer=trim($_REQUEST['issuer']);
};

  $paramRequestor='';
  if (array_key_exists('requestor',$_REQUEST)) {
    $paramRequestor=trim($_REQUEST['requestor']);
  }
  
$paramResponsible='';
if (array_key_exists('responsible',$_REQUEST)) {
  $paramResponsible=trim($_REQUEST['responsible']);
};

$user=$_SESSION['user'];

$periodType='year';
//$periodValue=$_REQUEST['periodValue'];
$periodValue=$paramYear;

// Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';  
}
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
  if ($paramRequestor!="") {
    $headerParameters.= i18n("colRequestor") . ' : ' . SqlList::getNameFromId('Contact', $paramRequestor) . '<br/>';
  }
if ($paramIssuer!="") {
  $headerParameters.= i18n("colIssuer") . ' : ' . SqlList::getNameFromId('User', $paramIssuer) . '<br/>';
}
if ($paramResponsible!="") {
  $headerParameters.= i18n("colResponsible") . ' : ' . SqlList::getNameFromId('Resource', $paramResponsible) . '<br/>';
}
include "header.php";

$lst=SqlList::getList('TicketType');

$includedReport=true;

$cptBoucle=0;
$cptBoucleMax=count($lst);
Foreach ($lst as $code=>$name) {
  echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
  echo "$name" . '<br/>';
  echo '</td></tr>';
  echo '<tr><td>&nbsp;</td></tr>';
  echo '<tr><td></td></tr>';
  echo '</table>';
  $paramTicketType=$code;
  include "ticketYearlyReport.php";
  echo '<br/>';
  $cptBoucle++;
  if ($cptBoucle<$cptBoucleMax) {
    echo '</page><page pageset="old">';
  }
}
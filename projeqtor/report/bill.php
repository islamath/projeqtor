<?php
//
// THIS IS THE BILL REPORT
// USE IT AS A TEMPLATE (GO TO "BILL TEMPLATE" COMMENT) 
// INSERT YOUR OWN LOGO, CHANGE DISPLAY TO EDIT YOUR OWN BILL FORMAT
//
include_once '../tool/projeqtor.php';
$idProject = "";
if (array_key_exists('idProject', $_REQUEST)){
	$idProject=trim($_REQUEST['idProject']);
}
$idClient = "";
if (array_key_exists('idClient', $_REQUEST)){
	$idClient=trim($_REQUEST['idClient']);
}
$idBill = "";
if (array_key_exists('idBill', $_REQUEST)){
	$idBill=trim($_REQUEST['idBill']);
}
$crit = array();
$crit['idle']="0";
$crit['done']="1";
if ($idBill != ""){
	$crit['id']=$idBill;
} else {
	if ($idClient)	$crit['idClient']=$idClient;
	if ($idProject) $crit['idProject']=$idProject;
}
$bill = new Bill();
$billList = $bill->getSqlElementsFromCriteria($crit,false);
$first=true;
foreach ($billList as $bill)
{
  // BILL TEMPLATE : BRING YOUR CHANGES HERE
	$recipient = new Recipient($bill->idRecipient);
	$project=new Project($bill->idProject);
	$client=new Client($bill->idClient);
	$contact=new Contact($bill->idContact);
	
	if (! $first) {
	  echo '<div style="page-break-before:always;"></div>';
	}
	$first=false;
	echo '<table style="width: 100%;"><tr><td style="width: 50%;">';
  // LOGO
  $uri=$_SERVER['REQUEST_URI'];
  $uri=substr($uri,0,strpos($uri,'/report/'));
  echo '<div style="position: relative; top: 0em; left: 1em; width: 20em; height: 5em;">';
    if (file_exists("../logo.gif")) {
      $uri.='/view/logo.gif';	
    } else {
      $uri.='/view/img/title.gif';
    }
  echo '<img style="height:5em" src="' . $uri . '" />';
  echo '</div>';
	// RECIPIENT ADDRESS
	echo '<div style="position: relative; top: 1em; left: 1em; width: 20em; height: 10em;font-size: 12px">';
  	echo '<b>' . $recipient->designation .'</b><br/>';
	  echo ($recipient->street)?$recipient->street . '<br/>':'';
	  echo ($recipient->complement)?$recipient->complement . '<br/>':'';
	  echo ($recipient->zip)?$recipient->zip . '<br/>':'';
	  echo ($recipient->city)?$recipient->city . '<br/>':'';
	  echo ($recipient->state)?$recipient->state . '<br/>':'';
	  echo ($recipient->country)?$recipient->country . '<br/>':'';
  echo '</div>';
  echo '</td><td style="width:50%">';
  // BILLING
  $numBill=Parameter::getGlobalParameter('billPrefix')
          . str_pad($bill->billId,Parameter::getGlobalParameter('billNumSize'),'0', STR_PAD_LEFT)
          . Parameter::getGlobalParameter('billSuffix');   
  echo '<div style="position: relative; top: 1em; left: 1em; width: 90%; height: 4em; ';
    echo ' border: 2px solid #7070A0;-moz-border-radius: 15px; border-radius: 15px;">';
    echo '<table style="width:100%">';
    echo '<tr><td style="text-align:right; width:50%"><b>' . i18n('colBillId')  . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $numBill . '</td></tr>';
    echo '<tr><td style="text-align:right;"><b>' . i18n('colCompanyNumber') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $recipient->companyNumber . '</td></tr>';
    echo '<tr><td style="text-align:right;"><b>' . i18n('colNumTax') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $recipient->numTax . '</td></tr>';
    echo '</table>';
	echo '</div>';
	// CONTACT
  echo '<div style="position: relative; top: 3em; left: 1em; width: 90%; height: 10em; font-size:14px;">';
    echo '<b>' . $contact->designation .'</b><br/>';
    echo ($contact->street)?$contact->street . '<br/>':'';
    echo ($contact->complement)?$contact->complement . '<br/>':'';
    echo ($contact->zip)?$contact->zip . '<br/>':'';
    echo ($contact->city)?$contact->city . '<br/>':'';
    echo ($contact->state)?$contact->state . '<br/>':'';
    echo ($contact->country)?$contact->country . '<br/>':'';
  echo '</div>';
  echo '</td></tr></table>';  
  echo '<table style="width:100%;"><tr><td width="100%">';
	// TITLE
	echo '<div style="solid red;position: relative; top: 3em; left: 1em; width: 98%; height: 2em;">';
    echo '<div style="width: 100%;border-bottom: 3px solid #7070A0">&nbsp;</div>';
  echo '</div>';
  echo '</td></tr><tr><td>';
  echo '<div style="position: relative; top: 1.5em; left: 1em; width: 98%; height: 2em;">';
	  echo '<div style="width: 100%;text-align:center;color:#7070A0"><h1><b>' . strtoupper(i18n('Bill')) . '</b></h1></div>';
	echo '</div>';
	echo '</td></tr><tr><td>';
	echo '<div style="position: relative; top: 1em; left: 1em; width: 98%; height: 2em;">';
    echo '<div style="width: 100%;border-bottom: 3px solid #7070A0">&nbsp;</div>';
  echo '</div>';
  echo '</td></tr><tr><td>';	
	// NAME
	echo '<table width="100%"><tr><td width="70%">';
	echo '<div style="position: relative; top: 1em; left: 1em; width: 100%; height: 3em; ">';
    echo " " . htmlEncode($bill->name) . '<br/>';
    echo " " . i18n('Project') . " : " . htmlEncode($project->name);
  echo '</div>';  
  echo '</td><td style="width:30%; text-align: right;">';
  // DATE
  echo '<div style="position: relative; top: 1em; width: 12em; height: 1.5em;';
  echo ' border: 2px solid #7070A0;-moz-border-radius: 15px; border-radius: 15px;';
  echo ' text-align:center; vertical-align: middle; ">';
    echo htmlFormatDate($bill->date);
  echo '</div>';
  echo '</td></tr></table>';
  echo '</td></tr></table>';
	// BILL LINES and TOTAL
	$line = new BillLine();
  $crit = array("refId"=>$bill->id,"refType"=>"Bill");
  $lineList = $line->getSqlElementsFromCriteria($crit,false,null,"line");
  echo '<div style="border: 0px solid red;width:98%; text-align: center; position: relative; top: 2em; left: 1em; ';
  echo ' font-family: arial; font-size: 11px; min-height: 55em; page-break-inside:avoid">';
	echo '<table style="width:100%; vertical-align: middle; text-align: center;">';
  echo '<tr>';
  echo '<th style="width:10%; border:solid 2px #7070A0; background: #F0F0F0; text-align: center;">' . ucfirst(i18n('colQuantity')) . '</th>';  
  echo '<th style="width:30%; border:solid 2px #7070A0; background: #F0F0F0; text-align: center;">' . ucfirst(i18n('colDescription')) . '</th>';
  echo '<th style="width:40%; border:solid 2px #7070A0; background: #F0F0F0; text-align: center;">' . ucfirst(i18n('colDetail')) . '</th>';
  echo '<th style="width:10%; border:solid 2px #7070A0; background: #F0F0F0; text-align: center;">' . ucfirst(i18n('colPrice')) . '</th>';
  echo '<th style="width:10%; border:solid 2px #7070A0; background: #F0F0F0; text-align: center;">' . ucfirst(i18n('colAmount')) . '</th>';  
  echo '</tr>';
  foreach ($lineList as $line) {
  	echo '<tr>';
  	echo '<td style="border-left:solid 2px #7070A0; border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';
  	echo '</tr>';
    echo '<tr>';
    echo '<td style="text-align: center; vertical-align: top; border-left:solid 2px #7070A0; border-right:solid 2px #7070A0;">' . $line->quantity . '</td>';
    echo '<td style="text-align: left; vertical-align: top; border-right:solid 2px #7070A0;">' . htmlEncode($line->description,'withBR') . '</td>';
    echo '<td style="text-align: left; vertical-align: top; border-right:solid 2px #7070A0;">' . htmlEncode($line->detail,'withBR') . '</td>';
    echo '<td style="text-align: center; vertical-align: top; border-right:solid 2px #7070A0;">' . htmlDisplayCurrency($line->price) . '</td>';
    echo '<td style="text-align: center; vertical-align: top; border-right:solid 2px #7070A0;">' . htmlDisplayCurrency($line->amount) . '</td>';
    echo '</tr>';
  }
  echo '<tr>';
    echo '<td style="border-left:solid 2px #7070A0; border-right:solid 2px #7070A0;border-bottom:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;border-bottom:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;border-bottom:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;border-bottom:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;border-bottom:solid 2px #7070A0;">&nbsp;</td>';
  echo '</tr>';
  echo '<tr>';
    echo '<td colspan="4" style=" border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';   
  echo '</tr>';
  echo '<tr>';
    echo '<td colspan="3" style="text-align: right;">&nbsp;</td>';
    echo '<td style=" border-right:solid 2px #7070A0;text-align: center;">' . i18n('colUntaxedAmount') . '&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">' . htmlDisplayCurrency($bill->untaxedAmount) . '</td>';   
  echo '</tr>';
  echo '<tr>';
    echo '<td colspan="4" style=" border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';   
  echo '</tr>';
  echo '<tr>';
    echo '<td colspan="3" style="text-align: right;">' . i18n('colTax') . '&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">' . htmlDisplayPct($bill->tax) . '</td>';
    echo '<td style="border-right:solid 2px #7070A0;">' . htmlDisplayCurrency(( $bill->fullAmount - $bill->untaxedAmount) ) . '</td>';   
  echo '</tr>';
  echo '<tr>';
    echo '<td colspan="4" style="border-right:solid 2px #7070A0;">&nbsp;</td>';
    echo '<td style="border-right:solid 2px #7070A0;">&nbsp;</td>';   
  echo '</tr>';
  echo '<tr>';
    echo '<td colspan="3" style="text-align: right;">&nbsp;</td>';
    echo '<td style=" border-right:solid 2px #7070A0;text-align: center;font-weight: bold;">' . i18n('colFullAmount') . '&nbsp;</td>';
    echo '<td style="border:solid 2px #7070A0;font-weight: bold;">' . htmlDisplayCurrency($bill->fullAmount) . '</td>';   
  echo '</tr>';
  
  echo '</table>';
  echo '</div>';
	// PAYMENT  
  echo '<div style="position: relative; top: -6.5em; left: 1em; width: 55%; height: 8em; ';
    echo ' border: 2px solid #7070A0;-moz-border-radius: 15px; border-radius: 5px;">';
    echo '<table style="width:100%">';
    echo '<tr><td colspan="2" style="text-align:center; font-size: 120%; font-weight: bold; color: #7070A0">' . i18n('Payment') . '</td></tr>';
    echo '<tr><td style="text-align:right; width:50%;"><b>' . i18n('colDesignation') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $recipient->designation . '</td></tr>';
    echo '<tr><td style="text-align:right;"><b>' . i18n('colIbanCountry') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $recipient->ibanCountry . '</td></tr>';
    echo '<tr><td style="text-align:right;"><b>' . i18n('colIbanKey') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $recipient->ibanKey . '</td></tr>';
    echo '<tr><td style="text-align:right;"><b>' . i18n('colIbanBban') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $recipient->ibanBban . '</td></tr>';
    echo '<tr><td style="text-align:right;"><b>' . i18n('colPaymentDelay') . '&nbsp;:&nbsp;</b></td>';
    echo '    <td style="text-align:left;white-space:nowrap;">' . $client->paymentDelay . ' ' . i18n('days') . '</td></tr>';
    echo '</table>';
  echo '</div>';
	continue;
	

	
	$client = new Client($bill->idClient);
	echo htmlEncode($client->name)."<br/>";
	echo htmlEncode($client->description)."<br/>";
	echo "Delai : ".$client->paymentDelay."<br/>";
	
	if ($client->id)
	{
		$user = new User();
		$critb = array("idClient"=>$client->id);
		$userList = $user->getSqlElementsFromCriteria($critb,false);
		if (count($userList)!=0)
		{
			echo "Contact : ".htmlEncode($userList[0]->name)."<br/>";
			echo "Portable : ".$userList[0]->mobile."<br/>";
			echo "Fixe : ".$userList[0]->phone."<br/>";
			echo "Fax : ".$userList[0]->fax."<br/><br/>";
			echo $userList[0]->street."<br/>";
			echo $userList[0]->complement."<br/>";
			echo $userList[0]->zip."  ".$userList[0]->city."<br/>";
			echo $userList[0]->country."  ".$userList[0]->state."<br/>";
			
		}
	}
	
	// nom de contact et adresse
	
	
	echo "</td></tr>";
	echo '<tr><td>&nbsp;</td></tr>';
	//date et autres détails
	echo "<tr><td>";
	
	echo "Date de facturation : ".$bill->date."</td></tr>";
	
	if ($bill->startDate!="")
	{
		echo "<tr><td>Pour la periode du ".$bill->startDate." au ".$bill->endDate;
		echo "</td></tr>"; 
	}
	
	echo '<tr><td>&nbsp;</td></tr>';
	// affichage des lignes
	echo "<tr><td>";
	
	


	echo "</td></tr>";
	echo '<tr><td>&nbsp;</td></tr>';
	// totaux	
	echo "<tr><td>";
	echo "<table>";
	
	echo "<tr><td width=100px>Total HT : </td><td>".$acc."</td></tr>";
	echo "<tr><td>TVA : </td><td>".$client->tax."</td></tr>";
	echo "<tr><td>Total TTC : </td><td>".($acc+$acc/100*$client->tax)."</td></tr>";
	
	echo "</table>";
	echo "</td></tr>";
	echo '<tr><td>&nbsp;</td></tr>';
	// détails contractant
	echo "<tr><td>";
	
	echo i18n("colCompanyNumber") . " : ".$recipient->companyNumber."<br/>";
	echo "numero TVA : ".$recipient->numTax."<br/>";
	echo "banque : ".$recipient->bank."<br/>";
	echo "numero RIB : ".$recipient->numBank." ".$recipient->numOffice." ".$recipient->numAccount." ".$recipient->numKey."<br/>";
	
	echo "</td></tr>";
	echo "</table>";
}

?>
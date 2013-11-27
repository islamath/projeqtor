<?php
include_once "../tool/projeqtor.php";
header ('Content-Type: text/html; charset=UTF-8');
scriptLog('   ->/tool/importHelp.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtor.css" />
</head>

<body class="white" onLoad="top.hideWait();" style="overflow: auto; ">
<?php 
$class='';

if (! array_key_exists('elementType',$_REQUEST)) {
  throwError('elementType parameter not found in REQUEST');
}
$class=SqlList::getNameFromId('Importable',$_REQUEST['elementType'],false);

if (! array_key_exists('fileType',$_REQUEST)) {
  throwError('fileType parameter not found in REQUEST');
}
$fileType=$_REQUEST['fileType'];

//echo $class . '<br/>';
$obj=new $class();
$fields=getFields($obj);

echo '<TABLE WIDTH="100%" style="border: 1px solid black">';
echo '<TR>';
foreach ($fields as $value=>$foo) {
  echo '<TH class="messageHeader" style="color:#000000;">' . $value . "</TH>";  
}
echo '</TR><TR>';
foreach ($fields as $value) {
  $split=explode('#',$value);
  echo '<td class="messageData" style="color:#000000;">' . $split[0] . '</td>';
}
echo '</TR><TR>';
foreach ($fields as $value) {
  $split=explode('#',$value);
  $val=$split[1];
  if ($val!='date' and $val!='datetime') {
    $val.='('.$split[2].')';
  }
  echo '<td class="messageData" style="color:#000000;">' . $val . '</td>';
}
echo '</TR>';
echo "</TABLE>";

function getFields($obj, $included=false) {
  $fields=array();
  foreach($obj as $fld=>$val) {
    $firstCar=substr($fld,0,1);
    $threeCars=substr($fld,0,3);
    if ($firstCar=="_") {
      // don't display
    } else if ( $included and ($fld=='id' or $threeCars=='ref' or $threeCars=='top' 
                            or $fld=='idle' 
                            //or $threeCars=='wbs'
                            )) {
      // don't display
    } else if ( strpos($obj->getFieldAttributes($fld),'hidden')!==false or strpos($obj->getFieldAttributes($fld),'calculated')!==false) {
      // don't display
    } else if ($firstCar==ucfirst($firstCar)) {
      //echo $fld . '<br/>';
      $subObj=new $fld();
      $subFields=getFields($subObj,true);
      $fields=array_merge($fields,$subFields);
    } else {
      $fields[$fld]=$obj->getColCaption($fld) . '#' . $obj->getDataType($fld) . '#' . $obj->getDataLength($fld);
    }
  }
  return $fields;
}
?>
</body>
</html>

<?php 
// Header
//echo "<page_header>";
set_time_limit(300);
ini_set('memory_limit', '512M');

echo "<table style='width:100%'><tr>";
echo "<td style='width:1%' class='reportHeader'>&nbsp;</td>";
echo "<td style='width:10%' class='reportHeader'>" . i18n('colParameters') . "</td>";
echo "<td style='width:1%' class='reportHeader'>&nbsp;</td>";
echo "<td style='width:1%' >&nbsp;</td>";
echo "<td style='width:30%'>"; 
echo $headerParameters;
echo "</td>";
echo "<td align='center' style='width:40%; font-size: 150%; font-weight: bold;'>"; 

if (array_key_exists('reportName', $_REQUEST)) {
  echo '<table><tr><td class="reportTableHeader" style="text-align: center; padding: 3px 10px 3px 10px;">';
  echo ucfirst($_REQUEST['reportName']);
  echo '</td></tr></table>';
}
echo "</td>";
echo "<td style='width:1%'>&nbsp;</td>";
echo "<td style='width:15%; text-align:right'>";
echo  htmlFormatDate(date('Y-m-d')) . " " . date('H:i');
echo "</td>";
echo "<td style='width:1%'>&nbsp;</td>";
echo "</tr></table>";
echo "<br/>";
//echo "</page_header>";

$graphEnabled=true;
if (! function_exists('ImagePng')) {
  $graphEnabled=false;
  errorLog("GD Library not enabled - impossible to draw charts");
}
if (! function_exists('imageftbbox')) {
  $graphEnabled=false;
  errorLog("GD Library or FreeType Librairy incorrect or not correctly installed - impossible to draw charts");
}

$rgbPalette=array(
6=>array('B'=>200, 'G'=>100, 'R'=>100),
7=>array('B'=>100, 'G'=>200, 'R'=>100),
8=>array('B'=>100, 'G'=>100, 'R'=>200),
9=>array('B'=>200, 'G'=>200, 'R'=>100),
10=>array('B'=>200, 'G'=>100, 'R'=>200),
11=>array('B'=>100, 'G'=>200, 'R'=>200),
0=>array('B'=>250, 'G'=> 50, 'R'=> 50),
1=>array('B'=> 50, 'G'=>250, 'R'=> 50),
2=>array('B'=> 50, 'G'=> 50, 'R'=>250),
3=>array('B'=>250, 'G'=>250, 'R'=> 50),
4=>array('B'=>250, 'G'=> 50, 'R'=>250),
5=>array('B'=> 50, 'G'=>250, 'R'=>250)
);

include_once('headerFunctions.php');
?>

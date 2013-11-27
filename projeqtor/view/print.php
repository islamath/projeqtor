<?php
/* ============================================================================
 * Print page of application.
 */
   require_once "../tool/projeqtor.php";
   scriptLog('   ->/view/print.php'); 
   set_time_limit(300);
   ob_start();
   $outMode='html';
   $printInNewPage=getPrintInNewWindow();
   if (array_key_exists('outMode', $_REQUEST)) {
     if ($_REQUEST['outMode']) {
       $outMode=$_REQUEST['outMode'];
     }
   }
   if ($outMode=='pdf') {
     $printInNewPage=getPrintInNewWindow('pdf');
     $memoryLimitForPDF=Parameter::getGlobalParameter('paramMemoryLimitForPDF');
     if (isset($memoryLimitForPDF)) {
       $limit=$memoryLimitForPDF;	
     } else {
     	 $limit='';
     }
     if ($limit===0) {
     	 header ('Content-Type: text/html; charset=UTF-8');
     	 echo "<html><head></head><body>";
     	 echo i18n("msgPdfDisabled");
     	 echo "</body></html>";
     	 return;
     } else if ($limit=='') {
       // Keep existing
     } else {
      ini_set("memory_limit", $limit.'M');
     }
   } else if ($outMode=='csv')  {
     $contentType="application/force-download";
     $name="export_" . $_REQUEST['objectClass'] . "_" . date('Ymd_His') . ".csv";
     header("Content-Type: " . $contentType . "; name=\"" . $name . "\""); 
	   header("Content-Transfer-Encoding: binary"); 
	   //header("Content-Length: $size"); 
	   header("Content-Disposition: attachment; filename=\"" .$name . "\""); 
	   header("Expires: 0"); 
	   header("Cache-Control: no-cache, must-revalidate");
	   header("Pragma: no-cache");
   } else if ($outMode=='mpp')  {
     $contentType="application/force-download";
     $name="export_planning_" . date('Ymd_His') . ".xml";
     header("Content-Type: " . $contentType . "; name=\"" . $name . "\""); 
     header("Content-Transfer-Encoding: binary"); 
     //header("Content-Length: $size"); 
     header("Content-Disposition: attachment; filename=\"" .$name . "\""); 
     header("Expires: 0"); 
     header("Cache-Control: no-cache, must-revalidate");
     header("Pragma: no-cache");
   } else {
     header ('Content-Type: text/html; charset=UTF-8');
   }
   $detail=false;
   if (array_key_exists('detail', $_REQUEST)) {
   	$detail=true;
   }
  if ($outMode!='pdf' and $outMode!='csv' and $outMode!='mpp') {?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<?php }
   if ($outMode!='csv' and $outMode!='mpp') {?>
<html>
<head>   
  <title><?php echo getPrintTitle();?></title>
  <link rel="stylesheet" type="text/css" href="css/jsgantt.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorPrint.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='modulePaths: {i18n: "../../tool/i18n"},
              parseOnLoad: true,
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js"></script>
  <script type="text/javascript" src="../view/js/jsGantt.js"></script>
  <script type="text/javascript"> 
    dojo.require("dojo.parser");
    dojo.require("dojo.i18n");
    //dojo.require("dojo.date.locale");
    dojo.addOnLoad(function(){
      <?php 
        if (array_key_exists('directPrint', $_REQUEST)) {
          echo "window.print();";
          //echo "window.close();";
        }
      ?>
      var printInNewWindow=<?php echo (getPrintInNewWindow())?'true':'false';?>;
      if (printInNewWindow) {
        objTop=window.opener;
      } else {
    	  objTop=top;
      }
      objTop.hideWait();
      objTop.window.document.title="<?php echo getPrintTitle();?>";
      window.document.title="<?php echo getPrintTitle();?>";
      <?php if ($_REQUEST['page']=='planningPrint.php') {?>
        
        dojo.byId("leftGanttChartDIV_print").innerHTML=objTop.dojo.byId("leftGanttChartDIV").innerHTML;
        dojo.byId("leftGanttChartDIV_print").style.width=objTop.dojo.byId("leftGanttChartDIV").style.width;
        dojo.byId("leftside").style.top=0;
        dojo.byId("ganttScale").innerHTML="";
        dojo.byId("GanttChartDIV_print").style.left=dojo.byId("leftGanttChartDIV_print").offsetWidth+"px";
        dojo.byId("rightGanttChartDIV_print").innerHTML=objTop.dojo.byId("rightGanttChartDIV").innerHTML;
        dojo.byId("topGanttChartDIV_print").innerHTML=objTop.dojo.byId("topGanttChartDIV").innerHTML;
        dojo.byId("rightside").style.left='-1px';
        var height=dojo.byId("leftside").offsetHeight;
        height+=43;
        dojo.byId("GanttChartDIV_print").style.height=height+'px';
        //var g = new JSGantt.GanttChart('g',dojo.byId('ganttDiv'), top.g.getFormat()); 
      <?php }?>
    }); 
    
  </script>
</head>
<page backtop="100px" backbottom="20px" footer="page">
<<?php echo ($printInNewPage or $outMode=='pdf') ?'body':'div';?> style="-webkit-print-color-adjust: exact;" id="bodyPrint" class="white" onload="top.hideWait();";>
  <?php 
  }
  $includeFile=$_REQUEST['page'];
  if (! substr($_REQUEST['page'],0,3)=='../') {
    $includeFile.='../view/';
  }
  if (strpos($includeFile,'?')>0) {
    $params=substr($includeFile,strpos($includeFile,'?')+1);
    $includeFile=substr($includeFile,0,strpos($includeFile,'?'));
    $paramArray=explode('&',$params);
    foreach ($paramArray as $param) {
      $par=explode('=',$param);
      $_REQUEST[$par[0]]=$par[1];
    }
  }
  include $includeFile;
  if ($outMode!='csv' and $outMode!='mpp') {?>
</<?php echo ($printInNewPage or $outMode=='pdf')?'body':'div';?>>
</page>
</html>
<?php
  } 
  finalizePrint();
?>
<?php function finalizePrint() {
  global $outMode;
  $pdfLib='html2pdf';
  //$pdfLib='dompdf';
  if ($outMode=='pdf') {
    $content = ob_get_clean(); 
    if ($pdfLib=='html2pdf') {
      /* HTML2PDF way */
      require_once('../external/html2pdf/html2pdf.class.php');
      $html2pdf = new HTML2PDF('L','A4','en');
      $html2pdf->pdf->SetDisplayMode('fullpage');
      $html2pdf->setDefaultFont('freesans');
      $html2pdf->setTestTdInOnePage(false);
      //$html2pdf->setDefaultFont('uni2cid_ag15');
      $html2pdf->writeHTML($html2pdf->getHtmlFromPage($content)); 
      $html2pdf->Output();
    } else if ($pdfLib=='dompdf') {
    /* DOMPDF way */
      require_once("../external/dompdf/dompdf_config.inc.php");
      $dompdf = new DOMPDF();
      $dompdf->load_html($content);
      $dompdf->render();
      $dompdf->stream("sample.pdf");
    }
  }
}
?>
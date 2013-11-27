<?php
require_once "../tool/projeqtor.php";
$proj='';
if (array_key_exists('project',$_SESSION)) {
  $proj=$_SESSION['project'];
}
$prj=new Project($proj);
$lstProj=$prj->getRecursiveSubProjectsFlatList(true,true);
echo '<table style="width: 100%;">';
foreach ($lstProj as $prjId=>$prjName) {
  $att=new Attachement();
  $lstAtt=$att->getSqlElementsFromCriteria(array('refType'=>'Project','refId'=>$prjId, 'type'=>'link'));
  //* $lstAtt Attachment[]
  if (count($lstAtt)>0) {
    echo '<tr><th class="linkHeader">';
    echo htmlEncode($prjName);
    echo '</th></tr>';
    foreach ($lstAtt as $att) {
      echo '<tr><td class="linkData">';
        echo '<a href="' . $att->link . '" target="#" class="hyperlink" title="' . $att->link . '">';
        echo ($att->description)?htmlEncode($att->description):htmlEncode($att->link);
        echo '</a>';
      echo '</td></tr>';
    }
  }
}
echo "</table>";

<?php include_once("../tool/projeqtor.php");
$proj='*'; 
if (array_key_exists('project',$_SESSION)) {
  $proj=$_SESSION['project'];
} else {
  $_SESSION['project']="*";
}
$prj=new Project();
$prj->id='*';
//$cpt=$prj->countMenuProjectsList();
$limitToActiveProjects=true;
if (isset($_SESSION['projectSelectorShowIdle']) and $_SESSION['projectSelectorShowIdle']==1) {
  $limitToActiveProjects=false;
}
$subProjectsToDraw=$prj->drawSubProjects('selectedProject', false, true, $limitToActiveProjects);     
$cpt=substr_count($subProjectsToDraw,'<tr>');
$displayMode="standard";
if (isset($_SESSION['projectSelectorDisplayMode'])) {
  $displayMode=$_SESSION['projectSelectorDisplayMode'];
}
?>
<?php if ($displayMode=='standard') {?>
<span maxsize="180px" style="position: absolute; left:75px; top:1px; height: 20px; width: 160px; color:#202020;" 
  dojoType="dijit.form.DropDownButton" 
  id="selectedProject" jsId="selectedProject" name="selectedProject" showlabel="true" class="">
  <span style="width:160px; text-align: left;">
    <div style="width:160px; overflow: hidden; text-align: left;" >
    <?php
if ($proj=='*') {
  echo '<i>' . i18n('allProjects') . '</i>';
} else {
  $projObject=new Project($proj);
  echo htmlEncode($projObject->name);
};
    ?>
    </div>
  </span>
  <span dojoType="dijit.TooltipDialog" class="white" <?php echo ($cpt>25)?'style="width:200px;"':'';?>>   
    <div <?php echo ($cpt>25)?'style="height: 500px; overflow-y: scroll;"':'';?>>    
    <?php 
      echo $subProjectsToDraw;
    ?>
    </div>       
  </span>
</span>
<?php } else if ($displayMode=='select') {?>
<select dojoType="dijit.form.FilteringSelect" class="input"
   style="position: absolute; left:75px; top:3px; width: 185px;" 
   name="projectSelectorFiletering" id="projectSelectorFiletering" >
   <script type="dojo/connect" event="onChange" args="evt">
    if (this.isValid()) {
      setSelectedProject(this.value, this.displayedValue, null);
    }
  </script>
   <option value="*"><i><?php echo i18n("allProjects");?></i></option>
   <?php htmlDrawOptionForReference("idProject", $proj, null, true,null, null, $limitToActiveProjects);?>
</select>
<?php } else  {?>
ERROR : Unknown display mode
<?php }?>
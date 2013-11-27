<?php
/* ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/calendar.php');
  
  $user=$_SESSION['user'];
  $collapsedList=Collapsed::getCollaspedList();
  $currentYear=strftime("%Y") ;
  if (isset($_REQUEST['year'])) {
    $currentYear=$_REQUEST['year'];
  }
  if (isset($_REQUEST['day'])) {
    switchDay($_REQUEST['day']);
    $currentYear=substr($_REQUEST['day'],0,4);
  }

?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Calendar" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div id="calendarButtonDiv" class="listTitle" dojoType="dijit.layout.ContentPane" region="top">
    <table width="100%">
      <tr>
        <td width="50px" align="center">
          <img src="css/images/iconCalendar32.png" width="32" height="32" />
        </td>
        <td NOWRAP width="50px" class="title" >
          <?php echo i18n("menuCalendar");?>&nbsp;&nbsp;&nbsp;
        </td>
        <td width="100px" align="right">&nbsp;<?php echo i18n("year");?>&nbsp;
        </td>
        <td width="200px">
          <div style="width:70px; text-align: center; color: #000000;"
               dojoType="dijit.form.NumberSpinner"
               constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
               intermediateChanges="true"
               maxlength="4"
               value="<?php echo $currentYear;?>" smallDelta="1"
               id="calendartYearSpinner" name="calendarYearSpinner" >
            <script type="dojo/method" event="onChange" >
              if (this.value!="<?php echo $currentYear;?>") {
                loadContent("calendar.php?year="+this.value,"centerDiv");
              }
            </script>
          </div>
        </td>
        <td>
           <div id="resultDiv" dojoType="dijit.layout.ContentPane" region="center" style="height:20px">
           </div>       
        </td>
      </tr>
    </table>
  </div>
  <div id="formCalendarDiv" dojoType="dijit.layout.ContentPane" region="center">
    <form dojoType="dijit.form.Form" id="calendarForm" jsId="calendarForm" name="calendarForm" encType="multipart/form-data" action="" method="" >
      <?php $cal=new Calendar;
        $cal->setDates($currentYear.'-01-01');
        echo $cal->drawSpecificItem('calendarView');
      ?>
    </form>
  </div>
</div>
<?php
function switchDay ($day) {
  global $bankHolidays, $bankWorkdays;
  $cal=SqlElement::getSingleSqlElementFromCriteria('Calendar',array('calendarDate'=>$day));
  if (!$cal->id) {
    $cal->setDates($day);
    if (isOpenDay($day)) {
      $cal->isOffDay=1;
    } else {
      $cal->isOffDay=0;
    }
    $cal->save();
  } else {
    $cal->delete();
  }
  $bankHolidays=array();
  $bankWorkdays=array();
}
?>
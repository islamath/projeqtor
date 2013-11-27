<?php
  $today=new Today();
  $crit=array('idUser'=>$user->id);
  $todayList=$today->getSqlElementsFromCriteria($crit, false,null, 'sortOrder asc');
  $cptStatic=0;
  foreach ($todayList as $todayItem) {
    if ($todayItem->scope=='static') {$cptStatic+=1;}
  }
  if ($cptStatic!=count(Today::$staticList)) {
    Today::insertStaticItems();
    $todayList=$today->getSqlElementsFromCriteria($crit, false, null,'sortOrder asc');
  }
  $user=$_SESSION['user'];
  $profile=SqlList::getFieldFromId('Profile', $user->idProfile, 'profileCode',false);
  echo '<form dojoType="dijit.form.Form" id="todayParametersForm" name="todayParametersForm" onSubmit="return false;">';
  echo '<table style="width:100%">';
  echo '<tr><td class="dialogSection" colspan="2">'.i18n('periodForTasks').'</td></tr>';
  echo '<tr><td></td><td>&nbsp;</td></tr>';
  echo '<tr style="height:10px;">';
  echo '  <td class="dialogLabel" width="10px;"><label>'.i18n('colDueDate').'&nbsp;:&nbsp;</label></td>';
  echo '  <td>';
  $crit=array('idUser'=>$user->id,'idToday'=>null,'parameterName'=>'periodDays');
  $tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter',$crit);
  echo '<input id="todayPeriodDays" name="todayPeriodDays" dojoType="dijit.form.NumberTextBox" type="text"';    
  echo '         maxlength="4"  style="width:30px; text-align: center;" class="input" value="'.$tp->parameterValue.'"/>';
  echo '<nobr>&nbsp;'.i18n('nextDays').'</nobr>';
  echo '</td>';
  echo '</tr>';
  echo '<tr style="height:10px;">';
  echo '  <td class="dialogLabel" width="10px;"><label>'.i18n('colOrNotSet').'&nbsp;:&nbsp;</label></td>';
  echo '  <td>';
  $crit=array('idUser'=>$user->id,'idToday'=>null,'parameterName'=>'periodNotSet');
  $tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter',$crit);
  echo '     <div name="todayPeriodNotSet" id="todayPeriodNotSet" dojoType="dijit.form.CheckBox" type="checkbox" '; 
  echo ($tp->parameterValue=='1')?' checked="checked"':'';
  echo '></div>';
  echo '  </td>';
  echo '  </tr>';
  echo '<tr style="border-bottom:2px solid #F0F0F0;"><td></td><td>&nbsp;</td></tr>';
  echo '<tr><td></td><td>&nbsp;</td></tr>';
  echo '</table>';
  echo '<table id="dndTodayParameters" jsId="dndTodayParameters" dojotype="dojo.dnd.Source"  dndType="today"
               withhandles="true" class="container" style="height:10px;width:100%;cellspacing:0; cellpadding:0;">';
  echo '<tr><td class="dialogSection" colspan="4">'.i18n('listTodayItems').'</td></tr>';
  echo '<tr><td colspan="4">&nbsp;</td></tr>';
  foreach ($todayList as $todayItem) {
    if ($todayItem->scope!="static" or $todayItem->staticSection!="ProjectsTasks" or $profile=='PL') {
      echo '<tr id="dialogTodayParametersRow' . $todayItem->id. '"
                class="dojoDndItem" dndType="today" style="height:10px;">';
      echo '<td class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;</td>';
      echo '<td style="width:16px;height:10px;">';
      if ($todayItem->scope!='static') {
        echo '<img src="../view/css/images/smallButtonRemove.png" onClick="setTodayParameterDeleted(' . $todayItem->id. ');" />';
      }
      echo '<input type="hidden" name="dialogTodayParametersDelete' . $todayItem->id. '" id="dialogTodayParametersDelete' . $todayItem->id. '" value="0" />';
      echo '</td>';
      echo '<td style="width:16px;height:10px;"><div name="dialogTodayParametersIdle' . $todayItem->id. '" 
                 dojoType="dijit.form.CheckBox" type="checkbox" '.(($todayItem->idle=='0')?' checked="checked"':'').'>
                </div>'.'</td>';
      echo '<td>';
      if ($todayItem->scope=="static") {
        echo "<nobr>".i18n('today'.$todayItem->staticSection)."</nobr>";
      } else if ($todayItem->scope=="report"){
        $rpt=new Report($todayItem->idReport);
        echo "<nobr>".i18n('colReport').' "'.i18n($rpt->name).'"</nobr>';
      } else {
        echo "unknown today scope";
      }
      echo '<input type="hidden" style="width:100px" 
       id="dialogTodayParametersOrder' . $todayItem->id. '" name="dialogTodayParametersOrder' . $todayItem->id. '" 
       value="' . $todayItem->sortOrder. '"/>';
      echo '</td>';
      echo '</tr>';
    }
  }
  echo '</table>'; 
  echo '<table style="width:100%">';
   echo '<tr style="border-bottom:2px solid #F0F0F0;"><td></td><td>&nbsp;</td></tr>';
  echo '<tr style="height:10px;"><td></td><td>&nbsp;</td></tr>';
  echo '</table>';
  echo '<table width="100%">';
  echo '  <tr style="height:10px;">';
  echo '    <td align="center">';
  echo '      <button dojoType="dijit.form.Button" onclick="dijit.byId(\'dialogTodayParameters\').hide();">';
  echo          i18n("buttonCancel");
  echo '      </button>';
  echo '      <button dojoType="dijit.form.Button" type="submit" id=dialogTodayParametersSubmit" onclick="saveTodayParameters();return false;">';
  echo          i18n("buttonOK");
  echo '      </button>';
  echo '    </td>';
  echo '  </tr>';
  echo '</table>';
  echo '</form>';
  ?>
<?php 
/* ============================================================================
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
class ResourceCost extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2;
  public $id;    // redefine $id to specify its visible place 
  public $idResource;
  public $idRole;
  public $cost=0;
  public $startDate;
  public $endDate; 
  public $idle;
  public $_col_2_2;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  public function save() {
    $new=($this->id)?false:true;
    $newCost=true;
    if (! $new) {
      $old=$this->getOld();
      $newCost=($old->cost==$this->cost)?false:true;
    }
    $result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $id=($this->id)?$this->id:SQL::$lastQueryNewid;
    if ($this->startDate and $new) {
      $where="idResource='" . Sql::fmtId($this->idResource) . "' and idRole='" . Sql::fmtId($this->idRole) . "' ";
      $where.=" and endDate is null";
      $where.=" and id<>'" . Sql::fmtId($id) . "'";
      $rc=new ResourceCost();
      $precs=$rc->getSqlElementsFromCriteria(null, false, $where);
      if (count($precs)==1) {
        $prec=$precs[0];
        $prec->endDate=addDaysToDate($this->startDate,-1);
        $prec->save();
      }
    }
    if ($newCost) {
      $wk=new Work();
      $where="idResource='" . Sql::fmtId($this->idResource) . "'";
      if ($this->startDate) {
        $where.= " and workDate>='" . $this->startDate . "'";
      }
      $wkList=$wk->getSqlElementsFromCriteria(null, false, $where);
      foreach ($wkList as $wk) {
        $ass=new Assignment($wk->idAssignment);
        if ($ass->idRole==$this->idRole) {
          $wk->dailyCost=$this->cost;
          $wk->save();
        }
      }     
      $where="idResource='" . Sql::fmtId($this->idResource) . "' and idRole='" .Sql::fmtId($this->idRole) . "' and leftWork>0";
      $ass=new Assignment();
      $assList=$ass->getSqlElementsFromCriteria(null, false, $where);
      foreach ($assList as $ass) {
        $ass->saveWithRefresh();
      }
    }
    return $result; 
  }
  
  public function delete() { 
    $result = parent::delete();  
    if (strpos($result,'lastOperationStatus" value="OK"')==0) {
      return $result;
    }
    
    $precStartDate=null;
    $precCost=0;
    if ($this->startDate) {
      $where="idResource='" . Sql::fmtId($this->idResource) . "' and idRole='" . Sql::fmtId($this->idRole)
        . "' and (endDate is not null ".((Sql::isMysql())?"and endDate<>'0000-00-00'":""). ")";
      if ($this->id) {
        $where.=" and id<>'" . Sql::fmtId($this->id) . "'";
      }
      $order="endDate desc";
      $rc=new ResourceCost();
      $precs=$rc->getSqlElementsFromCriteria(null, false, $where, $order);
      if (count($precs)>=1) {
        $prec=$precs[0];
        $prec->endDate=null;
        $prec->save();
        $precStartDate==$prec->startDate;
        $precCost=$prec->cost;
      }
    }

    $wk=new Work();
    $where="idResource='" . Sql::fmtId($this->idResource) . "'";
    if ($this->startDate) {
      $where.= " and workDate>='" . $this->startDate . "'";
    }
    $wkList=$wk->getSqlElementsFromCriteria(null, false, $where);
    foreach ($wkList as $wk) {
      $ass=new Assignment($wk->idAssignment);
      if ($ass->idRole==$this->idRole) {
        $wk->dailyCost=$precCost;
        $wk->save();
      }
    }     
  
    $where="idResource='" . Sql::fmtId($this->idResource) . "' and idRole='" . Sql::fmtId($this->idRole) . "' and leftWork>0";
    $ass=new Assignment();
    $assList=$ass->getSqlElementsFromCriteria(null, false, $where);
    foreach ($assList as $ass) {
      $ass->saveWithRefresh();
    }
    
    return $result;
  }
  
  public function deleteControl() { 
    $result='';
    if (! $this->startDate) {
      // Control : if assignment exists for this ressource and role => cancel deletion
      $crit=array("idResource"=>$this->idResource,"idRole"=>$this->idRole);
      $asg=new Assignment();
      $lstAsg=$asg->getSqlElementsFromCriteria($crit, false);
      if (count($lstAsg)>0) {
        // ERROR CONTROL
        $result.="<br/>".i18n("errorControlDelete");
        $result.="<br/>&nbsp;-&nbsp;" . i18n('Assignment') . " (" . count($lstAsg) . ")";
        return $result;
      } 
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  
  public function control() {
    $result="";
    if ($this->startDate and !$this->id) {
      $where="idResource='" . Sql::fmtId($this->idResource) . "' ";
      $where.=" and idRole='" . Sql::fmtId($this->idRole) . "' ";
      $where.=" and (endDate is null " .((Sql::isMysql())?"or endDate='0000-00-00'":"") .")";
      if ($this->id) {
        $where.=" and id<>'" . $this->id . "'";
      }
      $rc=new ResourceCost();       
      $precs=$rc->getSqlElementsFromCriteria(null, false, $where);
      if (count($precs)==1) {
        $prec=$precs[0];
        if ($prec->startDate and $prec->startDate>=$this->startDate) {
          $result.='<br/>' . i18n('errorStartEndDates', array(i18n('colPreviousStartDate'),i18n('colStartDate')));
        }
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
}
?>
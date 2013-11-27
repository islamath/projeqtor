<?php 
/* ============================================================================
 * User is a resource that can connect to the application.
 */ 
class Approver extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visible place
  public $refType;
  public $refId;
  public $idAffectable;
  public $approved;
  public $approvedDate;
  public $idle;

  
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
 function save() {
   $result=parent::save();
   if ($this->refType=="Document") {
     // If add an approver to Document, then add it to current DocumentVersion
     $doc=new Document($this->refId);
     if ($doc->idDocumentVersion) {
       $crit=array('refType'=>'DocumentVersion','refId'=>$doc->idDocumentVersion,'idAffectable'=>$this->idAffectable);
       $app=SqlElement::getSingleSqlElementFromCriteria('Approver',$crit);
       if (!$app->id) {
         $app->save();
       }
     }
   }
   if ($this->refType=="DocumentVersion") {
     // On update check approvement : update document version status depending on approvement
     $vers=new DocumentVersion($this->refId);
     $vers->checkApproved();
   }
   return $result;
 }

  function control() {
    $result="";
    if (! $this->id) {
      $check=SqlElement::getSingleSqlElementFromCriteria('Approver',array('refType'=>$this->refType,'refId'=>$this->refId, 'idAffectable'=>$this->idAffectable));
      if ($check->id) {
        $result.='<br/>' . i18n('errorDuplicateApprover');
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }

  function delete() {
    $result=parent::delete();
    if ($this->refType=="Document") {
      // If delete an approver to Document, then delete it from current DocumentVersion
      $doc=new Document($this->refId);
      if ($doc->idDocumentVersion) {
        $crit=array('refType'=>'DocumentVersion','refId'=>$doc->idDocumentVersion,'idAffectable'=>$this->idAffectable);
        $app=SqlElement::getSingleSqlElementFromCriteria('Approver',$crit);
        if ($app->id) {
          $app->delete();
        }
      }
    }
    if ($this->refType=="DocumentVersion") {
      // On update check approvement : update document version status depending on approvement
      $vers=new DocumentVersion($this->refId);
      $vers->checkApproved();
    }
    return $result;
  }

}
?>
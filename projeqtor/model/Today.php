<?php 
/* ============================================================================
 * Parameter is a global kind of object for parametring.
 * It may be on user level, on project level or on global level.
 */ 
class Today extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visiblez place 
  public $idUser;
  public $scope;
  public $staticSection;
  public $idReport;
  public $sortOrder;
  public $idle;
  
  public static $staticList=array('Projects','AssignedTasks','ResponsibleTasks','IssuerRequestorTasks','ProjectsTasks');
  public $_noHistory=true; // Will never save history for this object
  
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
  
  function delete() {
  	$p=new TodayParameter();
  	$res=$p->purge("idToday=".$this->id);
  	return parent::delete();
  }

// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********

  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  
  public static function insertStaticItems() {
    $user=$_SESSION['user'];
    $sort=0;
    foreach (self::$staticList as $static) {
      $crit=array('idUser'=>$user->id, 'scope'=>'static', 'staticSection'=>$static);
      $sort+=1;
      $item=SqlElement::getSingleSqlElementFromCriteria('Today', $crit);
      if (!$item->id) {
        $item->sortOrder=$sort;
        $item->idle=0;
        $item->scope='static';
        $item->save();
      }
    }
  }
}
?>
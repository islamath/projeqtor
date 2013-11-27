<?php 
/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
class OtherVersion extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $refType;
  public $refId;
  public $idVersion;
  public $scope;
  public $comment;
  public $creationDate;
  public $idUser;
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********

  public static function sort($a,$b) {
  return ($a->idVersion < $b->idVersion) ? -1 : 1;
}
}
?>
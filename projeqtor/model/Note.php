<?php
/* ============================================================================
 * Note is an object that can be included in all objects, as comments.
 */ 
class Note extends SqlElement {

  public $id;
  public $refType;
  public $refId;
  public $idUser;
  public $creationDate;
  public $updateDate;
  public $note;
  public $idPrivacy;
  public $idTeam;
  public $fromEmail;
  
  //public $_noHistory=true; // Will never save history for this object
  
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
// GET VALIDATION SCRIPT
// ============================================================================**********
 
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
  }
    
}
?>
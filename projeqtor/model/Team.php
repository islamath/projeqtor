<?php 
/* ============================================================================
 * Client is the owner of a project.
 */ 
class Team extends SqlElement {

  // extends SqlElement, so has $id
  public $_col_1_2_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $idle;
  public $description;
  public $_col_2_2_members;
  public $_spe_members;
  public $_spe_affectMembers;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="85%">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_colCaptionTransposition = array();
  
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld) {
    return self::$_colCaptionTransposition;
  }
  
  public function drawSpecificItem($item){
    $result="";
    if ($item=='members') {
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('members') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        $ress=new Resource();
        $result .= $ress->drawMemberList($this->id);
      }
      $result .="</td></tr></table>";
      return $result;
    } else if ($item=='affectMembers') {
    	
    	if ($this->id) {
	    	$result .= '<button id="affectTeamMembers" dojoType="dijit.form.Button" showlabel="true"'; 
	      $result .= ' title="' . i18n('affectTeamMembers') . '" >';
	      $result .= '<span>' . i18n('affectTeamMembers') . '</span>';
	      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
	      $result .=  '  affectTeamMembers(' . $this->id . ');';
	      $result .= '</script>';
	      $result .= '</button>';
	      return $result;
    	}
    }
  }
  
}
?>
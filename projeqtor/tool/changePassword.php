<?php
/** =========================================================================== 
 * Chek login/password entered in connection screen
 */
  require_once "../tool/projeqtor.php"; 
scriptLog("changePassword.php");  
  $password="";
  if (array_key_exists('password',$_POST)) {
    $password=$_POST['password'];
  }    
  $userSalt=$_POST['userSalt'];
  if ($password=="") {
    passwordError();
  }
  if ($password==hash('sha256',Parameter::getGlobalParameter('paramDefaultPassword').$userSalt)) {
    passwordError();
  }
  $user=$_SESSION['user'];
  if ( ! $user ) {
   passwordError();
  } 
  if ( ! $user->id) {
    passwordError();
  } 
  if ( $user->idle!=0) {
    passwordError();
  } 
  if ($user->isLdap<>0) {
    passwordError();
  } 
  $passwordLength=$_POST['passwordLength'];
  if ($passwordLength<Parameter::getGlobalParameter('paramPasswordMinLength')) {
    passwordError();
  }
  changePassword($user, $password, $userSalt, 'sha256');
  
  /** ========================================================================
   * Display an error message because of invalid login
   * @return void
   */
  function passwordError() {
    echo '<span class="messageERROR">';
    echo i18n('invalidPasswordChange', array(Parameter::getGlobalParameter('paramPasswordMinLength')));
    echo '</span>';
    exit;
  }
  
   /** ========================================================================
   * Valid login
   * @param $user the user object containing login information
   * @return void
   */
  function changePassword ($user, $newPassword, $salt, $crypto) {
  	Sql::beginTransaction();
    //$user->password=md5($newPassword); password is encryted in JS
    $user->password=$newPassword;
    $user->salt=$salt;
    $user->crypto=$crypto;
    $result=$user->save();
		if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
		  Sql::rollbackTransaction();
		  echo '<span class="messageERROR" >' . $result . '</span>';
		} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
		  Sql::commitTransaction();
		  $_SESSION['user']=$user;
		  echo '<span class="messageOK">';
	    echo i18n('passwordChanged');
	    echo '<div id="validated" name="validated" type="hidden"  dojoType="dijit.form.TextBox">OK';
	    echo '</div>';
	    echo '</span>';
		} else { 
		  Sql::rollbackTransaction();
		  echo '<span class="messageWARNING" >' . $result . '</span>';
		}
  }
  
?>
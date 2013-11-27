<?php
/** ============================================================================
 * 
 */

require_once "../tool/projeqtor.php";
$id=$_REQUEST['id'];
$txt=new PredefinedNote($id);
echo $txt->text;
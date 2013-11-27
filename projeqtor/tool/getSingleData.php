<?PHP
/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
    require_once "../tool/projeqtor.php"; 
    scriptLog('   ->/tool/getSingleData.php');
    $type=$_REQUEST['dataType'];
    if ($type=='resourceCost') {
      $idRes=$_REQUEST['idResource'];
      if (! $idRes) return;
      $idRol=$_REQUEST['idRole'];
      if (! $idRol) return;
      $r=new Resource($idRes);
      // #303
      //echo htmlDisplayNumeric($r->getActualResourceCost($idRol));
      echo $r->getActualResourceCost($idRol);
    } else if ($type=='resourceRole') {
      $idRes=$_REQUEST['idResource'];
      if (! $idRes) return;
      $r=new Resource($idRes);
      echo $r->idRole;
    } else {    
      echo '';
    } 
?>

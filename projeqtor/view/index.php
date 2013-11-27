<?php
/* ============================================================================
 * Default page. Redirects to view directory
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html style="margin: 0px; padding: 0px;">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <title>ProjeQtOr</title>
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='parseOnLoad: false, 
              isDebug: false'></script>
  <script type="text/javascript">             
     dojo.addOnLoad(function(){
       //dojo.byId("currentLocale").value=dojo.locale;
       window.setTimeout('dojo.byId("indexForm").submit();',10);
     });
  </script>
</head>

<body class="ProjeQtOr" style='background-color: #C3C3EB' >
  <div id="wait">
  &nbsp;
  </div> 
  <table align="center" width="100%" height="100%" class="loginBackground">
    <tr height="100%">
      <td width="100%" align="center">
        <div class="background loginFrame" >
        <table  align="center" >
          <tr style="height:10px;" >
            <td align="left" style="height: 1%;" valign="top">
              <div style="width: 300px; height: 54px; background-size: contain; background-repeat: no-repeat;
              background-image: url(<?php echo (file_exists("../logo.gif"))?'../logo.gif':'img/title.gif';?>);">
              </div>
            </td>
          </tr>
          <tr style="height:100%" height="100%">
            <td style="height:99%" align="left" valign="middle">
              <div  id="formDiv" dojoType="dijit.layout.ContentPane" region="center" style="width: 450px; height:210px;overflow:hidden">
  <form id="indexForm" name="indexForm" action="main.php" method="post">
    <input type="hidden" id="xcurrentLocale" name="xcurrentLocale" value="en" />
  </form>
              </div>
            </td>
          </tr>
        </table>
        </div>
      </td>
    </tr>
  </table>
</body>

</html>

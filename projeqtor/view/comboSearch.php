<?php
/* ============================================================================
 * Print page of application.
 */
/* @var string $paramDebugMode */
   require_once "../tool/projeqtor.php";
   ob_start();
   scriptLog('   ->/view/comboSearch.php'); 
   $comboDetail=true;
   $mode="";
   if (array_key_exists('mode', $_REQUEST)) {
     $mode=$_REQUEST['mode'];
   }
 ?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>   
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorWork.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorFormatter.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"
    djConfig='modulePaths: {i18n: "../../tool/i18n"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript"> 
  dojo.require("dojo.store.DataStore");
  dojo.require("dojo.data.ItemFileWriteStore");
  dojo.require("dojo.date");
  dojo.require("dojo.date.locale");
  dojo.require("dojo.i18n");
  dojo.require("dojo.parser");
  dojo.require("dijit.Dialog"); 
  dojo.require("dijit.Tooltip");
  dojo.require("dijit.layout.BorderContainer");
  dojo.require("dijit.layout.ContentPane");
  dojo.require("dijit.layout.AccordionContainer");
  dojo.require("dijit.Menu"); 
  dojo.require("dijit.MenuBar"); 
  dojo.require("dijit.MenuBarItem");
  dojo.require("dijit.Toolbar") 
  dojo.require("dijit.PopupMenuBarItem");
  dojo.require("dijit.form.ValidationTextBox");
  dojo.require("dijit.form.Textarea");
  dojo.require("dijit.form.ComboBox");
  dojo.require("dijit.form.CheckBox");
  dojo.require("dijit.form.RadioButton");
  dojo.require("dijit.form.DateTextBox");
  dojo.require("dijit.form.TimeTextBox");
  dojo.require("dijit.form.TextBox");
  dojo.require("dijit.form.NumberTextBox");
  dojo.require("dijit.form.Button");
  dojo.require("dijit.ColorPalette");
  dojo.require("dijit.form.Form");
  dojo.require("dijit.form.FilteringSelect");
  dojo.require("dijit.form.MultiSelect");
  dojo.require("dijit.form.NumberSpinner");
  dojo.require("dijit.Tree"); 
  dojo.require("dijit.TitlePane");
  dojo.require("dojox.grid.DataGrid");
  dojo.require("dojox.form.FileInput");
  dojo.require("dojox.form.Uploader");
  dojo.require("dojox.form.uploader.FileList");
  dojo.require("dojo.dnd.Container");
  dojo.require("dojo.dnd.Manager");
  dojo.require("dojo.dnd.Source");
  dojo.addOnLoad(function(){
      var onKeyPressFunc = function(event) {
            if(event.ctrlKey && event.keyChar == 's'){
              event.preventDefault();
              top.globalSave();
            }  
      };
      dojo.connect(document, "onkeypress", this, onKeyPressFunc);
    });
  </script>
</head>
<body id="body" class="<?php echo getTheme();?>" onload="top.hideWait();">
  <input type="hidden" id="comboDetail" name="comboDetail" value="true" />
  <input type="hidden" id="comboDetailId" name="comboDetailId" value="" />
  <input type="hidden" id="comboDetailName" name="comboDetailName" value="" />
  <?php 
  if ($mode=='search') {
    echo '<div id="listDiv" style="height:100%" dojoType="dijit.layout.ContentPane" region="top" splitter="true">';
    include 'objectList.php';
    echo '</div>';
  } else if ($mode=='new'){
    echo '<div id="detailDiv" style="height:100%" dojoType="dijit.layout.ContentPane" region="center" splitter="false">';
    include 'objectDetail.php';
    echo '</div>';    
  }
  ?>
</body>
</html>
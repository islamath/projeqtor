<?php
// ==================================================================================================
// This file includes all specific parameters for ProjeQtOr application
// Automatic configuration at first run
// ==================================================================================================
header ('Content-Type: text/html; charset=UTF-8');
restore_error_handler();
// Database parameters (connection information)
// BE SURE THIS DATA WAY NOT BE READABLE FROM WEB (see above important notice)
$paramFadeLoadingMode='false';
$paramDebugMode='false';
$defaultBillCode = '0';

$param=array();

$param['DbType'] = 'mysql';                           
$label['DbType'] = "Database type";
$value['DbType'] = "'mysql' or 'pgsql' (for PostgreSql)";
$pname['DbType'] = 'paramDbType';
$ctrls['DbType'] = '=mysql=pgsql=';

$param['DbHost'] = 'localhost';                       
$label['DbHost'] = "Database host";
$value['DbHost'] = "Database Server name (default is 'localhost')";
$pname['DbHost'] = 'paramDbHost';
$ctrls['DbHost'] = 'mandatory';

$param['DbPort'] = '3306';                       
$label['DbPort'] = "Database port";
$value['DbPort'] = "Database Server Port (default is '3306' for MySql, '5432' for PostgreSql)";
$pname['DbPort'] = 'paramDbPort';
$ctrls['DbPort'] = '';

$param['DbUser'] = 'root';                            
$label['DbUser'] = "Database user to connect";
$value['DbUser'] = "valid user (default is 'root' for MySql, 'postgres' for PostgreSql)";
$pname['DbUser'] = 'paramDbUser';
$ctrls['DbUser'] = 'mandatory';

$param['DbPassword'] = 'mysql';                       
$label['DbPassword'] = "Database password for user";
$value['DbPassword'] = "password for user (default is 'mysql' or '' for MySql)";
$pname['DbPassword'] = 'paramDbPassword';
$ctrls['DbPassword'] = '';

$param['DbName'] = 'projeqtor';                       
$label['DbName'] = "Database schema name";  
$value['DbName'] = "database instance name";  
$pname['DbName'] = 'paramDbName';
$ctrls['DbName'] = 'mandatory';

$param['DbDisplayName'] = 'My Own ProjeQtOr';         
$label['DbDisplayName'] = "Name to be displayed"; 
$value['DbDisplayName'] = "any value possible to identify connected database"; 
$pname['DbDisplayName'] = 'paramDbDisplayName';
$ctrls['DbDisplayName'] = '';

$param['DbPrefix'] = '';                              
$label['DbPrefix'] = "Database prefix for table names";
$value['DbPrefix'] = "prefix on table names, used to store several instances under same schema, may be left blank";
$pname['DbPrefix'] = 'paramDbPrefix';
$ctrls['DbPrefix'] = '';

$param['crlf00']='';
$label['crlf00']='crlf';

$param['ldap_allow_login'] = 'false';                              
$label['ldap_allow_login'] = "Allow login from Ldap";
$value['ldap_allow_login'] = "'true' or 'false', if set to true, ProjeQtO can log user from Ldap";
$pname['ldap_allow_login'] = 'paramLdap_allow_login';
$ctrls['ldap_allow_login'] = '=false=true=';

$param['ldap_base_dn'] = 'dc=mydomain,dc=com';                              
$label['ldap_base_dn'] = "Ldap Base DN";
$value['ldap_base_dn'] = "Ldap Base DN (dc=mydomain,dc=com)";
$pname['ldap_base_dn'] = 'paramLdap_base_dn';
$ctrls['ldap_base_dn'] = '';

$param['ldap_host'] = 'localhost';                              
$label['ldap_host'] = "Ldap Host address";
$value['ldap_host'] = "Ldap Host address (server name)";
$pname['ldap_host'] = 'paramLdap_host';
$ctrls['ldap_host'] = '';

$param['ldap_port'] = '389';                              
$label['ldap_port'] = "Ldap Port";
$value['ldap_port'] = "Ldap Port (default is 389)";
$pname['ldap_port'] = 'paramLdap_port';
$ctrls['ldap_port'] = '';

$param['ldap_version'] = '3';                              
$label['ldap_version'] = "Ldap version";
$value['ldap_version'] = "Ldap version (can be 2 or 3)";
$pname['ldap_version'] = 'paramLdap_version';
$ctrls['ldap_version'] = '=2=3=';

$param['ldap_search_user'] = 'cn=Manager,dc=mydomain,dc=com';                              
$label['ldap_search_user'] = "Ldap Search User";
$value['ldap_search_user'] = "DN of Ldap user used for search functionality";
$pname['ldap_search_user'] = 'paramLdap_search_user';
$ctrls['ldap_search_user'] = '';

$param['ldap_search_pass'] = 'secret';                              
$label['ldap_search_pass'] = "LDAP Search User Password";
$value['ldap_search_pass'] = "Password of Ldap user used for search functionality";
$pname['ldap_search_pass'] = 'paramLdap_search_pass';
$ctrls['ldap_search_pass'] = '';

$param['ldap_user_filter'] = 'uid=%USERNAME%';                              
$label['ldap_user_filter'] = "Ldap filter";
$value['ldap_user_filter'] = "Ldap filter to find used name (must include %USERNAME%)";
$pname['ldap_user_filter'] = 'paramLdap_user_filter';
$ctrls['ldap_user_filter'] = '';

$param['crlf01']='';
$label['crlf01']='crlf';
 
$param['MailSender'] = '';                              
$label['MailSender'] = "eMail address of sender";
$value['MailSender'] = "a valid email as sender for mailing function";
$pname['MailSender'] = 'paramMailSender';
$ctrls['MailSender'] = 'email';

$param['MailReplyTo'] = '';                              
$label['MailReplyTo'] = "eMail address to reply to";
$value['MailReplyTo'] = "a valid email to define the reply to for mailing function";
$pname['MailReplyTo'] = 'paramMailReplyTo';
$ctrls['MailReplyTo'] = 'email';

$param['AdminMail'] = '';                              
$label['AdminMail'] = "eMail of administrator";
$value['AdminMail'] = "a valid email of the administratror (will appear on error messages)";
$pname['AdminMail'] = 'paramAdminMail';
$ctrls['AdminMail'] = 'email';

$param['MailSmtpServer'] = 'localhost';                              
$label['MailSmtpServer'] = "SMTP Server";
$value['MailSmtpServer'] = "address of SMTP (mail) server, may be left blank (default is 'localhost')";
$pname['MailSmtpServer'] = 'paramMailSmtpServer';
$ctrls['MailSmtpServer'] = '';

$param['MailSmtpPort'] = '25';                              
$label['MailSmtpPort'] = "SMTP Port";
$value['MailSmtpPort'] = "port to talk to SMTP (mail) server (default is '25')";
$pname['MailSmtpPort'] = 'paramMailSmtpPort';
$ctrls['MailSmtpPort'] = '';

$param['MailSendmailPath'] = '';                              
$label['MailSendmailPath'] = "Sendmail program path";
$value['MailSendmailPath'] = "to set only on issue to send mails, or not using default sendmail";
$pname['MailSendmailPath'] = 'paramMailSendmailPath';
$ctrls['MailSendmailPath'] = '';

$param['crlf03']='';
$label['crlf03']='crlf';

$param['DefaultPassword'] = 'projeqtor';                              
$label['DefaultPassword'] = "Default password for initialization";
$value['DefaultPassword'] = "any string possible as default password";
$pname['DefaultPassword'] = 'paramDefaultPassword';
$ctrls['DefaultPassword'] = 'mandatory';

$param['PasswordMinLength'] = '5';                              
$label['PasswordMinLength'] = "Min length for password";
$value['PasswordMinLength'] = "any integer, to force a long password (keep is reasonable)";
$pname['PasswordMinLength'] = 'paramPasswordMinLength';
$ctrls['PasswordMinLength'] = 'integer';

$param['crlf04']='';
$label['crlf04']='crlf';

// === i18n (internationalization)
$param['DefaultLocale'] = 'en';                              
$label['DefaultLocale'] = "Default locale to be used on i18n";
$value['DefaultLocale'] = "default language, 'en' for English, 'fr' for French, 'de' for German (more locales to come next)";
$pname['DefaultLocale'] = 'paramDefaultLocale';
$ctrls['DefaultLocale'] = '=en=fr=de=es=ru=zh=';

$param['DefaultTimezone'] = 'Europe/Paris';                              
$label['DefaultTimezone'] = "Default time zone";
$value['DefaultTimezone'] = "default time zone, list can be found at <a href='http://us3.php.net/manual/en/timezones.php' target='#'>http://us3.php.net/manual/en/timezones.php</a>";
$pname['DefaultTimezone'] = 'paramDefaultTimezone';
$ctrls['DefaultTimezone'] = '';

$param['Currency'] = '€';                              
$label['Currency'] = "Currency";
$value['Currency'] = "currency displayed for costs";
$pname['Currency'] = 'currency';
$ctrls['Currency'] = '';
$param['CurrencyPosition'] = 'after';                              
$label['CurrencyPosition'] = "Currency position";
$value['CurrencyPosition'] = "position of currency displayed for costs";
$pname['CurrencyPosition'] = 'currencyPosition';
$ctrls['CurrencyPosition'] = '=after=before=none=';

$param['crlf05']='';
$label['crlf05']='crlf';

$param['FadeLoadingMode'] = 'true';                              
$label['FadeLoadingMode'] = "Use fading mode for frames refresh";
$value['FadeLoadingMode'] = "'true' or 'false', if set to 'true' screens will appear in a fading motion";
$pname['FadeLoadingMode'] = 'paramFadeLoadingMode';
$ctrls['FadeLoadingMode'] = '=true=false=';

/*
$param['RowPerPage'] = '50';                              
$label['RowPerPage'] = "Number of row per page on main Grid view";
$value['RowPerPage'] = "any integer to define number on rows rendering at a time (see Dojo ...)";
$pname['RowPerPage'] = 'paramRowPerPage';
$ctrls['RowPerPage'] = 'integer';
*/

$param['IconSize'] = '22';                              
$label['IconSize'] = "Icon size on menu tree";
$value['IconSize'] = "'16' for small icons, '22' for medium icons, '32' for big icons";
$pname['IconSize'] = 'paramIconSize';
$ctrls['IconSize'] = '=16=22=32=';

$param['DefaultTheme'] = 'ProjeQtOr';                              
$label['DefaultTheme'] = "Default color theme, proposed while login";
$value['DefaultTheme'] = "select a theme in the list";
$pname['DefaultTheme'] = 'defaultTheme';
$ctrls['DefaultTheme'] = '=ProjeQtOr=ProjeQtOrContrasted=ProjeQtOrLight'
                       . '=blue=blueLight=blueContrast'
                       . '=red=redLight=redContrast'
                       . '=green=greenLight=greenContrast'
                       . '=orange=orangeLight=orangeContrast'
                       . '=grey=greyLight=greyContrast'
                       . '=white=random=';

/*                       
$param['crlf06']='';
$label['crlf06']='crlf';

$param['PathSeparator'] = '/';                              
$label['PathSeparator'] = "Path separator";
$value['PathSeparator'] = "depending on system, '\\' for Windows, '/' for Unix";
$pname['PathSeparator'] = 'paramPathSeparator';
$ctrls['PathSeparator'] = '=/=\\=';
*/

$param['crlf07']='';
$label['crlf07']='crlf';

$param['AttachementDirectory'] = '../files/attach/';                              
$label['AttachementDirectory'] = "Directory to store Attachments";
$value['AttachementDirectory'] = "any valid directory, set to empty string to disable attachment<br/><b>Security hint :</b> move it ouside web access";
$pname['AttachementDirectory'] = 'paramAttachementDirectory';
$ctrls['AttachementDirectory'] = '';

$param['AttachementMaxSize'] = 1024*1024*2;                              
$label['AttachementMaxSize'] = "Max file size for attachment";
$value['AttachementMaxSize'] = "size in bytes (1024 * 1024 * MB)";
$pname['AttachementMaxSize'] = 'paramAttachementMaxSize';
$ctrls['AttachementMaxSize'] = 'integer';

$param['crlf08']='';
$label['crlf08']='crlf';

$param['ReportTempDirectory'] = '../files/report/';                              
$label['ReportTempDirectory'] = "Temp directory for reports";
$value['ReportTempDirectory'] = "any valid directory in the web structure";
$pname['ReportTempDirectory'] = 'paramReportTempDirectory';
$ctrls['ReportTempDirectory'] = '';

$param['MemoryLimitForPDF'] = '512';                              
$label['MemoryLimitForPDF'] = "Memory limit for PDF reports";
$value['MemoryLimitForPDF'] = "any numeric value, for size in MB";
$pname['MemoryLimitForPDF'] = 'paramMemoryLimitForPDF';
$ctrls['MemoryLimitForPDF'] = '';

$param['crlf09']='';
$label['crlf09']='crlf';

$param['logFile'] = '../files/logs/projeqtor_${date}.log';                              
$label['logFile'] = "Log file name";
$value['logFile'] = 'any valid file name, may contain \'${date}\' to get 1 file a day<br/><b>Security hint :</b> move it ouside web access';
$pname['logFile'] = 'logFile';
$ctrls['logFile'] = '';

$param['logLevel'] = '2';                              
$label['logLevel'] = "Log level";
$value['logLevel'] = "'4' for script tracing, '3' for debug, '2' for general trace, '1' for error trace, '0' for none";
$pname['logLevel'] = 'logLevel';
$ctrls['logLevel'] = '=4=3=2=1=0=';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"
    djConfig='modulePaths: {i18n: "../../tool/i18n"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>'></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js"></script>
  <script type="text/javascript"> 
    dojo.require("dojo.parser");
    dojo.require("dojo.i18n");
    dojo.require("dojo.date");
    dojo.require("dojo.date.locale");
    dojo.require("dojo.number");
    dojo.require("dijit.Dialog"); 
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.FilteringSelect");
    var fadeLoading=<?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramFadeLoadingMode'));?>;
    dojo.addOnLoad(function(){
      currentLocale="<?php echo $currentLocale?>";
      saveResolutionToSession();
      saveBrowserLocaleToSession();
      dijit.Tooltip.defaultPosition=["below","right"];
      //dojo.byId('login').focus();
      <?php 
      if (array_key_exists('theme',$_SESSION) ) {
        echo "dojo.byId('body').className='" . $_SESSION['theme'] . "';";
      }
      ?>
      var changePassword=false;
      hideWait();
    }); 
  </script>
</head>

<body id="body" class="ProjeQtOr" onLoad="hideWait();" style="overflow: auto; ">
  <div id="waitLogin" >
  </div> 
  <table align="left" valign="top" width="100%" height="100%" class="background">
    <tr height="10%">
      <td rowspan="2" width="80px" valign="top">
      </td>
      <td width="10px" valign="top">
        <img style="height: 54px" src="img/title.gif" />
      </td>
      <td>
        <div class="siteH1" >Configuration</div>
        <br/>
        <div class="siteH2">This screen will help you configure Project'Or RIA at first run.</div>
        <br/>
      </td>
    </tr>
    <tr height="90%">
      <td colspan="3" align="left" valign="top">
          <form  dojoType="dijit.form.Form" id="configForm" jsId="configForm" name="configForm" encType="multipart/form-data" action="" method="POST" >
            <script type="dojo/method" event="onSubmit" >
              loadContent("../tool/configCheck.php","configResultDiv", "configForm");
              return false;        
            </script>
            <table>
            <?php foreach ($param as $par=>$val) {
              if ($label[$par]=='crlf') {?>
              <tr><td colspan="4">&nbsp;</td></tr>
              <?php } else {?>
              <tr>     
                <td class="label" style="width:300px"><label style="width:300px"><?php echo $label[$par]?>&nbsp;:&nbsp;</label></td>
                <td>
                <?php if (substr($ctrls[$par],0,1)=='=') {?>
                <select id="param[<?php echo $par;?>]" class="input" name="param[<?php echo $par;?>]" 
                   style="width:300px" dojoType="dijit.form.FilteringSelect" 
                   value="<?php echo $val;?>" >
                 <?php $split=explode('=',$ctrls[$par]);
                 foreach($split as $val) {
                   if ($val!='=' and $val) {
                   	 echo '<option value="'.$val.'"';
                   	 if ($val==$param[$par]) {echo ' selected="selected" ';}
                   	 echo '>';
                   	 echo $val;
                   	 echo '</option>';
                   }
                 }?>
                </select>                    
                <?php } else {?>
                <input id="param[<?php echo $par;?>]" name="param[<?php echo $par;?>]" 
                   style="width:300px" type="text"  dojoType="dijit.form.TextBox" 
                   value="<?php echo $val;?>" />
                <?php }?>
                </td>
                <td>
                &nbsp;&nbsp;
                  <input id="pname[<?php echo $par;?>]" name="pname[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $pname[$par];?>" />
                  <input id="label[<?php echo $par;?>]" name="label[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $label[$par];?>" />
                  <input id="value[<?php echo $par;?>]" name="value[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $value[$par];?>" />
                  <input id="ctrls[<?php echo $par;?>]" name="ctrls[<?php echo $par;?>]" type="hidden"
                   value="<?php echo $ctrls[$par];?>" /> 
                </td>
                <td>
                   <?php echo $value[$par]?>
                </td>
              </tr>
            <?php } 
              }?>
              <tr><td colspan="4">&nbsp;</td></tr>
              <tr>
                <td class="label" style="width:300px"><label style="width:300px">Parameter file name&nbsp;:&nbsp;</label></td>
                <td><input id="location" name="location" 
                   style="width:300px" type="text"  dojoType="dijit.form.TextBox" 
                   value="../files/config/parameters.php" />
                </td>
                <td></td>
                <td>a php file name where to store parameters<br/><b>Security hint :</b> move it ouside web access</td>
              </tr>
              <tr><td colspan="4">&nbsp;</td>
              </tr>
              <tr>
                <td></td>
                <td colspan="3">
                  <button tabindex="4" type="submit" id="configButton" dojoType="dijit.form.Button" showlabel="true">OK
                    <script type="dojo/connect" event="onClick" args="evt">
                    return true;
                    </script>
                  </button>
                </td>
              </tr>
              <tr><td colspan="4">&nbsp;</td></tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="3">
                  <div id="configResultDiv" name="configResultDiv" dojoType="dijit.layout.ContentPane" region="center" 
                    style="width:100%; border: 0px solid black; overflow: auto;">
					<br/><br/><br/><br/><br/>
                  </div>
				  <br/>
                </td>
              </tr>
            </table>
          </form>
      </td>
    </tr>
  </table>
</body>
</html>

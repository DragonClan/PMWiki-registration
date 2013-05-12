<?php if (!defined('PmWiki')) exit();
SDV($SiteAdmin, 'admin@tulpa.info.pl');
SDV($NewUserMessage, "To register as a new user, simply 
fill out the form below. 
Account information and generated password will be sent on your mail. ");

Markup("userauth-newuser", "_end","/\\(:newuser:\\)/", UserauthNewUser());

function rand_string( $length ) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	return substr(str_shuffle($chars),0,$length);
}

function UserauthRegisterDo($arg){
  //create new user with username $arg['uname'] and pass $arg['password'].
  global $UserInfoObj;
  if($UserInfoObj->DoesUserExist($arg['uname'])){
    //fail... user exists...
    return false;
  }else{
    $UserInfoObj->AddUser($arg['uname']);
    $UserInfoObj->SetUserPassword($arg['uname'],$arg['pass']);
    $abilities_arr = preg_split('/[\s,]+/', "@writers");
    $UserInfoObj->SetUserAbilities($arg['uname'], $abilities_arr);
    $UserInfoObj->PublishChanges();
    return true;
  }
}

function UserauthRegisterForm($arg) {
  global $ScriptUrl,$HTMLStartFmt,$HTMLEndFmt,$RegisterMessage;
  $uname = $arg['uname'];
  $email = $arg['email'];
  $errs = $arg['errors'];
  return <<<FORM
    <p><b>$errs</b></p>
    <form name='regform' action='{$_SERVER['REQUEST_URI']}' method='post'>
    <table width='45%'>
    <tr><td>Username:</td>
    <td><input type='text' name='uname' value='$uname' /></td></tr>
    <tr><td>Password:</td>
    <td><input type='password' name='password' value='' /></td></tr>
    <tr><td>Password (again):</td>
    <td><input type='password' name='password2' value='' /></td></tr>
    <tr><td colspan='2' align='center'>
	<input type='submit' value='Register' />
    </td></tr>
    </table>
    </form>
FORM;
}

function UserauthNewUser() {
  global $ScriptUrl,$HTMLStartFmt,$HTMLEndFmt;
  #PrintFmt($pagename,$HTMLStartFmt);
  $arg['uname']   = @$_POST['uname'];
  $arg['email']   = @$_POST['email'];
  $arg['uname']   = preg_replace('/\s/', '', $arg['uname']);
  $arg['rname']   = $arg['uname'];
  $arg['errors']  = '';
  $arg['pass'] = rand_string(8);

  return ($arg['uname'] && $arg['email'])
    ? UserauthNewUserReport($arg)
    : UserauthNewUserForm($arg);
}
function UserauthNewUserReport($arg) {
    global $EmailTitle, $SiteAdmin;
    if(UserauthRegisterDo($arg)) {
	$failed = <<<FAILED
	<p>Username already taken.</p>
FAILED;
        $crypt_msg = <<<CRYPT
    
CRYPT;
        return (false) ? $crypt_msg : $failed;
    }

    UserauthNewUserEmail($arg);
    $rname = $arg['rname'];
    $email = $arg['email'];
    $thanks = <<<THANKS

<h2>New Account Processing</h2>

<p>Thank you, <b>$rname</b> for creating an account on the $EmailTitle website. A
verification email has been sent to "<b>$email</b>." </p>
<br />
<p>Webmaster, $EmailTitle</p>

THANKS;
    $uname = $arg['uname'];
    $crypt_msg = <<<CRYPT
    
CRYPT;
    return (false) ? $crypt_msg : $thanks;
}
function UserauthNewUserForm($arg) {
  global $ScriptUrl,$HTMLStartFmt,$HTMLEndFmt,$NewUserMessage;
  $uname = $arg['uname'];
  $email = $arg['email'];
  $pass = $arg['pass'];
  $errs = $arg['errors'];
  return<<<FORM
<p><b>$errs</b></p>
<form action="{$_SERVER['REQUEST_URI']}" method='POST'>
  <table width='45%'>
  <tr><td colspan='2'>
  </td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>
    Username:</td><td><input type='text' name='uname' value='$uname' /><br />
  <tr><td>
  <tr><td>
    Email Address:</td><td><input type='text' name='email' value='$email' /><br />
  <tr><td>&nbsp;</td></tr>
  </td></tr>
  <tr><td colspan='2' align='center'>
    <input type='submit' value='Register' />
    <input type='hidden' name='n' value='$FullName' />
    <input type='hidden' name='action' value='nuser' />
  </table>
</form>
FORM;
}

function UserauthNewUserEmail($arg) {
    global $SiteAdmin; global $EmailTitle;
    $rname = $arg['rname'];
    $uname = $arg['uname'];
    $pass = $arg['pass'];
    $msg =<<<MESSAGE
$rname, 

You have received this email because somebody, hopefully you, 
has requested an account on wiki.tulpa.info If you feel this email was sent
in error, delete it and no further action will be taken.

Username: $uname
Password: $pass

Regards,
Webmaster, $EmailTitle

MESSAGE;
    $msg = wordwrap($msg, 70, "\n");
    $subject   = "Your $EmailTitle User Account Request";
    mail($arg['email'], $subject, $msg, "From: $SiteAdmin\r\n");
}

?>

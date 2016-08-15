<html>
<body>

<form action="" method="post"> <input type="hidden" name="posted" value="1" />
Database Hostname: <input type="text" name="mysql_host"><br>
Database Username: <input type="text" name="mysql_username"><br>
Database Password: <input type="text" name="mysql_password"><br>
Database Name: <input type="text" name="mysql_database"><br>
File Name: <input type="text" name="filename"><br>
<input type="submit">
</form>

</body>
</html>

<?php

// Name of the file
$mysql_host = $_POST["mysql_host"];
$mysql_username = $_POST["mysql_username"]; 
$mysql_password = $_POST["mysql_password"];
$mysql_database = $_POST["mysql_database"]; 
$filename = $_POST["filename"]; 



if 

(@$_POST['posted']=='1' and preg_match("/^[a-zA-Z0-9 \s]+$/", $_POST['posted']))
 {
$connect = mysqli_connect($mysql_host, $mysql_username, $mysql_password);
mysqli_select_db($connect, $mysql_database) or die('Error selecting MySQL database: ' . mysqli_error($connect));

$newfilename = "temp.sql";
copy($filename, $newfilename);

// Select database


$oldMessage = "utf8mb4_unicode_ci";
$deletedFormat = "utf8_unicode_ci"; 

$oldMessage2 = "utf8mb4";
$deletedFormat2 = "utf8";

$oldMessage3 = "utf8_general_ci";
$deletedFormat3 = "utf8mb4_general_ci";

//read the entire string
$str=file_get_contents('temp.sql');
$str=str_replace("$oldMessage", "$deletedFormat",$str);
file_put_contents('temp.sql', $str);

$str2=file_get_contents('temp.sql');
$str2=str_replace("$oldMessage2", "$deletedFormat2",$str2);
file_put_contents('temp.sql', $str2);

$str3=file_get_contents('temp.sql');
$str3=str_replace("$oldMessage3", "$deletedFormat2",$str3);
file_put_contents('temp.sql', $str3);


// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file($newfilename);
// Loop through each line





foreach ($lines as $line)
{
// Skip it if it's a comment
if (substr($line, 0, 2) == '--' || $line == '')
    continue;

// Add this line to the current segment
$templine .= $line;
// If it has a semicolon at the end, it's the end of the query
if (substr(trim($line), -1, 1) == ';')
{
    // Perform the query
    mysqli_query($connect, $templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error() . '<br /><br />');
    // Reset temp variable to empty
    $templine = '';
}
}
 echo "Tables imported successfully";
 $suicidesuccessful = unlink('temp.sql') ;

} 

?>
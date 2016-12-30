<!DOCTYPE html>
<html>
<head>
<h1 align = "center">Server and Network Device Monitoring Tool</h1><br>
<title>Assignment 2</title>
</head>
<body style="width:80%";>
<?php
	include "db.php";

	$conn = mysqli_connect($host, $username, $password, $database, $port);

	if (!$conn)
	{
	   die("Connection failed: " . mysqli_connect_error());
	}

	mysqli_select_db($conn,"$database");	
	
	$create1 = mysqli_query($conn,"CREATE TABLE IF NOT EXISTS myserver (id int (11) NOT NULL AUTO_INCREMENT, IP tinytext NOT NULL, PORT int (11) NOT NULL, COMMUNITY tinytext NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET= latin1 AUTO_INCREMENT=1;");
	
	$create2 = mysqli_query($conn,"CREATE TABLE IF NOT EXISTS mydevice (id int (11) NOT NULL AUTO_INCREMENT, IP tinytext NOT NULL, PORT int (11) NOT NULL, COMMUNITY tinytext NOT NULL, interfaces tinytext NOT NULL, selectinter tinytext NOT NULL, graph tinytext NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET= latin1 AUTO_INCREMENT=1;");

	$result = mysqli_query($conn,"SELECT * FROM DEVICES");
?>
<table style = "width: 120%; text-align: center; border: 1px solid black;">
<caption><h3>DEVICES</h3></caption>
<tr>
<th>IP</th>
<th>PORT</th>
<th>COMMUNITY</th>
</tr>

<?php
	while($row = mysqli_fetch_array($result)) 
	{
?>
<tr>
<td><?php echo $row["IP"]; ?></td>
<td><?php echo $row["PORT"]; ?></td>
<td><?php echo $row["COMMUNITY"]; ?></td>
</tr>
<?php 	}?>
</table><br>

<?php
	$results = mysqli_query($conn,"SELECT * FROM myserver");
	$resultd = mysqli_query($conn,"SELECT * FROM mydevice");
?>
<table style = "width: 120%; text-align: center; border: 1px solid black;">
<caption><h3>My_Servers</h3></caption>
<tr>
<th>IP</th>
<th>PORT</th>
<th>COMMUNITY</th>
</tr>

<?php
	while($row = mysqli_fetch_array($results)) 
	{
?>
<tr>
<td><?php echo $row["IP"]; ?></td>
<td><?php echo $row["PORT"]; ?></td>
<td><?php echo $row["COMMUNITY"]; ?></td>
</tr>
<?php 	}?>
</table><br>

<form method="get" action="result.php">
<table style = "width: 120%; text-align: center; border: 1px solid black; table-layout:fixed; word-wrap:break-word; overflow:scroll;">
<caption><h3>My_Devices</h3></caption>
<tr>
<th>IP</th>
<th>PORT</th>
<th>COMMUNITY</th>
<th>Total Interfaces</th>
<th>Selected Interfaces</th>
<th>Graph Interfaces</th>
<th>Aggregate</th>
</tr>
<?php
	while($row = mysqli_fetch_array($resultd)) 
	{
$id = $row["id"];
$ip = $row["IP"];
$ports = $row["PORT"];
$com = $row["COMMUNITY"];

$ex = explode (",",$row['selectinter']);
sort($ex);
?>
<tr>
<td><?php echo $row["IP"]; ?></td>
<td><?php echo $row["PORT"]; ?></td>
<td><?php echo $row["COMMUNITY"]; ?></td>
<td><?php echo $row["interfaces"]; ?></td>
<td><?php echo $row["selectinter"]; ?></td>
<td><?php foreach($ex as $ex2)
{
echo "<input type='checkbox' name=dev[" . $ip . '_' . $ports . '_' . $com . '_' . $id . "][] value='$ex2'>";
echo $ex2;	
}
?>
</td>
<td><?php echo "<input type ='checkbox' name='aggregate' value='aggregate'>" ?></td>
</tr>
<?php }
?>
</table>
<br>
<fieldset>
<legend>Server Metrics</legend>
    CPULoad<input type="checkbox" name="cpu">&emsp;
    ReqPerSec<input type="checkbox" name="reqpersec">&emsp;
    BytesPerReq<input type="checkbox" name="bpreq">&emsp;
    BytesPerSec<input type="checkbox" name="bpsec">&emsp;
    <input type="submit" name="submitmetric" value="Prepare Graphs">
</fieldset>
</form>
<br><br>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<fieldset>
<legend>Insert or Remove a Server/Device</legend>
    IP<input type="text" name="ip">&emsp;
    PORT<input type="number" name="port">&emsp;
    COM<input type="text" name="com">&emsp;
    Server<input type="radio" name="type" value="server">&emsp;
    Device<input type="radio" name="type" value="device">&emsp;
    <input type="submit" name="submitins" value="Insert">&emsp;
    <input type="submit" name="submitdel" value="Remove">&emsp;
</fieldset>
</form><br>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<fieldset>
<legend>Select Interfaces to probe</legend>
    IP<input type="text" name="ip">&emsp;
    PORT<input type="number" name="port">&emsp;
    COM<input type="text" name="com">&emsp;
    Interfaces<input type="text" name="int" placeholder="x,x,x">&emsp;
    <input type="submit" name="submitint" value="Select">
</fieldset>
</form><br>
</form><br><br>


<?php

$ip = $_POST["ip"];
$ports = $_POST["port"];
$com = $_POST["com"];
$type = $_POST["type"];
$sel = $_POST["int"];
$graph = $_POST["grint"];

	if(isset($_REQUEST['submitins']))
	{
		if ($type=='server')
		{
	 	$req = mysqli_query($conn,"INSERT INTO myserver (IP,PORT,COMMUNITY) VALUES ('$ip','$ports','$com')"); 	 
		}
		
		if ($type=='device')
		{
	 	$req = mysqli_query($conn,"INSERT INTO mydevice (IP,PORT,COMMUNITY) VALUES ('$ip','$ports','$com')"); 	 
		}
	}

	if(isset($_REQUEST['submitdel']))
	{
		if ($type=='server')
		{
	 	$req = mysqli_query($conn,"DELETE FROM myserver WHERE IP='$ip' AND PORT='$ports' AND COMMUNITY='$com' "); 	 
		}
		
		if ($type=='device')
		{
	 	$req = mysqli_query($conn,"DELETE FROM mydevice WHERE IP='$ip' AND PORT='$ports' AND COMMUNITY='$com'"); 	 
		}
	}

	if(isset($_POST['submitint']))
	{
	 	$req = mysqli_query($conn,"UPDATE mydevice SET selectinter='$sel' WHERE IP='$ip' AND PORT='$ports' AND COMMUNITY='$com'"); 	 
	}

	if(isset($_POST['submitgraph']))
	{
	 	$req = mysqli_query($conn,"UPDATE mydevice SET graph='$graph' WHERE IP='$ip' AND PORT='$ports' AND COMMUNITY='$com'"); 	 
	}

?>
<br><br><br><footer><center>Rohit Pothuraju</center></footer>
</html>

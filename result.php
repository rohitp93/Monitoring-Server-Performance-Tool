<!DOCTYPE html>
<html>
<head>
<h1 align = "center">Graphs</h1><br>
<title>Assignment 2</title>
</head>

<?php
	include "db.php";

	$conn = mysqli_connect($host, $username, $password, $database, $port);

	if (!$conn)
	{
	   die("Connection failed: " . mysqli_connect_error());
	}
	
	 mysqli_select_db($conn,"$database");	

	 $result2 = mysqli_query($conn,"SELECT * FROM myserver");

	 $retr = $_GET['dev'];

	 $opts = array( "--start", "-1d","--vertical-label=Bytes per second");

	 foreach ($retr as $inp => $out)
	 {
	 $inp1 = explode ("_",$inp);

	 $ip = $inp1[0];
	 $ports = $inp1[1];
	 $com = $inp1[2];
	 $id = $inp1[3];

	 $comb_in = array();
	 $comb_out = array();

	 foreach ($out as $int)
	 {

	 $bin = "byteIn$id"."$int";
	 $bout = "byteOut$id"."$int";
	 $aggin = "aggIN"."$id";
	 $aggout = "aggOUT"."$id";

	 array_push ($comb_in,$bin);
	 array_push ($comb_out,$bout);
	 
	 $rand1 = '#' . dechex(rand(0x000000, 0xFFFFFF));
	 $rand2 = '#' . dechex(rand(0x000000, 0xFFFFFF));
	 $rand3 = '#' . dechex(rand(0x000000, 0xFFFFFF));
	 $rand4 = '#' . dechex(rand(0x000000, 0xFFFFFF));

	 array_push ($opts,"DEF:$bin=$ip\:$ports\:$com.rrd:bytesIn$int:AVERAGE",
                 	   "DEF:$bout=$ip\:$ports\:$com.rrd:bytesOut$int:AVERAGE",
		 	   "AREA:$bin"."$rand1".":In traffic $ip-$int",
                 	   "LINE1:$bout"."$rand2".":Out traffic $ip-$int\\r",
                 	   "GPRINT:$bin:MAX:Max In\:%6.2lf %SBps",
                 	   "GPRINT:$bin:AVERAGE:Avg In\:%6.2lf %SBps",
                 	   "GPRINT:$bin:LAST:Current In\:%6.2lf %SBps\\j",
		 	   "GPRINT:$bout:MAX:Max Out\:%6.2lf %SBps",
                 	   "GPRINT:$bout:AVERAGE:Avg Out\:%6.2lf %SBps",
                 	   "GPRINT:$bout:LAST:Current Out\:%6.2lf %SBps\\j"
               		);

	 $size1 = sizeof($comb_in);
	 $size2 = sizeof($comb_out);

	 $glue1 = implode(",",$comb_in);
	 $glue2 = implode(",",$comb_out);

	 }

	if (isset($_GET['aggregate']))
	{
	
	$aggIN = "CDEF:$aggin=". $glue1 .",+";
	$aggOUT = "CDEF:$aggout=". $glue2 .",+";

	array_push ($opts,$aggIN,$aggOUT,"AREA:$aggin"."$rand3".":Agg In traffic ",
                 	   "LINE1:$aggout"."$rand4".":Agg Out traffic\\r",
                 	   "GPRINT:$aggin:MAX:Max In\:%6.2lf %SBps",
                 	   "GPRINT:$aggin:AVERAGE:Avg In\:%6.2lf %SBps",
                 	   "GPRINT:$aggin:LAST:Current In\:%6.2lf %SBps\\j",
		 	   "GPRINT:$aggout:MAX:Max Out\:%6.2lf %SBps",
                 	   "GPRINT:$aggout:AVERAGE:Avg Out\:%6.2lf %SBps",
                 	   "GPRINT:$aggout:LAST:Current Out\:%6.2lf %SBps\\j");

	}	
	}
		$ret = rrd_graph("DEVICES.png", $opts);

if( !is_array($ret) )
  {
    $err = rrd_error();
    echo "rrd_graph() ERROR: $err\n";
  }

?>
<div>
<h4 align =center><?php echo "DEVICES"?><br><img src=<?php echo "./DEVICES.png";?> alt="Graph">
</h4>
</a><br>
</div>

<?php

	while($row = mysqli_fetch_array($result2))
	{
	 $ip = $row["IP"];
	 $ports = $row["PORT"];
	 $com = $row["COMMUNITY"];
 #--x-grid MINUTE:10:HOUR:1:HOUR:4:0:%X

	 $rand5 = '#' . dechex(rand(0x000000, 0xFFFFFF));
	 $rand6 = '#' . dechex(rand(0x000000, 0xFFFFFF));
	 $rand7 = '#' . dechex(rand(0x000000, 0xFFFFFF));
	 $rand8 = '#' . dechex(rand(0x000000, 0xFFFFFF));

	 $opts = array( "--start", "-1d","--vertical-label=Bytes per second");

	 if (isset ($_GET['cpu']))
	 {

	  $cpu = $_GET['cpu'];
	  array_push ($opts,"DEF:cpuload=$ip\:$ports\:$com.rrd:CPULoad:AVERAGE","LINE1:cpuload"."$rand5:CPU $ip\\r","GPRINT:cpuload:MAX:Max CPU\:%.6lf ","GPRINT:cpuload:AVERAGE:Avg CPU\:%0.6lf ","GPRINT:cpuload:LAST:Current CPU\:%0.6lf \\j");
	 }

	 if (isset($_GET['reqpersec']))
	 {
	  $rps = $_GET['reqpersec'];
	  array_push ($opts,"DEF:reqpersec=$ip\:$ports\:$com.rrd:ReqPerSec:AVERAGE","LINE2:reqpersec"."$rand6:Req Per Sec $ip\\r","GPRINT:reqpersec:MAX:Max RPS\:%0.6lf ","GPRINT:reqpersec:AVERAGE:Avg RPS\:%0.6lf ", "GPRINT:reqpersec:LAST:Current RPS\:%0.6lf \\j");
	 }

	 if (isset ($_GET['bpreq']))
	 {
	  $bpr = $_GET['bpreq'];
	  array_push ($opts,"DEF:bytespersec=$ip\:$ports\:$com.rrd:BytesPerSec:AVERAGE","LINE3:bytespersec"."$rand7:Bytes Per Sec $ip\\r","GPRINT:bytespersec:MAX:Max BPS\:%4.2lf %SBps","GPRINT:bytespersec:AVERAGE:Avg BPS\:%4.2lf %SBps","GPRINT:bytespersec:LAST:Current BPS\:%4.2lf %SBps\\j");
	 }

	 if (isset ($_GET['bpsec']))
	 {
	  $bps = $_GET['bpsec'];
	  array_push ($opts,"DEF:bytesperreq=$ip\:$ports\:$com.rrd:BytesPerReq:AVERAGE","LINE4:bytesperreq"."$rand8:Bytes Per Req $ip\\r","GPRINT:bytesperreq:MAX:Max BPR\:%4.2lf %SBps","GPRINT:bytesperreq:AVERAGE:Avg BPR\:%4.2lf %SBps","GPRINT:bytesperreq:LAST:Current BPR\:%4.2lf %SBps\\j");
	 }
		 
		$ret = rrd_graph("SERVERS.png", $opts);

?>
<div>
<h4 align =center><?php echo "SERVERS"?><br><img src=<?php echo "./SERVERS.png";?> alt="Graph">
</h4>
</a><br>
</div>

<?php	

	}
?>
<br><br><br><footer><center>Rohit Pothuraju</center></footer>
</html>



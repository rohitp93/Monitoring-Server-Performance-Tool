#!usr/bin/perl

use DBI;    
use Cwd;
use Net::SNMP qw(snmp_dispatcher oid_lex_sort oid_base_match);
use RRD::Simple ();
use Data::Dumper qw(Dumper);
use LWP::Simple;

require "dbpath.pl";
require "$realpath";

while (1)
{

	$dsn = "DBI:mysql:$database:$host:$port";
	$dbh = DBI->connect($dsn,$username,$password);   

	$bth = $dbh->prepare("SELECT * FROM myserver");
	$bth->execute() or die $DBI::errstr;

	$cth = $dbh->prepare("SELECT * FROM mydevice");
	$cth->execute() or die $DBI::errstr;

%server;
%device;	

while(@row = $bth->fetchrow_array())
{
	$ip = $row[1];
	$ports = $row[2];
	$com = $row[3];

	$server{"$ip:$ports:$com"} = {
					ip => $ip,
					port => $ports,
					community => $com
				      };

	$url = "http://$ip:$ports/server-status?auto";
	$server_status = get($url);

	my ($cpuload,$reqpersec,$bytespersec,$bytesperreq);

	$cpuload = $1 if ($server_status =~ /CPULoad:\ (\d*\.\d*)/);
	$bytespersec = $1 if ($server_status =~ /BytesPerSec:\ (\d*\.\d*)/);		
	$bytesperreq = $1 if ($server_status =~ /BytesPerReq:\ (\d*\.\d*)/);
	$reqpersec = $1 if ($server_status =~ /ReqPerSec:\ (\d*\.\d*)/);


	$server{"$ip:$ports:$com"}{modstatus} = {
						  cpu => $cpuload,					
						  bsec => $bytespersec,
						  breq => $bytesperreq,
						  reqsec => $reqpersec
						};
}
	foreach (keys (%server))
	{
	 @update = ();
	 @create = ();

	 $rrdfile = "$_.rrd";

	 $rrd = RRD::Simple->new(
         			 file => $rrdfile,
         			 cf => [ qw(AVERAGE) ],
         			 default_dstype => "GAUGE",
         			 on_missing_ds => "add"
     	 				); 

	$cpu = $server{"$ip:$ports:$com"}{modstatus}{cpu};
	$bsec = $server{"$ip:$ports:$com"}{modstatus}{bsec};
	$breq = $server{"$ip:$ports:$com"}{modstatus}{breq};
	$reqsec = $server{"$ip:$ports:$com"}{modstatus}{reqsec};
	 
	 push @update,"CPULoad"=>"$cpu","BytesPerSec"=>"$bsec","BytesPerReq"=>"$breq","ReqPerSec"=>"$reqsec";
	 push @create,"CPULoad"=>"GAUGE","BytesPerSec"=>"GAUGE","BytesPerReq"=>"GAUGE","ReqPerSec"=>"GAUGE";
	}	

	$rrd->create(@create) unless -f $rrdfile;
	$rrd->update(time(),@update);

while(@row = $cth->fetchrow_array())
{
	$ip = $row[1];
	$ports = $row[2];
	$com = $row[3];

	$device{"$ip:$ports:$com"} = {
					ip => $ip,
					port => $ports,
					community => $com
				      };

	($session, $error) = Net::SNMP->session(
   						-hostname    => $ip,
   						-community   => $com,
   						-port        => $ports,
   						-nonblocking => 1,
						);

	$device{"$ip:$ports:$com"}{session} = $session;

	if (!defined($session->get_table(-baseoid  => '1.3.6.1.2.1.2.2.1.1',
                                 	 -callback => [\&cback,$ip,$ports,$com])))
	{
   		printf("ERROR: %s.\n", $session->error());
	}
}

	snmp_dispatcher();

sub cback
{
	($session, $ip, $ports, $com) = @_;

	foreach (keys (%{$session->var_bind_list()})) 
	{

		$device{"$ip:$ports:$com"}{interfaces}{$_} = $session->var_bind_list->{$_};	
	}

}

	foreach(keys (%device))
	{
	$ifin = '1.3.6.1.2.1.2.2.1.10.';
	$ifout = '1.3.6.1.2.1.2.2.1.16.';	

	($ip,$ports,$com) = split/:/,$_;	
	@ifup = ();
	@oct = ();
	
	foreach (values (%{$device{"$ip:$ports:$com"}{interfaces}}))
		{
		 push @ifup,$_;
		}		

	$int = join (",",@ifup);

	$rth = $dbh->prepare("UPDATE mydevice SET interfaces='$int' WHERE IP='$ip' AND PORT ='$ports' AND COMMUNITY ='$com'");		
	$rth->execute() or die $DBI::errstr;

	$cth = $dbh->prepare("SELECT * FROM mydevice");
	$cth->execute() or die $DBI::errstr;

	while(@row = $cth->fetchrow_array())
	{
	@pro = ();
	$ip = $row[1];
	$ports = $row[2];
	$com = $row[3];
	$sel = $row[5];

	@pro = split/,/,$sel;

	foreach(@pro)
	{
	push @oct,$ifin.$_,$ifout.$_;
	}

	while(@oct)
	{
	@ifin = splice (@oct, 0, 40);	

	$device{"$ip:$ports:$com"}{session}->get_request(
                          				-callback        => [\&cback2,$ip,$ports,$com],
                          				-varbindlist     => \@ifin,			
                       					);	
	}
	}
	snmp_dispatcher();
	}

sub cback2
{
	($session,$ip,$ports,$com) = @_;

		foreach (keys (%{$session->var_bind_list()}))
		{
			$device{"$ip:$ports:$com"}{bitrate}{$_} = $session->var_bind_list()->{$_};
		}		

}

foreach (keys %device)
{
	 if ($device{"$ip:$ports:$com"}{interfaces}!=0)
  	 {
	 @update = ();
	 @create = ();

	 ($ip,$ports,$com) = split/:/,$_;	

	 $rrdfile = "$_.rrd";

	 $rrd = RRD::Simple->new(
         			 file => $rrdfile,
         			 cf => [ qw(AVERAGE) ],
         			 default_dstype => "COUNTER",
         			 on_missing_ds => "add"
     	 				);

	foreach (values %{$device{"$ip:$ports:$com"}{interfaces}})
	{			 

	 $inoct = $device{"$ip:$ports:$com"}{bitrate}{$ifin.$_};
	 $outoct = $device{"$ip:$ports:$com"}{bitrate}{$ifout.$_};
	 $bytesIN = "bytesIn$_";
	 $bytesOUT = "bytesOut$_";
	 
	 push @update,"$bytesIN"=>"$inoct","$bytesOUT"=>"$outoct";
	 push @create,"$bytesIN"=>"COUNTER","$bytesOUT"=>"COUNTER";
	}
	
	$rrd->create(@create) unless -f $rrdfile;
	$rrd->update(time(),@update);
	}
}

#print Dumper \%server;
#print Dumper \%device;

sleep (60);
}



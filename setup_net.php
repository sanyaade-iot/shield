<?php

include_once "/lib/sc_envs.php";

$envs = envs_read();

if(envs_get_net_opt($envs, NET_OPT_DHCP))
	$ip_type = 1; // DHCP
else
	$ip_type = 0; // Static

if(envs_get_net_opt($envs, NET_OPT_IP6))
	$ip6_opt = 1;
else
	$ip6_opt = 0;

if(envs_get_net_opt($envs, NET_OPT_IP6_GUA))
	$ip6_type = 1; // Static
else
	$ip6_type = 0; // DHCP

if(envs_get_net_opt($envs, NET_OPT_IP6_EUI))
	$ip6_eui = 1; // Random
else
	$ip6_eui = 0; // MAC address

if(envs_get_net_opt($envs, NET_OPT_WLAN))
	$net_type = 1;
else
	$net_type = 0;

$pid = pid_open("/mmap/net1");
$wlan_status = pid_ioctl($pid, "get mode");

$wmode    = envs_get_net_opt($envs, NET_OPT_TSF);
$channel  = envs_get_net_opt($envs, NET_OPT_CH);
$eap_type = envs_get_net_opt($envs, NET_OPT_WPA);

$ipaddr  = inet_ntop(substr(envs_find($envs, ENV_CODE_IP4, 0x00), 0, 4));
$netmask = inet_ntop(substr(envs_find($envs, ENV_CODE_IP4, 0x01), 0, 4));
$gwaddr  = inet_ntop(substr(envs_find($envs, ENV_CODE_IP4, 0x02), 0, 4));
$nsaddr  = inet_ntop(substr(envs_find($envs, ENV_CODE_IP4, 0x03), 0, 4));	
	
$ip6addr    = inet_ntop(substr(envs_find($envs, ENV_CODE_IP6, 0x00), 0, 16));
$ip6prefix  = bin2int(substr(envs_find($envs, ENV_CODE_IP6, 0x00), 16, 2), 0, 2);
$ip6gwaddr  = inet_ntop(substr(envs_find($envs, ENV_CODE_IP6, 0x02), 0, 16));
$ip6nsaddr  = inet_ntop(substr(envs_find($envs, ENV_CODE_IP6, 0x03), 0, 16));

$ssid_env       = envs_find($envs, ENV_CODE_WLAN, 0x01);
$ssid_pos 		= strpos($ssid_env, int2bin(0x00, 1));
$ssid		 	= substr($ssid_env, 0, (int)$ssid_pos);
$ssid_raw	 	= bin2hex($ssid);

$shared_key_env = envs_find($envs, ENV_CODE_WLAN, 0x08);	
$shared_key_pos = strpos($shared_key_env, int2bin(0x00, 1));
$shared_key		= substr($shared_key_env, 0, (int)$shared_key_pos);

?>
<!DOCTYPE html>
<html>
<head>
	<title>PHPoC</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=1.0, minimum-scale=0.5, user-scalable=yes">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
	<style type="text/css">
		body { font-family: verdana, Helvetica, Arial, sans-serif, gulim; height:750px; }
		h1 { font-weight: bold; font-family : verdana, Helvetica, Arial, verdana, sans-serif, gulim; font-size:15pt; padding-bottom:5px;}
		table {border-collapse:collapse; width:450px;  font-size:10pt;}
		.theader { font-weight: bold;}
		tr { height :28px;}
		td { padding: 5px 10px; text-align: left;}
		.superHeader {height: 2em; color: white; background-color: rgb(0,153,153); font-size:9pt; position:fixed; left:0; right:0; top:0; z-index:5;  }		
		.right {
		  color: white;
		  position: absolute;
		  right: 1px;
		  bottom: 4px;
		  font-size:9pt;		  
		}	
		.left {
		  color: white;
		  position: absolute;
		  left: 1px;
		  bottom: 4px;
		  font-size:9pt;		  
		}
		.right a, .left a
		{
		  color: white;
		  background-color: transparent;
		  text-decoration: none;
		  margin: 0;
		  padding:0 1ex 0 1ex;
		}			
		.right a:hover, .left a:hover 
		{
		  color: white;
		  text-decoration: underline;
		 }		 
		.midHeader {color: white; background-color: rgb(6, 38, 111);  position:fixed; left:0; right:0; top:1.5em;  z-index:3;}
		.headerTitle {
		  font-size: 250%;
		  font-weight: normal;
		  margin: 0 0 0 4mm;
		  padding: 0.25ex 0 1ex 0;
		  font-family: impact;
		}
		.headerMenu{
			position:relative;
			width: 450px;
			padding: 5px;
		}
		#footer{margin:0 auto; height:auto !important; height:100%; margin-bottom:-100px;  }
		.superFooter {
			height: 2em; color: white; background-color: rgb(6, 38, 111); font-size:9pt; position:fixed; left:0; right:0; bottom:0; z-index:4; 
		}				
		.zebra {background-color : #ECECEC;}
	</style>
	<script type="text/javascript">

	function chkUI()
	{
		chkWlan();
		chkIPGetType();	
		chkHideKey();
		<?php 
		if(ini_get("init_ip6") == "1")	
		{	
		?>
			chkIp6();
		<?php 
		} 
		?>	
			
	}
	
	function chkWlan()
	{
		var phpoc_setup = document.phpoc_setup;
		if(phpoc_setup.wlan_opt[1].checked) // WLAN disable
		{
			phpoc_setup.ip_type[0].disabled = "";
			phpoc_setup.ip_type[1].disabled = "";
			
			phpoc_setup.tsf[0].disabled = "true";
			phpoc_setup.tsf[1].disabled = "true";
			phpoc_setup.tsf[2].disabled = "true";
			phpoc_setup.channel.disabled = "true";
			phpoc_setup.channel_search.disabled = "true";
			phpoc_setup.ap_search.disabled = "true";
			phpoc_setup.ssid.disabled = "true";
			phpoc_setup.shared_key.disabled = "true";
			phpoc_setup.hide_key.disabled = "true";	
		}
		else // WLAN enable
		{
			phpoc_setup.tsf[0].disabled = "";
			phpoc_setup.tsf[1].disabled = "";
			phpoc_setup.tsf[2].disabled = "";
					
			phpoc_setup.ssid.disabled = "";
			phpoc_setup.shared_key.disabled = "";
			phpoc_setup.hide_key.disabled = "";	

			chkChannel();			
		}		
	}
	
	function chkIp6()
	{
		var phpoc_setup = document.phpoc_setup;
		
		if(phpoc_setup.ip6_opt[0].checked) // IPv6 disable
		{
			phpoc_setup.ip6_type[0].disabled = "true";
			phpoc_setup.ip6_type[1].disabled = "true";
			
			phpoc_setup.ip6_eui[0].disabled = "true";
			phpoc_setup.ip6_eui[1].disabled = "true";
			
			phpoc_setup.ip6addr.disabled = "true";
			phpoc_setup.ip6prefix.disabled = "true";
			phpoc_setup.ip6gwaddr.disabled = "true";
			phpoc_setup.ip6nsaddr.disabled = "true";	
		}
		else // IPv6 enable
		{
			phpoc_setup.ip6_type[0].disabled = "";
			phpoc_setup.ip6_type[1].disabled = "";
			
			phpoc_setup.ip6_eui[0].disabled = "";
			phpoc_setup.ip6_eui[1].disabled = "";
					
			phpoc_setup.ip6addr.disabled = "";
			phpoc_setup.ip6prefix.disabled = "";
			phpoc_setup.ip6gwaddr.disabled = "";
			phpoc_setup.ip6nsaddr.disabled = "";		

			chkIP6GetType();
		}			
	}

	function chkChannel() 
	{
		var phpoc_setup = document.phpoc_setup;		
		
		if(phpoc_setup.tsf[1].checked) //infrastructure
		{	
			phpoc_setup.channel.disabled = "true";						
			phpoc_setup.channel.value = "0";	
			phpoc_setup.channel_search.disabled = "true";
			phpoc_setup.ap_search.disabled = "";
			
			if(phpoc_setup.ip_type[0].checked)	//DHCP
			{
				phpoc_setup.ipaddr.disabled = "true";
				phpoc_setup.netmask.disabled = "true";
				phpoc_setup.gwaddr.disabled = "true";
				phpoc_setup.nsaddr.disabled = "true";
			}	
			else
			{
				phpoc_setup.ipaddr.disabled = "";
				phpoc_setup.netmask.disabled = "";
				phpoc_setup.gwaddr.disabled = "";
				phpoc_setup.nsaddr.disabled = "";
			}
		} 
		else if(phpoc_setup.tsf[2].checked) 	//softap
		{
			phpoc_setup.channel.disabled = "";
			phpoc_setup.channel_search.disabled = "";
			phpoc_setup.ap_search.disabled = "true";
			phpoc_setup.shared_key.disabled = "";
			phpoc_setup.hide_key.disabled = "";
			chkIPGetType();
		}
		else  //ad-hoc
		{
			phpoc_setup.channel.disabled = "";	
			phpoc_setup.channel_search.disabled = "";
			phpoc_setup.ap_search.disabled = "true";
			phpoc_setup.shared_key.disabled = "";
			phpoc_setup.hide_key.disabled = "";
		}
		
		if("<?php echo $wlan_status;?>" == "")
		{
			phpoc_setup.channel_search.disabled = "true";
			phpoc_setup.ap_search.disabled = "true";
		}	
			
	}
	
	function chkIPGetType()
	{
		var phpoc_setup = document.phpoc_setup;
		if(phpoc_setup.ip_type[0].checked)
		{
			phpoc_setup.ipaddr.disabled = "true";
			phpoc_setup.netmask.disabled = "true";
			phpoc_setup.gwaddr.disabled = "true";
			phpoc_setup.nsaddr.disabled = "true";
		}
		else
		{
			phpoc_setup.ipaddr.disabled = "";
			phpoc_setup.netmask.disabled = "";
			phpoc_setup.gwaddr.disabled = "";
			phpoc_setup.nsaddr.disabled = "";
		}
	}
	
	function chkIP6GetType()
	{
		var phpoc_setup = document.phpoc_setup;
		if(phpoc_setup.ip6_type[0].checked)
		{
			phpoc_setup.ip6addr.disabled = "true";
			phpoc_setup.ip6prefix.disabled = "true";
			phpoc_setup.ip6gwaddr.disabled = "true";
			phpoc_setup.ip6nsaddr.disabled = "true";
		}
		else
		{
			phpoc_setup.ip6addr.disabled = "";
			phpoc_setup.ip6prefix.disabled = "";
			phpoc_setup.ip6gwaddr.disabled = "";
			phpoc_setup.ip6nsaddr.disabled = "";
		}
	}
	
	function chkHideKey() 
	{
		var phpoc_setup = document.phpoc_setup;
		if(phpoc_setup.hide_key.checked == true)
		{	
			phpoc_setup.shared_key.type = "password";
		}else {
			phpoc_setup.shared_key.type = "text";
		}
	}	

	function search_ap()
	{
		window.open("setup_search_ap.php", "_blank", "width=600, height=800 scrollbars=yes");	
	}
	
	function search_ch()
	{
		window.open("setup_search_ch.php", "_blank", "width=600, height=800 scrollbars=yes");	
	}
	
	function checkIpForm(ip_addr)
	{
		var filter =  /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
	
		if (ip_addr.match(filter)) 
			result = true;
		else 	
			result = false;		
		
		return result;	
	}	
	
	function excSubmit()
	{			
		var phpoc_setup = document.phpoc_setup;	

		//IP check
		var ip_result = checkIpForm(phpoc_setup.ipaddr.value);
		if(ip_result == false)
		{
			alert("Please check your IP address.");	
			phpoc_setup.ipaddr.focus();
			return;
		}
		
		//subnet check
		var subnet_result = checkIpForm(phpoc_setup.netmask.value);
		if(subnet_result == false)
		{
			alert("Please check your Subnet mask.");	
			phpoc_setup.netmask.focus();
			return;
		}
		
		//Gateway check
		var gwaddr_result = checkIpForm(phpoc_setup.gwaddr.value);
		if(gwaddr_result == false)
		{
			alert("Please check your Gateway IP address.");	
			phpoc_setup.gwaddr.focus();
			return;
		}
		
		//DNS check
		var nsaddr_result = checkIpForm(phpoc_setup.nsaddr.value);
		if(nsaddr_result == false)
		{
			alert("Please check your DNS IP address.");	
			phpoc_setup.nsaddr.focus();
			return;
		}

		if (phpoc_setup.wlan_opt[0].checked)
		{
			//SSID check
			if ( phpoc_setup.ssid.value.length > 32) // MAX 32 bytes
			{
				alert("Please check the SSID.");
				phpoc_setup.ssid.focus();
				return;
			}			
			
			//shared key check
			if(phpoc_setup.tsf[2].checked)
			{
				if(phpoc_setup.shared_key.value.length && (phpoc_setup.shared_key.value.length < 8))
				{
					alert("Please check the length of shared key.");
					phpoc_setup.shared_key.focus();
					return;
				}	
			}
		}
		
		//IPv6 prefix check
		if (phpoc_setup.ip6_opt[1].checked)
		{
			if (phpoc_setup.ip6_type[1].checked)
			{
				if (phpoc_setup.ip6prefix.value < 1 || phpoc_setup.ip6prefix.value > 128)
				{	
					alert("Please check the length of IPv6 prefix.");
					phpoc_setup.ip6prefix.focus();
					return;
				}
			}	
		}	
		
		phpoc_setup.submit();
			 
		var ipaddr = phpoc_setup.ipaddr.value;
	    var ip_type;
		
		if (phpoc_setup.ip_type[1].checked == true) 
			ip_type = 0; //static IP address
		else
			ip_type = 1; //DHCP

		if (((ipaddr != '<?php echo $ipaddr;?>' && phpoc_setup.ip_type[1].checked)) || (ip_type != <?php echo $ip_type;?> && phpoc_setup.ip_type[1].checked)) 
		{
			var url = phpoc_setup.ipaddr.value;
			url = "http://" + url + "/setup_net.php";
			alert("Redirect to : " + url);

			var win = window.open(url); 
		}

	}
	</script>
</head>
<body onload="chkUI();">
    <div id="header">
		<div class="superHeader">		
			<div class="left">
			</div>	
			<div class="right">
				<a href="http://www.sollae.co.kr" target="_blank">SOLLAE SYSTEMS</a>
			</div>
		</div>

		<div class="midHeader">
			<center>
				<h1 class="headerTitle"><?php echo system("uname -m");?></h1>
				<div class="headerMenu">
					<div class="left">
						<a href="index.php">HOME</a>| 
						<a href="setup_info.php">INFO</a>| 
						<a href="setup_net.php">SETUP</a>| 
						<a href="setup_time.php">TIME</a>| 
						<a href="setup_app.php">APP</a>	
					</div>
					<div class="right">
						<a href="javascript:excSubmit();">SAVE</a>			
					</div>
				</div>
			</center>
		</div>
		
		<div class="subHeader">
		</div>		
	</div>	
	<br /><br /><br /><br />
	<form name="phpoc_setup" action="setup_net_ok.php" method="post">		
	<center>	
		<hr style="margin:50px 0 -10px 0; width:450px;" size="6" noshade>
		<h1>Network</h1>
		
		<table>
			<tr class="zebra">
				<td width="10%" rowspan="5" class="theader">IPv4</td>
				<td width="35%" class="theader">IP address Type</td>	
				<td>
					<input type="radio" value="1" name="ip_type" onclick="chkIPGetType();" <? if($ip_type == 1) echo "checked" ?> /> Auto IP address<br />
					<input type="radio" value="0" name="ip_type" onclick="chkIPGetType();" <? if($ip_type == 0) echo "checked" ?> /> Static IP address
				</td>
			</tr>
			<tr>
				<td class="theader">IP address</td>	
				<td>
					<input type="text" name="ipaddr" value="<? echo $ipaddr ?>">
					<input type="hidden" name="old_ipaddr" value="<? echo $ipaddr ?>">
				</td>
			</tr>
			<tr class="zebra"> 
				<td class="theader">Subnet mask</td>	
				<td>
					<input type="text" name="netmask" value="<? echo $netmask ?>">
				</td>
			</tr>
			<tr> 
				<td class="theader">Gateway</td>	
				<td>
					<input type="text" name="gwaddr" value="<? echo $gwaddr ?>">
				</td>
			</tr>
			<tr class="zebra"> 
				<td class="theader">DNS Server</td>	
				<td>
					<input type="text" name="nsaddr" value="<? echo $nsaddr ?>"> 
				</td>
			</tr>
		</table>
		
		<?php 
		if(ini_get("init_ip6") == "1")	
		{
		?>
		<br />
		<table>
			<tr class="zebra">
				<td width="10%" rowspan="6" class="theader">IPv6</td>
				<td width="35%" class="theader">IPv6</td>
				<td>
					<input type="radio" value="0" name="ip6_opt" onclick="chkIp6();" <? if($ip6_opt == 0) echo "checked" ?> /> Disable<br />
					<input type="radio" value="1" name="ip6_opt" onclick="chkIp6();" <? if($ip6_opt == 1) echo "checked" ?> /> Enable
				</td>
			</tr>
			<tr>
				<td class="theader">IP address Type</td>	
				<td>
					<input type="radio" value="0" name="ip6_type" onclick="chkIP6GetType();" <? if($ip6_type == 0) echo "checked" ?> /> Auto IP address<br />
					<input type="radio" value="1" name="ip6_type" onclick="chkIP6GetType();" <? if($ip6_type == 1) echo "checked" ?> /> Static IP address
				</td>
			</tr>
			<tr class="zebra">
				<td class="theader">EUI</td>	
				<td>
					<input type="radio" value="0" name="ip6_eui" <? if($ip6_eui == 0) echo "checked" ?> /> MAC Address<br />
					<input type="radio" value="1" name="ip6_eui" <? if($ip6_eui == 1) echo "checked" ?> /> Random 
				</td>
			</tr>
			<tr>
				<td class="theader">IP address</td>	
				<td>
					<input type="text" size="15" name="ip6addr" value="<? echo $ip6addr ?>">
					/ <input type="text" size="3" maxlength="3" name="ip6prefix" value="<? echo $ip6prefix ?>">
				</td>
			</tr>
			<tr class="zebra"> 
				<td class="theader">Gateway</td>	
				<td>
					<input type="text" name="ip6gwaddr" value="<? echo $ip6gwaddr ?>">
				</td>
			</tr>
			<tr> 
				<td class="theader">DNS Server</td>	
				<td>
					<input type="text" name="ip6nsaddr" value="<? echo $ip6nsaddr ?>"> 
				</td>
			</tr>
		</table>
		<?php 
		}
		?>
		
		<hr style="margin:50px 0 -10px 0; width:450px;" size="6" noshade>
		<h1>Wireless LAN</h1>
		<table>
			<tr class="zebra">
				<td width="50%" class="theader">WLAN</td>
				<td>
					<input type="radio" value="1" name="wlan_opt" onclick="chkWlan();" <? if($net_type == 1) echo "checked" ?> /> Enable<br />
					<input type="radio" value="0" name="wlan_opt" onclick="chkWlan();" <? if($net_type == 0) echo "checked" ?> /> Disable
				</td>
			</tr>
			<tr>
				<td class="theader">WLAN mode</td>
				<td>
					<input type="radio" value="0" name="tsf" onclick="chkChannel();" <? if($wmode == 0) echo "checked" ?> /> Ad-hoc<br />
					<input type="radio" value="1" name="tsf" onclick="chkChannel();" <? if($wmode == 1) echo "checked" ?> /> Infrastructure<br />
					<input type="radio" value="2" name="tsf" onclick="chkChannel();" <? if($wmode == 2) echo "checked" ?> /> Soft AP
				</td>
			</tr>
			<tr class="zebra">
				<td class="theader">Channel</td>	
				<td>
					<select name="channel">
						<option value="0" <? if($channel == 0) echo "selected" ?>>Auto</option>
						<option value="1" <? if($channel == 1) echo "selected" ?>>1</option>
						<option value="2" <? if($channel == 2) echo "selected" ?>>2</option>
						<option value="3" <? if($channel == 3) echo "selected" ?>>3</option>
						<option value="4" <? if($channel == 4) echo "selected" ?>>4</option>
						<option value="5" <? if($channel == 5) echo "selected" ?>>5</option>
						<option value="6" <? if($channel == 6) echo "selected" ?>>6</option>
						<option value="7" <? if($channel == 7) echo "selected" ?>>7</option>
						<option value="8" <? if($channel == 8) echo "selected" ?>>8</option>
						<option value="9" <? if($channel == 9) echo "selected" ?>>9</option>
						<option value="10" <? if($channel == 10) echo "selected" ?>>10</option>
						<option value="11" <? if($channel == 11) echo "selected" ?>>11</option>						
						<option value="12" <? if($channel == 12) echo "selected" ?>>12</option>
						<option value="13" <? if($channel == 13) echo "selected" ?>>13</option>						
					</select>
					<button type="button" name="channel_search" onclick="search_ch();">Search</button>

				</td>
			</tr>
			<tr>
				<td class="theader">SSID</td>	
				<td>
					<input type="text" name="ssid" size="15" maxlength="31" value="<? echo $ssid ?>">
					<input type="hidden" name="ssid_raw" size="15" maxlength="31" value="<? echo $ssid_raw ?>">
					<button type="button" name="ap_search" onclick="search_ap();">Search</button>
				</td>
			</tr>
			<tr class="zebra">
				<td class="theader">Shared Key</td>
				<td>
					<input type="text" name="shared_key" size="15" maxlength="63" value="<? echo $shared_key ?>"><br /> 
					(<input type="checkbox" name="hide_key" onclick="chkHideKey()" checked />hide key)
				</td>
			</tr>
			<tr>
				<td class="theader">802.1x</td>
				<td>	
					<input type="hidden" name="eap_type" value="<? echo $eap_type ?>">  				
					<select name="eap" disabled>
						<option value="0" <? if($eap_type == 0) echo "selected" ?>>None</option>
						<option value="1" <? if($eap_type == 1) echo "selected" ?>>EAP-TLS</option>
						<option value="2" <? if($eap_type == 2) echo "selected" ?>>EAP-TTLS</option>
						<option value="3" <? if($eap_type == 3) echo "selected" ?>>PEAP</option>
					</select>
				</td>
			</tr>
		</table>	
	</center>	
	</form>
	<br /><br /><br /><br />
	<div id="footer">
		<div class="superFooter">
		</div>
	</div>	
</body>
</html>

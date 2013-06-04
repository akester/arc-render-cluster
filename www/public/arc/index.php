<?php 
	require '../../application/php-digest-mysql.class.php';
	$auth = new phpAuthMySQL();
	$auth->auth(true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : GoodLife 
Description: A two-column, fixed-width design with dark color scheme.
Version    : 1.0
Released   : 20121013

-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>ARC Render Cluster - ARC</title>
	<link href='http://fonts.googleapis.com/css?family=Oswald:400,300' 
		rel='stylesheet' type='text/css' />
	<link href='http://fonts.googleapis.com/css?family=Abel|Satisfy' 
		rel='stylesheet' type='text/css' />
	<link href="../default.css" rel="stylesheet" type="text/css" media="all" />
	<!--[if IE 6]>
		<link href="default_ie6.css" rel="stylesheet" type="text/css" />
	<![endif]-->
</head>
<body>
	<div id="header-wrapper">
		<div id="header">
			<div id="logo">
				<h1><a href="#">ARC</a></h1>
			</div>
			<div id="menu">
				<ul>
					<li class="current_page_item">
						<a href="#" accesskey="1">Jobs</a>
					</li>
					<li><a href="#" accesskey="2">Files</a></li>
					<li><a href="#" accesskey="3">Account</a></li>
					<li><a href="#" accesskey="4">Donate</a></li>
					<li><a href="#" accesskey="5">Stats</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="footer-wrapper">
		<div id="footer-content">
			<div id="fbox1">
				<h2><span>Job Tasks</span></h2>
				<p>&nbsp;</p>
				<ul class="style2">
					<li class="first"><a href="#">Submit New Job</a></li>
					<li><a href="#">Re-Render Job</a></li>
					<li><a href="#">Change Default Job Settings</a></li>
				</ul>
			</div>
			<div id="fbox2">
				<h2><span>Current Jobs</span></h2>
				<table style="width:100%;text-align:center;">
					<tr>
						<th>Job ID</th>
						<th>File Name</th>
						<th>Start Frame</th>
						<th>End Frame</th>
						<th>Status</th>
						<th>Completion</th>
						<th>Expires</th>
					</tr>
					<tr>
						<td>A-003-48395</td>
						<td>05.2b_comp.blend</td>
						<td>65</td>
						<td>113</td>
						<td>Completed!</td>
						<td>100%</td>
						<td>2013-6-14</td>
					</tr>
					<tr>
						<td>A-003-48458</td>
						<td>falling-blocks.blend</td>
						<td>1</td>
						<td>2685</td>
						<td>Rendering...</td>
						<td>42%</td>
						<td>2013-6-18</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div id="page-wrapper">
		<div id="page">
			<div id="content">
				<div>
					<h2>Donate</h2>
					<p>
						Operating this cluster does incur cost.  If you have
						found this cluster useful, please consider supporting it
						financially with either a one-time or a recurring
						contribution.  Your contributions allow us to continue
						to provide this service to artists everywhere.
					</p>
					<p class="button-style"><a href="#">More Details</a></p>
				</div>
			</div>
			<div id="sidebar">
				<h2>Helping Others</h2>
				<p>
					Our mission is to help others in any way that we can.  We
					built ARC to help artists, but we donate any remaning
					compute power to charities such as Folding at Home.  Even
					if you aren't using the cluster, it still is helping others.
				</p>
			</div>
		</div>
	</div>
	<div id="footer">
		<p>
			Copyright (c) 2012 ARC Render Cluster.  Design by 
			<a href="http://www.freecsstemplates.org/" rel="nofollow">
			FreeCSSTemplates.org</a>.  This design is released under the Creative
			Commons Attribution 2.5 License.
		</p>
		<p>
			Usage of ARC is subject to our Acceotable Use Policy.  Please consider
			supporting this project by donating.
		</p>
	</div>
</body>
</html>

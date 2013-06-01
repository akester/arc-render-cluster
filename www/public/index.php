<?php 
	require '../application/php-digest-mysql.class.php';
	$auth = new phpAuthMySQL();
	$user = $auth->auth(false);
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
	<title>ARC Render Cluster - Home</title>
	<link href='http://fonts.googleapis.com/css?family=Oswald:400,300' 
		rel='stylesheet' type='text/css' />
	<link href='http://fonts.googleapis.com/css?family=Abel|Satisfy' 
		rel='stylesheet' type='text/css' />
	<link href="default.css" rel="stylesheet" type="text/css" media="all" />
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
						<a href="#" accesskey="1">Home</a>
					</li>
					<li><a href="#" accesskey="2">About</a></li>
					<li><a href="#" accesskey="3">Premium</a></li>
					<li><a href="#" accesskey="4">Stats</a></li>
					<li><a href="arc/index.php" accesskey="5">Log In</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div id="footer-wrapper">
		<div id="footer-content">
			<div id="fbox1">
				<h2><span>Enabling</span> Art</h2>
				<p>
					ARC is a render cluster that is free to use by any 3D
					artist.  Our goal is to provide free, high power computing
					resources to artits anywhere.  We currently support Blender
					and LuxRender.
				</p>
				<p>&nbsp;</p>
				<ul class="style2">
					<li class="first"><a href="#">Services We Offer</a></li>
					<li><a href="#">Project Information</a></li>
					<li><a href="#">How to Support ARC</a></li>
					<li><a href="#">Supporting Local Artists</a></li>
					<li><a href="#">Priority Cluster Access</a></li>
					<li><a href="#">For Businesses</a></li>
				</ul>
				<p class="button-style"><a href="#">More Details</a></p>
			</div>
			<div id="fbox2">
				<h2><span>Featured Project</span></h2>
				<p><img src="images/sintel-banner.png" width="640" alt="" /></p>
				<p>
					“Sintel” is an independently produced short film, initiated
					by the Blender Foundation as a means to further improve and
					validate the free/open source 3D creation suite Blender.
					With initial funding provided by 1000s of donations via the
					internet community, it has again proven to be a viable
					development model for both open 3D technology as for
					independent animation film.
					This 15 minute film has been realized in the studio of the
					Amsterdam Blender Institute, by an international team of
					artists and developers. In addition to that, several crucial
					technical and creative targets have been realized online,
					by developers and artists and teams all over the world.
				</p>
				<p class="button-style"><a href="#">More Details</a></p>
			</div>
		</div>
	</div>
	<div id="page-wrapper">
		<div id="page">
			<div id="content">
				<div>
					<h2>Premium Access</h2>
					<p>
						Need your renders even faster?  With Premium Access your
						jobs are sent ahead in the line.  Not only are you
						helping to financially support ARC, you also cut your
						wait time.  With ARC, you <b>only pay for frames that you
						render.</b>
					</p>
					<p>
						With group options, volume discounts, pay as you go
						pricing, and numerous other features; ARC's difference
						is clear!
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
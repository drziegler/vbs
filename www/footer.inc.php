<!-- Footer -->
<footer class="w3-container w3-center w3-theme w3-large">  
  <div class=" w3-padding-32">
	<p>
	<b>Hope Lutheran Church</b><br>
	4131 Lehigh Drive,
	Cherryville, PA 18035<br>
	phone 610-767-7203,
	fax 610-767-7203<br>
	Email the Webmaster &nbsp;<a href="<?php print "$url_utilities/fmail/mailer_form.php?sendto=webmaster";?>"<i class="hl-plain-link fas fa-envelope"></i></a>
	</p>
	<p class="w3-small">Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
 </div>
</footer>

<!-- Site map -->
<?php require("$path_building_blocks/navigation_bottom.inc.php"); ?>

<!-- ELCA Banner -->
<div class="w3-container w3-padding w3-center">
        <a href="http://www.elca.org/"><img src="<?php echo "$url_images/4colorELCAsmashx.gif";?>"></a>
</div>

<!-- load scripts -->
<script language="javascript" type="text/javascript" src="<?php echo "$url_building_blocks/hope.js";?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo "$url_utilities/news/news.js";?>"></script>
</body>
</html>
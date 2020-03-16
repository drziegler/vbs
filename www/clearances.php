<?php
  require_once($_SERVER['DOCUMENT_ROOT'] . "/var.inc.php");
  include("$path_building_blocks/header.inc.php"); 
  include("../vbs/vbsUtils.inc");
?>
<!----------------------------------------------------------------------------->
<!-- Page top -->
<!----------------------------------------------------------------------------->
<style>
    td {border:3px solid black; border-collapse: collapse;}
</style>
<div class="w3-container w3-padding">
	<div class="w3-content">
		<h1 class="w3-text-theme">
			Volunteer Clearance Information
		</h1>
<div id="clearance" class="gridContainer">
	<p>If you are 18 or older or are attending the 'Mom and Me' class and ...</p>
	<table>
	<tr><td><ul><li>volunteered for Hope's within the past 5 years</li></ul></td>
	    <td><ul style="line-height:50%"><li>are a new volunteer</li></ul></td>
	</tr>
	<tr><td><ul><li>We have clearances on file for you but</li>
				<li>You must sign a new <?php echo date("Y") ?> <a href='#VolunteerDisclosureStatement'>Volunteer Discolsure Statement</a> and</li>
				<li>if you did not live in PA for the past 10 consecutive years, you need a <a href='#OtherClearances'>Federal Criminal History Check.</a></li>
	    <td><ul><li>You will need a <a href='#OtherClearances'>Child Abuse History Clearance</a> and</li>
	    		<li>You will need a <a href='#OtherClearances'>PA Criminal Record Check</a> and</li>
	    		<li>You must sign a new <?php echo date("Y") ?>  <a href='#VolunteerDisclosureStatement'>Volunteer Discolsure Statement</a> and</li>
	    		<li>if you did not lived in PA for the past 10 consecutive years, you need a <a href='#OtherClearances'>Federal Criminal History Check.</a></li></ul></td></ul></td>
	</tr>
	</table><br>
	<h2>FAQs</h2>
	<p class="hanging topmargin"><b>Q. What if I already have copies of my clearances from another event?</b></p>
	<p class="hanging">A. If you have clearances from another event and they are less than 5 years (60 months) old, place copies in the Clearance Coordinator's mailbox in the church hallway, 
	email an electronic copy to the <a href="mailto:clearances@hopecherryville.org">Clearance Coordinator</a> or mail a paper copy to the church, attention Clearance Coordinator.<p>
	<p class="hanging topmargin"><b>Q. How do I obtain a copy of my clearances?</b></p>
	<p class="hanging">A. If you do not have current copies of your clearances, you can follow the directions on the <a href="http://keepkidssafe.pa.gov/resources/clearances/index.htm" target="_new">Keep Kids Safe</a> website to obtain a copy.</p>
	<p class="hanging topmargin"><b>Q. What is the deadline to obtain my clearance(s)?</b></p>
	<p class="hanging">A. We recommend you apply for your clearances now but no later than 2 weeks prior to the start of VBS.</p>
	<p class="hanging topmargin"><b>Q. What if I have more questions?</b></p>
	<p class="hanging">A. Contact our Clearance Coordinator, Julie Sekol by email at <a href="mailto:clearances@hopecherryville.org">clearances@hopecherryville.org</a> or by phone at 610-984-3625.
	<p><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></p>	
	<a name='VolunteerDisclosureStatement'></a><h2>Volunteer Disclosure Statement</h2>
	<p>You can print a copy of the <a href="http://keepkidssafe.pa.gov/cs/groups/webcontent/documents/document/c_160267.pdf" target="_new">Volunteer Disclosure Statement</a> or obtain a paper copy from the church office.  
	Place your completed disclosure into the Clearance Coordinator mailbox in the hallway at church.</p><a href='#top'>Back to top</a>
	<p><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></p>  
	<a name='OtherClearances'></a><h2>Child Abuse History Clearance<br>PA Criminal Record Check<br>Federal Criminal History Check (FBI Fingerprinting)</h2></a>
	<p>Forms and instructions for the above clearances can be found on the <a href="http://keepkidssafe.pa.gov/resources/clearances/index.htm" target="_new">Keeps Kids Safe</a> website.</p>
	<p>Please note that the PA Child Abuse Clearance and the PA Criminal record Check are free for volunteers.  There is a fee for the FBI Fingerprints.  It can take weeks for you to receive the Child Abuse Clearance and FBT Fingerprint results, so
	 please do not delay in completing these as we must have them prior to the start of Vacation Bible School.</p><a href='#top'>Back to top</a>
    <p><br><br><br><br><br><br></p>
</div>
<?php require("$path_building_blocks/footer.inc.php");?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<link href="css/layout.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
require_once('Connections/vbsDB.php');
include('vbsUtils.inc');

$sql[0]['name']= "Student";
$sql[0]['sql'] = "select s.student_id, concat(s.first_name, ' ', s.last_name) as Student, s.class, f.family_name as 'Family', f.email, s.last_update as 'Registration Started'
from family as f inner join students as s on f.family_id=s.family_id where year(s.last_update)=2018 and registered='Y'";
$sql[1]['name']= "Staff";
$sql[1]['sql'] = "select s.staff_id, concat(s.first_name, ' ', s.last_name) as Student, 'Staff', f.family_name as 'Family', f.email, s.last_update as 'Registration Started'
from family as f inner join staff as s on f.family_id=s.family_id where year(s.last_update)=2018   and registered='Y'";

for ($s=0; $s<count($sql); $s++) {
	$result = mysqli_query($vbsDBi, $sql[$s]['sql']);
	
	if ($result-num_rows>0){
		$oRow = "<h1>Incomplete ". $sql[$s]['name'] . " Registrations</h1>";
		$oRow .= "<table>";	
		/* We have incomplete registrations */	
		while ($row = $result->fetch_array(MYSQLI_NUM)) {
			$oRow .= "<tr>";
			for ($i=0; $i < count(array_keys($row)); $i++) {
				$oRow .= "<td>".$row[$i]."</td>";
			}
			$oRow .= "</tr>";
		}
	$oRow .= "</table>";
	print $oRow;
	}
}
?>
</body>
</html>
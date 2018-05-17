// JavaScript Document

//google.charts.load('current', {'packages':(['corechart'],['Bar'])});
google.charts.load('current', {'packages':['bar']});
google.charts.setOnLoadCallback(drawCharts);
alert ('loading javascript');

function drawCharts() {
	alert ('calling Registration');
	drawRegistration();

//	alert ('calling churches');
//	drawChurches();
//	alert ('calling denominations');
//	drawDenominations();
//	alert ('calling classes');
//	drawClasses();
}

function drawDenominations() {

	var data = google.visualization.arrayToDataTable([
	  ['Denomination', 'Students'],
	  ['Lutheran', 154],
	  ['Wesleyan',  10],
	  ['Roman Catholic', 33],
	  ['Unknown', 26],
	  ['UCC',  10],
	  ['Ukrainian Orthodox', 1],
	  ['Non-denominational', 1]
	]);

	var options = {
	  title: 'Denominations'
	};

	var chartPie = new google.visualization.PieChart(document.getElementById('denominations'));
	chartPie.draw(data, options);

}

function drawChurches() {

	var data = google.visualization.arrayToDataTable([
		['Church', 'Students'],
		['Hope Lutheran', 109],
		['No Church', 27],
		['Bethany Wesleyan', 18],
		['St Nicholas RC', 14],
		['Assumption BVM-Northampton', 7],
		['St Paul\'s UCC-Indianland', 5],
		['St Peter\'s UCC-Seemsville', 4],
		['Sacred Heart-Bath', 4],
		['Holy Trinity-Egypt', 3],
		['Queenship of Mary', 3],
		['Jerusalem-Western Salisbury', 3],
		['Emmanuels Lutheran', 3],
		['Assumption BVM-Ukranian', 2],
		['St John Fisher-Catasauqua', 2],
		['Sts Peter and Paul', 2],
		['Union-Schnecksville', 2]
	]);

	var options = {
		title: 'Students by Church Membership',
		chartArea: {width: '50%'},
		hAxis: {
		  title: 'Total Students',
		  minValue: 0
		},
		vAxis: {
			title: 'Church'
		}
	};

	var chart = new google.visualization.BarChart(document.getElementById('churches'));
	chart.draw(data, options);
}

function drawClasses(){

	var data = google.visualization.arrayToDataTable([
		['Class', 'Students'],
		['First Grade', 31],
		['Second Grade', 30],
		['Third Grade', 28],
		['Pre-Kindergarten', 25],
		['Fifth Grade', 21],
		['Fourth Grade', 19],
		['Kindergarten', 16],
		['Nursery', 33],
		['Sixth Grade', 11],
		['Staff Nursery', 3]
	]);
	
	var options = {
	  title: 'Students by Class'
	};

	var chart = new google.visualization.PieChart(document.getElementById('students'));
	chart.draw(data, options);
	
}

function drawRegistration(){

	var data = google.visualization.arrayToDataTable([
		['Weeks','Registrations'],
		['+9', 32],
		['+8', 62],
		['+7', 25]
	]);
	
	
	var options = {
		chart: {
			title: 'Advance Registrations',
			subtitle: 'Weeks before VBS start',
		}
	}
	var temp = new google.
	var chart = new google.charts.bar(document.getElementById('register'));
	chart.draw(data, google.charts.bar.convertOptions(options));
}
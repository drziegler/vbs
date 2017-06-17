// JavaScript Document

google.charts.load('current', {'packages': ['corechart' , 'bar']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
	drawClasses();
	drawRegistration();
	drawChurches();
	drawDenominations();
	drawMedia();
	drawReturns();
	drawBrowser();
	drawLocale();
}

function drawDenominations() {

	var data = google.visualization.arrayToDataTable([
	  ['Denomination', 'Students'],
	  ['Lutheran', 154],
	  ['Roman Catholic', 33],
	  ['Un-Churched', 26],
	  ['Wesleyan',  10],
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
	  title: 'Students by Class',
//	  pieSliceText: 'value'
	};

	var chart = new google.visualization.PieChart(document.getElementById('students'));
	chart.draw(data, options);
	
}

function drawMedia(){

	var data = google.visualization.arrayToDataTable([
		['Media', 'Count'],
		['Desktop',  138],
		['Mobile',    64],
		['Tablet',    16],
		['Paper',     16]
	]);
	
	var options = {
	  title: 'Student Registration Percentage by Media Type'
	};

	var chart = new google.visualization.PieChart(document.getElementById('media'));
	chart.draw(data, options);
	
}

function drawReturns(){

	var data = google.visualization.arrayToDataTable([
		['Media', 'Count'],
		['Returning', 178],
		['New', 59]
	]);
	
	var options = {
	  title: 'Returning Students'
	};

	var chart = new google.visualization.PieChart(document.getElementById('returns'));
	chart.draw(data, options);
	
}

function drawBrowser(){

	var data = google.visualization.arrayToDataTable([
		['Browser', 'Count'],
		['Chrome', 117],
		['Safari', 59],
		['Internet Explorer', 31],
		['Firefox', 10],
		['Android', 9],
		['Edge', 8]
	]);
	
	var options = {
	  title: 'Registrations by Browser Type'
	};

	var chart = new google.visualization.PieChart(document.getElementById('browser'));
	chart.draw(data, options);
	
}

function drawChurches() {

	var data = google.visualization.arrayToDataTable([
		['Church', 'Students', 'Volunetters'],
		['Hope Lutheran', 109, 73],
		['No Church', 27, 4],
		['Bethany Wesleyan', 18, 1],
		['St Nicholas RC', 14, 1],
		['Assumption BVM-Northampton', 7, 0],
		['St Paul\'s UCC-Indianland', 5, 0],
		['St Peter\'s UCC-Seemsville', 4, 0],
		['Sacred Heart-Bath', 4, 0],
		['Holy Trinity-Egypt', 3, 0],
		['Queenship of Mary', 3, 6],
		['Jerusalem-Western Salisbury', 3, 0],
		['Emmanuels Lutheran', 3, 1],
		['Assumption BVM-Ukranian', 2, 0],
		['St John Fisher-Catasauqua', 2, 0],
		['Sts Peter and Paul', 2, 0],
		['Union-Schnecksville', 2, 0]
	]);

	var options = {
		title: 'Participation by Home Church',
		chartArea: {width: '80%'},
		bars: 'horizontal',
		bar: {groupWidth: '80%'},
		hAxis: {
		  title: 'Total Students & Staff',
		  minValue: 0
		},
		vAxis: {
			title: 'Home Church'
		}
	};

	var chart = new google.charts.Bar(document.getElementById('churches'));
	chart.draw(data, google.charts.Bar.convertOptions(options));
}

function drawLocale(){

	var data = google.visualization.arrayToDataTable([
		['Locale', 'Students', 'Volunteers'],
		['Walnutport, PA', 101, 24],
		['Northampton, PA', 37, 21],
		['Danielsville, PA', 30, 5],
		['Bath, PA', 11, 4],
		['Slatington, PA', 7, 8],
		['Coplay, PA', 4, 4],
		['Palmer Twsp, PA', 4, 1],		
		['Palmerton, PA', 4, 4],
		['Bethlehem, PA', 3, 3],
		['Catasauqua, PA', 2, 0],
		['Easton, PA', 2, 0],
		['Gilbertsville, PA', 2, 0],
		['Kutztown, PA', 2, 0],
		['Laurys Station, PA', 2, 6],
		['Lehighton, PA', 2, 0],
		['Mertztown, PA', 0, 1],
		['Schnecksville, PA', 2, 3],
		['Whitehall, PA', 1, 1]
	]);
	
	var options = {
		title: 'Participants by Locale',
		chartArea: {width: '80%'},
		bars: 'horizontal',
		bar: {groupWidth: '80%'},
		hAxis: {
		  title: 'Number',
		  minValue: 1
		},
		vAxis: {
			title: 'City, State'
		}
	};

	var chart = new google.charts.Bar(document.getElementById('locale'));
	chart.draw(data, google.charts.Bar.convertOptions(options));
	
}



function drawRegistration(){

	var data = google.visualization.arrayToDataTable([
		['Weeks','Students', 'Staff'],
		['+9', 32, 7],
		['+8', 62, 18],
		['+7', 25, 10],
		['+6', 22, 11],
		['+5', 28,  5],
		['+4', 18, 10],
		['+3', 17, 15],
		['+2', 11,  7],
		['+1',  2,  3]
	]);
	
	
	var options = {
		chart: {
			title: 'Advance Registrations',
			subtitle: 'New registration count in weeks before VBS starts',
		}
	}

	var chart = new google.charts.Bar(document.getElementById('register'));
	chart.draw(data, google.charts.Bar.convertOptions(options));
}

/* NOTES:
  Failed to load resource - check case of options in google.charts.load, e.g. 'Bar' get 404 response, 'bar' does not.
*/
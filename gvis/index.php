<?php require('Gvis.php');
/* options available
AnnotatedTimeLine
ImageAreaChart
AreaChart
ImageBarChart
ColumnChart
Guage
GeoMap
IntensityMap
ImageLineChart
LineChart
Map
MotionChart
PieChart
ScatterChart
Table
*/
	$g = new Gvis();
    $g->set_visualization('AreaChart');
	$g->set_heading('date', 'Sent Date');
	$g->set_heading('number', 'Surveys Sent');

	$g->set_height(300);
	$g->set_width(400);

	$g->add_row('1/20/2010', 10);
	$g->add_row('1/21/2010', 15);
	$g->add_row('1/22/2010', 25);
	$g->add_row('1/23/2010', 30);
	$g->add_row('1/24/2010', 21);
	$g->add_row('1/25/2010', 27);
	$g->add_row('1/26/2010', 79);

	$g->set_colors("#86ACC1");

	$googleVisualization = $g->generate();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Test google chart</title>
   <script type='text/javascript' src='http://www.google.com/jsapi'></script>
</head>

<body>
      <?php echo $googleVisualization;?>
</body>

</html>


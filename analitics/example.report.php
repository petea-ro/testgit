<?php
define('ga_email','petea2008@gmail.com');
define('ga_password','');
define('ga_profile_id','48798968');

require 'gapi.class.php';

$ga = new gapi(ga_email,ga_password);


$ga->requestReportData(ga_profile_id,array('country'),array('visits', 'pageviews', 'newvisits'), array('-visits'));
?>
 <?php //var_dump($ga->getResults())?>

<table>
<tr>
  <th>Date</th>
  <th>Pageviews</th>
  <th>Visits</th>
  <th>NewVisits</th>
</tr>
<?php
foreach($ga->getResults() as $result): ?>
<?php $m = $result->getMetrics();?>
<tr>
  <td><?php echo $result ?></td>
  <td><?php echo $result->getPageviews() ?></td>
  <td><?php echo $result->getVisits() ?></td>
  <td><?php echo $result->getNewVisits() ?></td>
</tr>
<?php
endforeach
?>
</table>


<table>
<tr>
  <th>Total Results</th>
  <td><?php echo $ga->getTotalResults() ?></td>
</tr>
<tr>
  <th>Total Pageviews</th>
  <td><?php echo $ga->getPageviews() ?></td>
</tr>
<tr>
  <th>Total Visits</th>
  <td><?php echo $ga->getVisits() ?></td>
</tr>
<tr>
  <th>Results Updated</th>
  <td><?php echo $ga->getUpdated() ?></td>
</tr>
<tr>
	<th>Metrics</th>
	<td><?php ?></td>
</tr>
</table>
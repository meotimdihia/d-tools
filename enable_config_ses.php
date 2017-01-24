<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<?php
require 'vendor/autoload.php';

$sdk = new Aws\Sdk([
    'region'   => 'ap-southeast-1',
    'version'  => 'latest',
    'Ses' => [
        'region' => 'us-east-1'
    ]
]);

$SesClient = $sdk->createSes();
$identities = $SesClient->listIdentities(array(	'IdentityType' => 'Domain'));

$notifications = $SesClient->getIdentityNotificationAttributes(array(
    'Identities' => $identities->get('Identities')
))->get('NotificationAttributes');
$dkims = $SesClient->getIdentityDkimAttributes(array(
    'Identities' => $identities->get('Identities')
))->get('DkimAttributes');
echo '<pre>';
echo '</pre>';



if (!empty($_GET['domain'])) {
	echo $_GET['domain'];

	$SesClient->setIdentityNotificationTopic([
   	 	'Identity' => $_GET['domain'], // REQUIRED
   	 	'NotificationType' => 'Complaint', // REQUIRED
    	'SnsTopic' => 'arn:aws:sns:us-east-1:371397073484:new-complaint-topic',
	]);

	$SesClient->setIdentityNotificationTopic([
	   	 	'Identity' => $_GET['domain'], // REQUIRED
	   	 	'NotificationType' => 'Bounce', // REQUIRED
	    	'SnsTopic' => 'arn:aws:sns:us-east-1:371397073484:new-bounces-topic',
	]);
	 $SesClient->setIdentityNotificationTopic([
	   	 	'Identity' => $_GET['domain'], // REQUIRED
	   	 	'NotificationType' => 'Delivery', // REQUIRED
	    	'SnsTopic' => 'arn:aws:sns:us-east-1:371397073484:email-delivery-topic',
	]);
	$SesClient->SetIdentityHeadersInNotificationsEnabled(array(
		    'Enabled' => true , // REQUIRED
    	'Identity' =>  $_GET['domain'], // REQUIRED
    	'NotificationType' => 'Complaint', // REQUIRED
	));
		$SesClient->SetIdentityHeadersInNotificationsEnabled(array(
		    'Enabled' => true , // REQUIRED
    	'Identity' =>  $_GET['domain'], // REQUIRED
    	'NotificationType' => 'Bounce', // REQUIRED
	));
	$SesClient->SetIdentityHeadersInNotificationsEnabled(array(
		    'Enabled' => true , // REQUIRED
    	'Identity' =>  $_GET['domain'], // REQUIRED
    	'NotificationType' => 'Delivery', // REQUIRED
	));
}
?>
<style type='text/css'>
.glyphicon-ok {
	color:green;
}
.glyphicon-remove {
	color: red;
}
</style>
<div class='row'>
<div class="col-md-8">
<table class='table table-bordered table-condensed'>
<thead>
<th>Index</th>
<th>Domain</th>
<th>Bounce</th>
<th>Complaint</th>
<th>Delivery</th>
<th>Forwarding vie Email</th>
<th>HeadersInBounce</th>
<th>HeadersInComplaint</th>
<th>HeadersInDelivery</th>
<th>Active</th>
</thead>
<tbody>
	<?php
	$i = 0;
	foreach ($notifications as $key => $value) {
	?>

		<tr>
		<td><?php $i++; echo $i;?></td>
			<td><?php echo $key ?>	</td>
			<?php 
			echo "<td>";
			if (!empty($value['BounceTopic'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";
			
			echo "<td>";
			if (!empty($value['ComplaintTopic'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";
			echo "<td>";
			if (!empty($value['DeliveryTopic'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";
			echo "<td>";
			if (!empty($value['ForwardingEnabled'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";
			echo "<td>";
			if (!empty($value['HeadersInBounceNotificationsEnabled'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";
			echo "<td>";
			if (!empty($value['HeadersInComplaintNotificationsEnabled'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";
			echo "<td>";
			if (!empty($value['HeadersInDeliveryNotificationsEnabled'])) {
				echo '<span class="glyphicon glyphicon-ok" aria-hidden="true">';
			} else {
				echo '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
			}
			echo "</td>";


			echo "<td><a href='?domain=" . $key . "' class='btn  btn-success' > Active</a>"
			?>
		</tr>
	<?php
		}
	?>
	</tbody>
</table>
</div>
</div>
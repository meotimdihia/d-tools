<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js"></script>

<script src="node_modules/chart.js/dist/Chart.min.js"></script>
<script src="node_modules/chartjs-plugin-annotation/chartjs-plugin-annotation.js"></script>
<h1>List Metrics</h1>

<?php
require 'vendor/autoload.php';

$sdk = new Aws\Sdk([
    'region'   => 'ap-southeast-1',
    'version'  => 'latest'
]);
$client = $sdk->createCloudWatch();
$ec2 = $sdk->createEc2();
$result = $ec2->describeInstances([
	 'Filters' => [
        [
            'Name' => 'tag:proj',
            'Values' => ['plf']
        ]
    ]
]);
$result = $result->toArray();
$ec2 = array();
foreach ($result['Reservations'] as $key => $value) {
	foreach ($value['Instances'][0]['Tags'] as $k => $v) {
		if ($v['Key'] == 'Name') {
			$name = $v['Value'];
		}
	};
	$ec2[] = [
	    'Dimensions' => [
	        [
	            'Value' => $value['Instances'][0]['InstanceId'],
	            'Name' => 'InstanceId'
	        ]
	    ],
	    'EndTime' => time(),
	    'MetricName' => 'CPUUtilization',
	    'Namespace' => 'AWS/EC2',
	    'Statistics' => ['Average'],
	    'Period' => 300, // multiple of 60
	    'StartTime' => strtotime('-6 hours'),
	    'title' => $name
	];
}
// $list = $client->ListMetrics([
// 	'Dimensions' => [
//         [
//             'Name' => 'InstanceId', // REQUIRED
//         ],
//     ],
//     'MetricName' => 'CPUUtilization'
// ]);

$data = [
	[
	    'Dimensions' => [
	        [
	            'Name' => 'DBInstanceIdentifier',
	            'Value' => 'platform'
	        ]
	    ],
	    'EndTime' => time(),
	    'MetricName' => 'CPUUtilization',
	    'Namespace' => 'AWS/RDS',
	    'Statistics' => ['Average'],
	    'Period' => 300, // multiple of 60
	    'StartTime' => strtotime('-6 hours'),
	    'title' => 'DB platform'
	],
	[
	    'Dimensions' => [
	        [
	            'Name' => 'DBInstanceIdentifier',
	            'Value' => 'platform2'
	        ]
	    ],
	    'EndTime' => time(),
	    'MetricName' => 'CPUUtilization',
	    'Namespace' => 'AWS/RDS',
	    'Statistics' => ['Average'],
	    'Period' => 300, // multiple of 60
	    'StartTime' => strtotime('-6 hours'),
	    'title' => 'DB platform 2'
	],
	[
	    'Dimensions' => [
	        [
	            'Name' => 'DBInstanceIdentifier',
	            'Value' => 'platform-rep1'
	        ]
	    ],
	    'EndTime' => time(),
	    'MetricName' => 'CPUUtilization',
	    'Namespace' => 'AWS/RDS',
	    'Statistics' => ['Average'],
	    'Period' => 300, // multiple of 60
	    'StartTime' => strtotime('-6 hours'),
	    'title' => 'DB platform replication'
	]	
];
$data = $data + $ec2;

foreach ($data as $key => $value) {
	if ($key == 0 || $key % 3 == 0) {
		echo '<div class="row">';
	}
	$result = $client->GetMetricStatistics ($value);
	$title = $value['title'];
	$result = $result->toArray();

	$chartData = $chartLabels = array();
	foreach ($result['Datapoints'] as $k => $v) {
		$time = new DateTime("@" . $v['Timestamp']->getTimestamp(), new DateTimeZone('UTC'));
		$time->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
		$chartData[$time->format('Y-m-d H:i:s')] = round($v['Average']);
	}
	ksort($chartData, SORT_NATURAL);
?>


<div class="col-md-3"><canvas id="chart<?php echo $key ?>" width="400" height="300"></canvas>	</div>
<script>
var ctx<?php echo $key ?> = document.getElementById("chart<?php echo $key ?>");
var myChart<?php echo $key ?> = new Chart(ctx<?php echo $key ?>, {
    type: 'line',
    data: {
    	labels: <?php echo json_encode(array_keys($chartData)) ?>,
	    datasets: [
	        {
	            label: "CPU",
                borderColor: "#444",
                fill:false,
	            data: <?php echo json_encode(array_values($chartData)) ?>,
	            pointRadius: 0,
	        }
	    ]
	},

   	options: {
   		responsive: true,
   		title:{
			display: true,
			text: '<?php echo $title ?>',
			fontColor: "#222"
		},
        tooltips: {
            mode: 'index',
            intersect: false,
        },

      	scales: {
            xAxes: [{
                type: 'time'
            }]
        },
    annotation: {
        annotations: [{
            id: 'a-line-1', // optional
            type: 'line',
            mode: 'horizontal',
            scaleID: 'y-axis-0',
            value: '40',
            borderColor: 'red',
            borderWidth: 2,
            label: {
    	        position: "left",
        		xAdjust: 0,
        		backgroundColor: 'rgba(0,0,0,0)',
        		fontColor: "red",
        		yAdjust: 10,
            	enabled: true,
            	content: "Warning"
            }
        }],
        drawTime: 'afterDraw', 
        events: ['click'],
        dblClickSpeed: 350 // ms (default)
    }
    }
});
</script>

<?php
	if ($key % 3 == 2 || $key == count($data)) {
		echo '</div>';
	}
}
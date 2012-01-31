<?php

include './get_twitter_details.php';
require_once("../dataStore/query.php");

$date = date('c');
echo "DATE: '.$date \n";
$printDate = date('j M',time() - 86400); //yesterday's date

$data = request(array('action'=>"getTotalConsumptionLast2Days"));;
$json = json_encode($data);
$json_out = json_decode($json);

$obj = $json_out->data;
//var_dump($obj);

//$today = $obj->today;
//var_dump($today);

//$yesterday = $obj->yesterday;
//var_dump($yesterday);

$test = $obj->yesterday;
$av = $test->average;
$count = $test->count;
$test = $obj->yesteryesterday; 
$averageY = $test->average;
$i=0;

foreach ($obj->yesterday as $yesterday) {
       //echo"{$today->hubId}\n";
	
	$hubId = $yesterday->hubId;
	$name = $yesterday->description;
	$usage = $yesterday->total;

	//look up twitter details for each hub from get_twitter_details.php
	$consumer_key = getConsumerKey($hubId);
	$consumer_secret = getConsumerSecret($hubId);
	$oauth_token = getOauthToken($hubId);
	$oauth_token_secret = getOauthTokenSecret($hubId);
	
	$hubIdY;
	$usageY;
	
	foreach ($obj->yesteryesterday as $yesteryesterday) {
     	   //echo"{$yesterday->hubId}\n";
	
		$h = $yesteryesterday->hubId;
		$u = $yesteryesterday->total;

		if ($h==$hubId) {
			$hubIdY = $h;
			$usageY = $u;
			break;
		}
	}
	
	echo "hubId: '.$hubId.' , name: '.$name.', usageToday: '.$usage.', averageToday: '.$av.', hubIdY: '.$hubIdY.', usageYesterday: '.$usageY.', averageYesterday: '.$averageY \n";  

	if ($hubId == 64) {
		$text = getCompareToNeighbourText($name,$usage,$av);
		echo "++++TWEET++++: '.$text\n";
		$text2 = getCompareToYesterdayText($name,$usage,$usageY);				echo "TWEET++++: '.$text \n";

		
		//make sure we have a key for this hub
		if (strcmp($consumer_key,'')!=0) {
			$result = post_tweet($text,$consumer_key,$consumer_secret,
$oauth_token,$oauth_token_secret);
			print "Response code: " . $result . "\n";
		$result = post_tweet($text2,$consumer_key,$consumer_secret,
$oauth_token,$oauth_token_secret);
			print "Response code: " . $result . "\n";
		
			if ($i<3) {
				//this is one of the 3 that used most electricity
				$text='Yesterday ('.$printDate.'), you were among the 3 neighbours that used the most electricity.';
				echo "+++++HIGH USER TWEET++++: '.$text \n";

				$result = post_tweet($text,$consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret);
				print "Response code: " . $result . "\n";
			}

			if ($i>($count-4)) {
				//this is one the 3 that used least electricity
				$text='Yesterday ('.$printDate.'), you were among the 3 neighbours that used the least electricity. Well done.';
				echo "+++++LOW USER TWEET++++: '.$text \n";

				$result = post_tweet($text,$consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret);
				print "Response code: " . $result . "\n";
			}

		}
		
	}

	$i++;
}

function getCompareToNeighbourText($name,$usage,$av) {
	
	$text='';

	$diff = 100-($usage/$av*100);
	if ($diff<0) {
		$diff = $diff-(2*$diff);
	}	

	if ($usage<$av) {
		$text = 'Yesterday, you have used '.round($usage,3). ' kwh of electricity. You used '.round($diff).'% less than your neighbours.';
	}
	if ($usage>$av) {
		$text =  'Yesterday, you have used '.round($usage,3). ' kwh of electricity. You used '.round($diff).'% more than your neighbours.';
	}
	return $text;
}

function getCompareToYesterdayText($name,$usage,$usageY) {
	
	$text='';
	
	$diffToY=100-($usage/$usageY*100);
	if ($diffToY<0) {
		$diffToY = $diffToY-(2*$diffToY);
	}

	if ($usage<$usageY) {
		$text = 'Yesterday you used '.round($diffToY).'% less than the day before. '.getCongrats();  
	}
	if ($usage>$usageY) {
		$text = 'Yesterday you used '.round($diffToY).'% more than the day before. '.getTip();
	}
	return $text;
}


function getCongrats() {
	$min = 0;
	$max = 5;
	$text ='';

	$rand_num =  rand($min, $max);

	switch ($rand_num) {
		case 0:
			$text = 'Well done!';
			break;
		case 1:
			$text = 'Congratulations.';
			break;
		case 2: 
			$text = 'Keep it up!';
			break;
		case 3: 
			$text = 'Keep up the good work.';
			break;
		case 4: 
			$text = 'You\'re great.';
			break;
		case 5:
			$text = 'Good job.';
			break;
	}
	return $text;
}	


function getTip() {
	$min = 0;
	$max = 11;
	$text ='';

	$rand_num = rand($min, $max);

	switch ($rand_num) {
		case 0:
			$text = 'If every person in the UK showered 1min less they could save almost £1 million per day.';
			break;
		case 1:
			$text = 'A 10-minute shower can use as much electricity as boiling 30 kettles.';
			break;
		case 2: 
			$text = 'Washing dishes by hand rather than in the dish washer may save you up to £1 per week.';
			break;
		case 3: 
			$text = 'Consider turning down the temperature on the washing machine.';
			break;
		case 4: 
			$text = 'Using a shorter programme on the dish washer may save you £9 a year.';
			break;
		case 5:
			$text = 'A switched on light (60W) may cost you 1p per hour.';
			break;
		case 6: 
			$text = 'Having a 60W light bulb switched on for an hour costs as much as boiling three kettles.';
			break;
		case 7: 
			$text = 'Use energy efficient light bulbs which use less energy and last up to ten times longer.';
			break;
		case 8:
			$text = 'Turn off appliances when not in use as they continue to use energy when left on standby.';
			break;
		case 9: 
			$text = 'Drying your clothes outside rather than using a tumble dryer could save you 50p per load.';
			break;
		case 10: 
			$text = 'The kettle uses a lot, only boil as much water as you need.';
			break;
		case 11:
			$text = 'Insulate your home to save energy and reduce your bills.';
			break;

	}
	return $text;
}	
	

function post_tweet($tweet_text,$consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret) {

  // Use Matt Harris' OAuth library to make the connection
  // This lives at: https://github.com/themattharris/tmhOAuth
  require_once('./tmhoauth.php');
      
  // Set the authorization values
  // In keeping with the OAuth tradition of maximum confusion, 
  // the names of some of these values are different from the Twitter Dev interface
  // user_token is called Access Token on the Dev site
  // user_secret is called Access Token Secret on the Dev site
  // The values here have asterisks to hide the true contents 
  // You need to use the actual values from your Twitter app
  $connection = new tmhOAuth(array(
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret,
    'user_token' => $oauth_token,
    'user_secret' => $oauth_token_secret
  )); 
  
  // Make the API cal
// $connection->request('GET',
// $connection->url('1/account/rate_limit_status'));
//return $connection->response['response'];  

  $connection->request('POST', 
    $connection->url('1/statuses/update'), 
    array('status' => $tweet_text));
  
   return $connection->response['code'];
}

?>


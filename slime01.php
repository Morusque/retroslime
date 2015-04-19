<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('TwitterAPIExchange.php');

/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' => "267144004-tDYmqReWGUZW1ku5ZcnnLAOHqVvN0FVok7HnHgt5",
    'oauth_access_token_secret' => "TckhPt75mH6tiIryK2S0kZ8ZerNj8o9R2LToRwVdOfE",
    'consumer_key' => "iWVdmdAHU4Yc9MnC8vUYRw",
    'consumer_secret' => "etWwJppjQSsGKCslWi0QPZXSn0Mxjw2HKyNPf5Xz2g"
);

$twitterId = 267144004;

$twitter = new TwitterAPIExchange($settings);

$allowTwitterPosts = true;
$saveXmlFile = true;
$followAllowed = true;

$logFile = "";

// load the xml to check if it is valid
$backupPath = 'backups/';
$baseXml = 'retroslime.xml';
$doc = new DOMDocument('1.0', 'utf-8');
$doc->Load($baseXml);

// check if the xml is valid
if ($doc->documentElement->childNodes->length==0) {
	$backupFiles = getDirectoryList($backupPath);
	$bestI=0;
	$recent=0;
	// look for the most recent valid backup file
	for ($i=0;$i<count($backupFiles);$i++) {
		if (substr($backupFiles[$i],-3)=="xml") {
			$thisOld = filemtime($backupPath . $backupFiles[$i]);
			if ($thisOld>$recent) {
				$docBackup = new DOMDocument('1.0', 'utf-8');
				$docBackup->Load($backupPath . $backupFiles[$i]);
				if ($docBackup->documentElement->childNodes->length>0) {
					$recent=$thisOld;
					$bestI=$i;
				}
			}
		}
	}
	// replace it
	$doc->Load($backupPath . $backupFiles[$bestI]);
	$doc->formatOutput = true;
	$doc->preserveWhiteSpace = false;
	$doc->saveXML();
	$doc->save($baseXml);
}

// loads the xml file
$xmlFileUrl = "retroslime.xml";
$doc = new DOMDocument('1.0', 'utf-8');
$doc->formatOutput = true;
$doc->preserveWhiteSpace = false;
$doc->load($xmlFileUrl);
$docElements = $doc->documentElement;
logInfos('Xml file opened <br/>',$logFile);

// extracts lists
$generalInfos = $docElements->getElementsByTagName('GeneralInfos')->item(0);
$trip = $docElements->getElementsByTagName('Trip')->item(0);
$urlToVisit = $docElements->getElementsByTagName('UrlToVisit')->item(0);
$bannedUrl = $docElements->getElementsByTagName('BannedUrl')->item(0);
$wipSentences = $docElements->getElementsByTagName('WipSentences')->item(0);
$bigSentences = $docElements->getElementsByTagName('BigSentences')->item(0);
$readyTweets = $docElements->getElementsByTagName('ReadyTweets')->item(0);
$doneTweets = $docElements->getElementsByTagName('DoneTweets')->item(0);
$emailAddresses = $docElements->getElementsByTagName('EmailAddresses')->item(0);
$hashtags = $docElements->getElementsByTagName('Hashtags')->item(0);

// log infos
logInfos('Current Xml state : <br/>',$logFile);
logInfos('<br/>',$logFile);

logInfos('General infos : <br/>',$logFile);
logInfos('Last save was : ' . $generalInfos->getElementsByTagName('lastSave')->item(0)->getAttribute('date') . '</br>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Trip : <br/>',$logFile);
logInfos('Trip latitude : ' . $trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('latitude') . '</br>',$logFile);
logInfos('Trip longitude : ' . $trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('longitude') . '</br>',$logFile);
logInfos('Trip travelling angle : ' . $trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('travelAngle') . '</br>',$logFile);
logInfos('Trip travelling speed : ' . $trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('travelSpeed') . '</br>',$logFile);
for ($i=0;$i<$trip->getElementsByTagName('LocationLog')->length;$i++) {
	$theElement = $trip->getElementsByTagName('LocationLog')->item($i);
	logInfos('Location log : ' . $theElement->getAttribute('date') . ' : ',$logFile);
	logInfos($theElement->getAttribute('name') . ' : ',$logFile);
	logInfos($theElement->getAttribute('latitude') . ' : ',$logFile);
	logInfos($theElement->getAttribute('longitude'),$logFile);
	logInfos('<br/>',$logFile);
}
logInfos('<br/>',$logFile);

logInfos('URL to visit : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>address</td><td>adding date</td></tr>',$logFile);
for ($i=0;$i<$urlToVisit->getElementsByTagName('Url')->length;$i++) {
	$theElement = $urlToVisit->getElementsByTagName('Url')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('address') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('addDate') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Banned URL : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>address</td><td>adding date</td><td>visit date</td><td>result</td></tr>',$logFile);
for ($i=0;$i<$bannedUrl->getElementsByTagName('Url')->length;$i++) {
	$theElement = $bannedUrl->getElementsByTagName('Url')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('address') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('addDate') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('visitDate') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('result') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('WIP sentences : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>text</td><td>starting date</td><td>unsuccessful tries</td></tr>',$logFile);
for ($i=0;$i<$wipSentences->getElementsByTagName('Sentence')->length;$i++) {
	$theElement = $wipSentences->getElementsByTagName('Sentence')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('text') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('startDate') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('tries') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Big sentences : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>text</td><td>starting date</td><td>finishing date</td></tr>',$logFile);
for ($i=0;$i<$bigSentences->getElementsByTagName('Sentence')->length;$i++) {
	$theElement = $bigSentences->getElementsByTagName('Sentence')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('text') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('startDate') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('finishedDate') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Ready tweets : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>text</td><td>starting date</td><td>finishing date</td></tr>',$logFile);
for ($i=0;$i<$readyTweets->getElementsByTagName('Tweet')->length;$i++) {
	$theElement = $readyTweets->getElementsByTagName('Tweet')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('text') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('startDate') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('finishedDate') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Done tweets : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>text</td><td>used date</td><td>in reply to</td></tr>',$logFile);
for ($i=0;$i<$doneTweets->getElementsByTagName('Tweet')->length;$i++) {
	$theElement = $doneTweets->getElementsByTagName('Tweet')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('text') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('usedDate') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('inReplyTo') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Email addresses : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>address</td><td>found on</td></tr>',$logFile);
for ($i=0;$i<$emailAddresses->getElementsByTagName('Email')->length;$i++) {
	$theElement = $emailAddresses->getElementsByTagName('Email')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('address') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('foundOn') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

logInfos('Hashtags : <br/>',$logFile);
logInfos('<table border=1>',$logFile);
logInfos('<tr><td>hashtag</td><td>user screen name</td><td>tweet text</td><td>date</td></tr>',$logFile);
for ($i=0;$i<$hashtags->getElementsByTagName('Hashtag')->length;$i++) {
	$theElement = $hashtags->getElementsByTagName('Hashtag')->item($i);
	logInfos('<tr>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('tag') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('screenName') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('text') . '</td>',$logFile);
	logInfos('<td>' . $theElement->getAttribute('date') . '</td>',$logFile);
	logInfos('</tr>',$logFile);
}
logInfos('</table>',$logFile);
logInfos('<br/>',$logFile);

// move geographical position
$newTravelAngle = floatval($trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('travelAngle'));
$newTravelSpeed = floatval($trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('travelSpeed'));
$newLatitude = floatval($trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('latitude'));
$newLongitude = floatval($trip->getElementsByTagName('CurrentTrip')->item(0)->getAttribute('longitude'));
$newTravelAngle = fmod($newTravelAngle + (rand(-10.0 , 10.0) * pi() / 100.0) + (pi()*2) , (pi()*2));
if ($newLatitude < -80) $newTravelAngle = 0;
if ($newLatitude >  80) $newTravelAngle = pi();
if (rand(0,100)<5) $newTravelSpeed = constrain(($newTravelSpeed + rand(-100.0,100.0)/300.0), 0, 1);
$newLatitude = constrain($newLatitude + cos($newTravelAngle) * $newTravelSpeed, -85.0, +85.0);
$newLongitude = fmod(($newLongitude + sin($newTravelAngle) * $newTravelSpeed + 180.0 + 360.0), 360.0) - 180.0;
// put back in the xml objects
$trip->getElementsByTagName('CurrentTrip')->item(0)->setAttribute('travelAngle',$newTravelAngle);
$trip->getElementsByTagName('CurrentTrip')->item(0)->setAttribute('travelSpeed',$newTravelSpeed);
$trip->getElementsByTagName('CurrentTrip')->item(0)->setAttribute('latitude',$newLatitude);
$trip->getElementsByTagName('CurrentTrip')->item(0)->setAttribute('longitude',$newLongitude);
// look for a place name
$ch = curl_init();
$queryUrl = 'http://ws.geonames.org/findNearbyPlaceNameJSON?lat=' . floatval($newLatitude) . '&lng=' . floatval($newLongitude) . '&radius=100';
curl_setopt($ch, CURLOPT_URL, $queryUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$outputs = json_decode(curl_exec($ch));
curl_close($ch);
$output = $outputs->geonames;
$currentLocationName="";
for ($i=0 ; $i<count($output) && $currentLocationName=="" ; $i++) 
{
	if (strlen($output[$i]->adminName1)>0) {
		$currentLocationName = $output[$i]->adminName1;
	}
}
logInfos('New trip movement : ' . $newTravelAngle . ' / ' . $newTravelSpeed . '<br/>',$logFile);
logInfos('New location : ' . $newLatitude . ' / ' . $newLongitude . ' / ' . $currentLocationName . '<br/>',$logFile);
logInfos('<br/>',$logFile);

// send update to twitter for the location name
if (strlen($currentLocationName)>0 && $allowTwitterPosts) {
	$postfields = array('location' => $currentLocationName);
	$twitter = new TwitterAPIExchange($settings);
	$outputs = json_decode($twitter->buildOauth('https://api.twitter.com/1.1/account/update_profile', 'POST')->setPostfields($postfields)->performRequest());
}

// write a location log for the current date
$theLocationLog = $doc->createElement("LocationLog");
$theLocationLog->setAttribute("latitude", $newLatitude);
$theLocationLog->setAttribute("longitude", $newLongitude);
if (strlen($currentLocationName)>0) $theLocationLog->setAttribute("name", $currentLocationName);
$theLocationLog->setAttribute("date", date('c'));
$trip->appendChild($theLocationLog);

// follow people
$twitter = new TwitterAPIExchange($settings);
$getField = '?user_id=' . $twitterId;
$recentFollowers = json_decode($twitter->setGetfield($getField)->buildOauth('https://api.twitter.com/1.1/followers/ids.json', 'GET')->performRequest());
foreach ($recentFollowers->{"ids"} as $user) {
	$twitter = new TwitterAPIExchange($settings);
	$getField = '?source_id=' . $user . '&target_id=' . $twitterId;
	$friendship = json_decode($twitter->setGetfield($getField)->buildOauth('https://api.twitter.com/1.1/friendships/show.json', 'GET')->performRequest());
	if (isset($friendship->{"relationship"}->{"target"}->{"following"})) {
		if (!$friendship->{"relationship"}->{"target"}->{"following"}) {
			echo "following " . $friendship->{"relationship"}->{"target"}->{"following"} . "<br/>";
			if ($followAllowed) {
				$twitter = new TwitterAPIExchange($settings);
				$postfields = array('user_id' => $user);
				echo $twitter->buildOauth('https://api.twitter.com/1.1/friendships/create.json', 'POST')->setPostfields($postfields)->performRequest();
				logInfos('now folloging '.$user.'<br/>',$logFile);
			}
		}
	} else {
		logInfos('unable to access relationship with '.$user.'<br/>',$logFile);
		break;
	}
}
logInfos('<br/>',$logFile);

// only tweet one thing in one session
$alreadyTweetedSomething = false;

// answer mentions tweets
if (!$alreadyTweetedSomething) {
	$twitter = new TwitterAPIExchange($settings);
	$mentions = json_decode($twitter->buildOauth('https://api.twitter.com/1.1/statuses/mentions_timeline.json', 'GET')->performRequest());
	foreach ($mentions AS $repl_tw) {
		$repl_date = $repl_tw->created_at;
		$repl_dateAgo = time() - strtotime($repl_date);
		
		// if it's still not so long ago
		if ($repl_dateAgo<100000) {
		
			$orig_tx = 0;// default value
			$orig_id = $repl_tw->in_reply_to_status_id_str;
			if (strlen($orig_id)>0) {
				$orig_tw = $twitter->get('statuses/show', array('id' => $orig_id));
				$orig_tx = $orig_tw->text;
			}
			$repl_id = $repl_tw->id_str;
			$repl_tx = $repl_tw->text;
			$repl_sn = $repl_tw->user->screen_name;
			$repl_tx = removeDestination($repl_tx);
			$orig_wo = explode(" ",$orig_tx);
			$repl_wo = explode(" ",$repl_tx);
			$alreadyAnswered = false;
			for ($i=0;$i<$doneTweets->getElementsByTagName('Tweet')->length;$i++) {
				if ($doneTweets->getElementsByTagName('Tweet')->item($i)->getAttribute('inReplyTo') == $repl_id) $alreadyAnswered=true;
			}
			if ($alreadyAnswered) {
				// skip if already answered
			} else {
				$the_reply = "No.";
				// pick a default reply
				if ($readyTweets->getElementsByTagName('Tweet')->length > 0) $the_reply = $readyTweets->getElementsByTagName('Tweet')->item(0)->getAttribute('text');
				$longest=0;
				for ($o_i=-1;$o_i<count($orig_wo);$o_i++) {
					for ($r_i=-1;$r_i<count($repl_wo);$r_i++) {
						$wordA = "";
						$wordB = "";
						if ($o_i>=0) $wordA = $orig_wo[$o_i];
						if ($r_i>=0) $wordB = $repl_wo[$r_i];
						$thisLength = strlen($wordA) + strlen($wordB);
						if ($thisLength>$longest) {
							$ch = curl_init();
							$queryUrl='https://api.twitter.com/1.1/search/tweets.json?q=' . $wordA . '%20' . $wordB;
							curl_setopt($ch, CURLOPT_URL, $queryUrl);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							$outputs = json_decode(curl_exec($ch));
							curl_close($ch);
							if (isset($outputs->results)) {
								$output = $outputs->results;
								if (count($output)>0) {
									// pick the shortest answer
									for ($i=0;$i<count($output);$i++) {
										if ($i==0 || strlen($output[0]->text)<strlen($the_reply)) $the_reply = $output[0]->text;
									}
									$longest = $thisLength;
								}
							}else{
								logInfos("can't access search $outputs->results <br/>",$logFile);
							}
						}
					}
				}
				$the_reply = removeDestination($the_reply);
				// add the mention if there is some room for it
				if (strlen($the_reply)+1+strlen($repl_sn)<140) $the_reply = '@' . $repl_sn . ' ' . $the_reply;
				while ((strlen($the_reply)>140 || substr($the_reply,strlen($the_reply)-1)!=" ") && strlen($the_reply)>30) $the_reply=substr($the_reply,0,strlen($the_reply)-1);
				if ($allowTwitterPosts) {
					$postfields = array('status' => $the_reply, 
										'in_reply_to_status_id' => $repl_id,
										'place_id' => $currentLocationName,
										'display_coordinates' => 'true',
										'lat' => $newLatitude,
										'long' => $newLongitude);
					$twitter = new TwitterAPIExchange($settings);
					$outputs = json_decode($twitter->buildOauth('https://api.twitter.com/1.1/statuses/update', 'POST')->setPostfields($postfields)->performRequest());
				}
				logInfos('Answered to : "' . $repl_tx . '" with "' . $the_reply . '"<br/>',$logFile);
				$alreadyTweetedSomething = true;

				// put the tweet in the done list
				$theTweet = $doc->createElement('Tweet');
				$theTweet->setAttribute('text', utf8_encode($the_reply));
				$theTweet->setAttribute('inReplyTo', $repl_id);
				$theTweet->setAttribute("usedDate", date('c'));
				$doneTweets->appendChild($theTweet);
				
				// add words from the twit to the WIP section
				for ($i=2;$i<count($repl_wo);$i++) {
					for ($i2=0;$i2<$wipSentences->getElementsByTagName('Sentence')->length;$i2++) {
						$thisSentence = $wipSentences->getElementsByTagName('Sentence')->item($i2);
						$lastChar=substr($thisSentence->getAttribute('text'),-1);
						if ($lastChar!="." && $lastChar!="?" && $lastChar!="!") {
							$previousWords = $repl_wo[$i-1];
							if (strlen($previousWords)<4) $previousWords = $repl_wo[$i-2] . " " . $repl_wo[$i-1];
							if (endTheSameWay($thisSentence->getAttribute('text'),$previousWords)) {
								$thisSentence->setAttribute('text',$thisSentence->getAttribute('text') . ' ' . utf8_encode($repl_wo[$i]));
								$thisSentence->setAttribute('tries',0);
								// skips the next word
								$i++;
								logInfos('Append mention word : "' . $thisSentence->getAttribute('text') . '" -> "' . $repl_wo[$i] . '"<br/>',$logFile);
							}
							$thisSentence->setAttribute('tries',$thisSentence->getAttribute('tries')+1);
						}
					}
				}
				
			}
		}
	}
}
logInfos('<br/>',$logFile);

// retweet something
if (rand(0,100)<5 && !$alreadyTweetedSomething) {
	$nbWipSentences = $wipSentences->getElementsByTagName('Sentence')->length;
	if ($nbWipSentences>0) $randomWord = $wipSentences->getElementsByTagName('Sentence')->item(rand(0,$nbWipSentences-1))->getAttribute('text');
	if (!(strpos($randomWord,' ') === false)) $randomWord = substr($randomWord,0,strpos($randomWord,' '));
	$randomWord = urlencode($randomWord);
	if (strlen($randomWord)>0) {
		$ch = curl_init();
		$queryUrl='https://api.twitter.com/1.1/search/tweets.json?q=' . $randomWord;
		curl_setopt($ch, CURLOPT_URL, $queryUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$outputs = json_decode(curl_exec($ch));
		curl_close($ch);
		$output = $outputs->results;
		if (count($output)>0) {
			$theId = $output[0]->id_str;
			$alreadyTweetedSomething = true;
			$twitter = new TwitterAPIExchange($settings);
			$outputs = json_decode($twitter->buildOauth('https://api.twitter.com/1.1/statuses/retweet/' . $theId, 'GET')->performRequest());			
			logInfos('Retweet : ' . $theId . '<br/>',$logFile);
			logInfos('<br/>',$logFile);
		}
	}
}

// favorite a tweet
if (rand(0,100)<10) {
	$nbWipSentences = $wipSentences->getElementsByTagName('Sentence')->length;
	if ($nbWipSentences>0) $randomWord = $wipSentences->getElementsByTagName('Sentence')->item(rand(0,$nbWipSentences-1))->getAttribute('text');
	if (!(strpos($randomWord,' ') === false)) $randomWord = substr($randomWord,0,strpos($randomWord,' '));
	$randomWord = urlencode($randomWord);
	if (strlen($randomWord)>0) {
		$ch = curl_init();
		$queryUrl='https://api.twitter.com/1.1/search/tweets.json?q=' . $randomWord;
		curl_setopt($ch, CURLOPT_URL, $queryUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$outputs = json_decode(curl_exec($ch));
		curl_close($ch);
		$output = $outputs->results;
		if (count($output)>0) {
			$theId = $output[0]->id_str;
			if ($allowTwitterPosts) $twitter->post('favorites/create/' . $theId);
			logInfos('Favorited : ' . $theId . '<br/>',$logFile);
			logInfos('<br/>',$logFile);
		}
	}
}

// send a tweet from the ready list
// if there is at least one ready tweet
if (!$alreadyTweetedSomething && $readyTweets->getElementsByTagName('Tweet')->length > 0) {

	$theTweet = $readyTweets->getElementsByTagName('Tweet')->item(0);
	
	// then send it
	if ($allowTwitterPosts) {
		$postfields = array('status' => htmlspecialchars_decode($theTweet->getAttribute('text')), 'place_id' => $currentLocationName, 'display_coordinates' => 'true', 'lat' => $newLatitude, 'long' => $newLongitude);
		$twitter = new TwitterAPIExchange($settings);
		echo $twitter->buildOauth('https://api.twitter.com/1.1/statuses/update.json', 'POST')->setPostfields($postfields)->performRequest();
	}
	
	$alreadyTweetedSomething = true;
	
	// moves it to the already tweeted list
	$theTweet->setAttribute("usedDate", date('c'));
	$doneTweets->appendChild($theTweet);
	
	// log infos
	logInfos('Twitter status update : <br/>',$logFile);
	logInfos($theTweet->getAttribute('text') . '<br/>',$logFile);
	logInfos('<br/>',$logFile);
	
}

// try to find hashtags
logInfos("Checking some follower statuses : ",$logFile);
$aFollower = randomFollowerName($settings);
logInfos($aFollower . "<br/>",$logFile);
$getField = '?include_entities=true&count=3&screen_name=' . $aFollower;
$twitter = new TwitterAPIExchange($settings);
$outputs = json_decode($twitter->setGetfield($getField)->buildOauth('https://api.twitter.com/1.1/statuses/user_timeline.json', 'GET')->performRequest());
foreach ($outputs AS $output) {
	if (isset($output->entities->hashtags)) {
		foreach ($output->entities->hashtags AS $newHashtag) {
			// check if the address is not already banned or to be visited
			$found=false;
			for ($j=0;$j<$hashtags->getElementsByTagName('Hashtag')->length && !$found;$j++) {
				if ($hashtags->getElementsByTagName('Hashtag')->item($j)->getAttribute("tag")==$newHashtag->text) $found=true;
			}
			if (!$found) {
				$hashtag = $doc->createElement("Hashtag");
				$hashtag->setAttribute("tag", $newHashtag->text);
				$hashtag->setAttribute("text", $output->text);
				$hashtag->setAttribute("screenName", $aFollower);
				$hashtag->setAttribute("date", date('c'));
				$hashtags->appendChild($hashtag);
				logInfos("new hashtag : " . $newHashtag->text . " associated with text " . $output->text . "<br/>",$logFile);
			}
		}
		$maxUrlToAdd = rand(0,1);
		for ($i=0;$i<min($maxUrlToAdd,$output->entities->urls->length);$i++) {
			$url = $output->entities->urls->item($i);
			// check if the address is not already banned or to be visited
			$found=false;
			for ($j=0;$j<$bannedUrl->getElementsByTagName('Url')->length && !$found;$j++) {
				if ($bannedUrl->getElementsByTagName('Url')->item($j)->getAttribute('address')==$url->expanded_url) $found=true;
			}
			for ($j=0;$j<$urlToVisit->getElementsByTagName('Url')->length && !$found;$j++) {
				if ($urlToVisit->getElementsByTagName('Url')->item($j)->getAttribute('address')==$url->expanded_url) $found=true;
			}
			if (!$found) {
				$defaultURL = $doc->createElement("Url");
				$defaultURL->setAttribute("address", $url->expanded_url);
				$defaultURL->setAttribute("addDate", date('c'));
				$urlToVisit->appendChild($defaultURL);
				logInfos("new url : " . $url->expanded_url . "<br/>",$logFile);
			}
		}
	}
}
logInfos("<br/><br/>",$logFile);

// add some random .com url
$randomWord = chr(rand(0,25)+97) . chr(rand(0,25)+97);
while (!(pageIsEasilyReadable("http://www." . $randomWord . ".com",$logFile) == 'nothing wrong')) {
	$randomWord = chr(rand(0,25)+97) . chr(rand(0,25)+97);
}
while (pageIsEasilyReadable("http://www." . $randomWord . ".com",$logFile) == 'nothing wrong') {
	$randomWord .= chr(rand(0,25)+97);
}
$randomWord = substr($randomWord,0,strlen($randomWord)-1);
$defaultURLAddress = "http://www." . $randomWord . ".com";
$defaultURL = $doc->createElement("Url");
$defaultURL->setAttribute("address", $defaultURLAddress);
$defaultURL->setAttribute("addDate", date('c'));
$urlToVisit->appendChild($defaultURL);
logInfos('Address added : ',$logFile);
logInfos($defaultURLAddress . '<br/>',$logFile);
logInfos('<br/>',$logFile);

$currentURL = $urlToVisit->getElementsByTagName('Url')->item(0);

// make sure the file is successfully opened before doing anything else
if ($fp = fopen($currentURL->getAttribute('address'), 'r')) 
{
	// puts the page lines in $content
	$content = '';
	while ($line = fread($fp, 1024)) 
	{
		$content .= $line;
	}

	logInfos('URL successfully red : <br/>',$logFile);
	logInfos($currentURL->getAttribute('address') . '<br/>',$logFile);
	logInfos('Contains ' . count($content) . ' lines.<br/>',$logFile);
	logInfos('<br/>',$logFile);

	$currentURL->setAttribute('visitDate',date('c'));
	$currentURL->setAttribute('result','visited');
	$bannedUrl->appendChild($currentURL);

	logInfos('Current URL added to the banned list : <br/>',$logFile);
	logInfos('<br/>',$logFile);
	
} else
{
	logInfos('Failed to read URL : <br/>',$logFile);
	logInfos($currentURL->getAttribute('address') . '<br/>',$logFile);
	logInfos('<br/><br/>',$logFile);
	
	$currentURL->setAttribute('visitDate',date('c'));
	$currentURL->setAttribute('result','unable to visit');
	$bannedUrl->appendChild($currentURL);

	logInfos('Current URL added to the banned list : <br/>',$logFile);
	logInfos('<br/><br/>',$logFile);
	
}

// separate html from text
$plainText='';
$htmlContent='';

$insideBal = 0;
$insideScript = 0;
$insideCom = 0;
$insideBracket = 0;
for ($i=0;$i<strlen($content);$i++) 
{
	if (substr($content,$i,1)=='<') $insideBal=1;
	if (substr($content,$i,7)=='<script') $insideScript=1;
	if (substr($content,$i,2)=='/*') $insideCom=1;
	if (substr($content,$i,1)=='{') $insideBracket=1;
	
	if ($insideBal==0 && substr($content,$i-1,1)=='>') $plainText .= ' ';
	if ($insideBal==0 && $insideScript==0 && $insideCom==0) $plainText .= substr($content,$i,1);
	if ($insideBal>0) $htmlContent .= substr($content,$i,1);
	
	if (substr($content,$i,1)=='>') $insideBal=0;
	if (substr($content,$i,8)=='</script') $insideScript=0;
	if (substr($content,$i,2)=='*/') $insideCom=0;
	if (substr($content,$i,1)=='}') $insideBracket=0;
}

logInfos('Text data size : ' . strlen($plainText) . '<br/>',$logFile);
logInfos('Html data size : ' . strlen($htmlContent) . '<br/>',$logFile);

// find addresses in text
$newAddresses = extractUrl($htmlContent);
logInfos('New addresses found : ' . count($newAddresses) . '<br/>',$logFile);
logInfos('<br/>',$logFile);

// put emails in the email list
for ($i=0;$i<count($newAddresses);$i++) 
{
	if (substr($newAddresses[$i],0,7)=='mailto:') {
		// check if the email is not already in the list
		$found = false;
		for ($i2=0;$i2<$emailAddresses->getElementsByTagName('Email')->length;$i2++) {
			if ($emailAddresses->getElementsByTagName('Email')->item($i2)->getAttribute('address')==substr($newAddresses[$i],7)) $found = true;
		}
		if (!$found) {
			$theEmail = $doc->createElement("Email");
			$theEmail->setAttribute("address", substr($newAddresses[$i],7));
			$theEmail->setAttribute("foundOn", $currentURL->getAttribute('address'));
			$emailAddresses->appendChild($theEmail);
			unset($newAddresses[$i]);
			$newAddresses = array_values($newAddresses);
			// because of shifting
			$i--;
		}
	}
}

// fix relative adresses
$nbRelativeFixed=0;
for ($i=0;$i<count($newAddresses);$i++) 
{
	if (substr($newAddresses[$i],0,7)!='http://' && substr($newAddresses[$i],0,4)!='www.') 
	{
		if (substr($newAddresses[$i],0,1)!="/") $newAddresses[$i] = "/" . $newAddresses[$i];
		$newAddresses[$i] = $currentURL->getAttribute('address') . $newAddresses[$i];
		$nbRelativeFixed++;
	}
}
logInfos($nbRelativeFixed . ' relative addresses fixed<br/>',$logFile);
logInfos('<br/>',$logFile);

// remove slashes at the end of urls
for ($i=0;$i<count($newAddresses);$i++) 
{
	while (substr($newAddresses[$i],-1,1)=="/") $newAddresses[$i] = substr($newAddresses[$i],0,strlen($newAddresses[$i])-1);
}

// delete addresses that are already used
$nbTwinsAvoided=0;
for ($i=0;$i<count($newAddresses);$i++) 
{
	$found = false;
	for ($i2=0 ; $i2<$urlToVisit->getElementsByTagName('Url')->length && !$found ; $i2++ )
		if ($urlToVisit->getElementsByTagName('Url')->item($i2)->getAttribute('address') == $newAddresses[$i]) $found = true;
	for ($i2=0 ; $i2<$bannedUrl->getElementsByTagName('Url')->length && !$found ; $i2++ )
		if ($bannedUrl->getElementsByTagName('Url')->item($i2)->getAttribute('address') == $newAddresses[$i]) $found = true;
	for ($i2=$i+1 ; $i2<count($newAddresses) && !$found ; $i2++ )
		if ($newAddresses[$i2] == $newAddresses[$i]) $found = true;
	if ($found)
	{
		$nbTwinsAvoided++;
		unset($newAddresses[$i]);
		$newAddresses = array_values($newAddresses);
		// because of shifting
		$i--;
	}
}
logInfos($nbTwinsAvoided . ' twin addresses avoided<br/>',$logFile);
logInfos('<br/>',$logFile);

// remove long urls
$nbLongDeleted=0;
for ($i=0;$i<count($newAddresses);$i++) 
{
	if (strlen($newAddresses[$i])>50) 
	{
		unset($newAddresses[$i]);
		$newAddresses = array_values($newAddresses);
		$nbLongDeleted++;
		// because of shifting
		$i--;		
	}
}
logInfos($nbLongDeleted . ' long adresses removed<br/>',$logFile);
logInfos('<br/>',$logFile);

// limit number of addresses
$adressNumberLimit = 10;
array_splice($newAddresses, $adressNumberLimit);
logInfos('address limited to ' . $adressNumberLimit . '<br/>',$logFile);
logInfos('<br/>',$logFile);

// checks if the addresses are correct
$totalAddressAdded = 0;
$pagesRed=0;
for ($i=0;$i<count($newAddresses);$i++) 
{
	// check if the address is not already banned or to be visited
	$found=false;
	for ($j=0;$j<$bannedUrl->getElementsByTagName('Url')->length && !$found;$j++) {
		if ($bannedUrl->getElementsByTagName('Url')->item($j)->getAttribute('address')==$newAddresses[$i]) $found=true;
	}
	for ($j=0;$j<$urlToVisit->getElementsByTagName('Url')->length && !$found;$j++) {
		if ($urlToVisit->getElementsByTagName('Url')->item($j)->getAttribute('address')==$newAddresses[$i]) $found=true;
	}
	if (!$found) {
		$resultReadable = pageIsEasilyReadable($newAddresses[$i],$logFile);
		if ($resultReadable == 'nothing wrong') {
			$theNewUrl = $doc->createElement("Url");
			$theNewUrl->setAttribute("address", $newAddresses[$i]);
			$theNewUrl->setAttribute("addDate", date('c'));
			$urlToVisit->appendChild($theNewUrl);
			$totalAddressAdded++;
		} else {
			$theNewUrl = $doc->createElement("Url");
			$theNewUrl->setAttribute("address", $newAddresses[$i]);
			$theNewUrl->setAttribute("addDate", date('c'));
			$theNewUrl->setAttribute("result", $resultReadable);
			$bannedUrl->appendChild($theNewUrl);
		}
	}
}
logInfos('<br/>',$logFile);

logInfos('total number of new address to visit next : ' . $totalAddressAdded . '<br/>',$logFile);
logInfos('<br/>',$logFile);

// replace 32 first ASCII characters from text by spaces
$unwanted_chars = array();
for ($i=0;$i<33;$i++) $unwanted_chars[] = chr($i);
$plainText = str_replace($unwanted_chars,' ',$plainText);

// separate words in text
$words = array_filter(explode(" ", $plainText));

// remove spaces
foreach ($words as $word) 
{
	if ($word==" ")
	{
		unset($word);
		$words = array_values($words);
	}
}

logInfos('number of words in the text : ' . count($words) . '<br/>',$logFile);
logInfos('<br/>',$logFile);

// limit number of words
$wordsNumberLimit = 500;
array_splice($words, $wordsNumberLimit);
logInfos('words limited to ' . $wordsNumberLimit . '<br/>',$logFile);
logInfos('<br/>',$logFile);

logInfos('final text data to parse :<br/>',$logFile);
foreach ($words AS $word) logInfos($word . " ",$logFile);
logInfos('<br/><br/>',$logFile);

// remove arobases at first position to avoid twitter problems
for ($i=0;$i<count($words);$i++) {
	if (substr($words[$i],0,1)=="@") $words[$i]=substr($words[$i],1);
}

// process the words

// checks if this word could be added to a sentence
for ($i=2;$i<count($words);$i++) {
	for ($i2=0;$i2<$wipSentences->getElementsByTagName('Sentence')->length && $i<count($words);$i2++) {
		$thisSentence = $wipSentences->getElementsByTagName('Sentence')->item($i2)->getAttribute('text');
		$lastChar=substr($thisSentence,-1,1);
		if ($lastChar!="." && $lastChar!="?" && $lastChar!="!") {
			$previousWords = $words[$i-1];
			// take two previous words for young sentences
			if ($wipSentences->getElementsByTagName('Sentence')->item($i2)->getAttribute('tries')<2000) $previousWords = $words[$i-2] . " " . $words[$i-1];
			// if they end the same way
			if (endTheSameWay($thisSentence,$previousWords)) {
				// appends the word
				$newText = $thisSentence . " " . $words[$i];
				logInfos('append : "' . $thisSentence . '" -> "' . $words[$i] . '"<br/>',$logFile);
				$wipSentences->getElementsByTagName('Sentence')->item($i2)->setAttribute('text',utf8_encode($newText));
				$wipSentences->getElementsByTagName('Sentence')->item($i2)->setAttribute('tries',0);
				// skips the next word
				$i++;
			}
			// increases the tries
			$newTries = $wipSentences->getElementsByTagName('Sentence')->item($i2)->getAttribute('tries') + 1;
			$wipSentences->getElementsByTagName('Sentence')->item($i2)->setAttribute('tries',$newTries);
		}
	}
}
logInfos('<br/>',$logFile);

// checks if this word could be used as a new sentence
for ($i=2;$i<count($words);$i++) {
	$lastChar=substr($words[$i-1],-1,1);
	if ($lastChar=="." || $lastChar=="?" || $lastChar=="!") {
		// if this is not already the beginning of a sentence
		$found=false;
		for ($i2=0;$i2<$wipSentences->getElementsByTagName('Sentence')->length && !$found;$i2++) {
			$thisSentence = $wipSentences->getElementsByTagName('Sentence')->item($i2)->getAttribute('text');
			if (substr($thisSentence,0,strlen($words[$i]))==$words[$i]) $found=true;
		}
		if (!$found) {
			// create a new Wip sentence
			logInfos('new sentence : "' . $words[$i] . '"<br/>',$logFile);			
			$theNewSentence = $doc->createElement("Sentence");
			$theNewSentence->setAttribute("text", utf8_encode($words[$i]));
			$theNewSentence->setAttribute("startDate", date('c'));
			$theNewSentence->setAttribute("tries", 0);
			$wipSentences->appendChild($theNewSentence);
			// skips the next word
			$i++;
		}
	}
}
logInfos('<br/>',$logFile);

// try to end WIP twits with twitter queries
$nbTwitterQueriesMade=0;
for ($i=0 ; $i < $wipSentences->getElementsByTagName('Sentence')->length && $nbTwitterQueriesMade<15; $i++) 
{
	$thisText = $wipSentences->getElementsByTagName('Sentence')->item($i)->getAttribute('text');
	// if it's not complete
	if (substr($thisText,-1)!="." && substr($thisText,-1)!="!" && substr($thisText,-1)!="?") 
	{
		// if it has several words and some age	
		if (substr_count($thisText," ")>1 && $wipSentences->getElementsByTagName('Sentence')->item($i)->getAttribute('tries')>100)
		{
			$nbTwitterQueriesMade++;
			$textToAppend = appendTwitterQueries($thisText,$settings);
			if (strlen($textToAppend)>0) 
			{
				logInfos('append via Twitter queries : "' . $thisText . '" -> "' . $textToAppend . '"<br/>',$logFile);
				$thisText .= ' ' . $textToAppend;
				$wipSentences->getElementsByTagName('Sentence')->item($i)->setAttribute('text',utf8_encode($thisText));
				$wipSentences->getElementsByTagName('Sentence')->item($i)->setAttribute('tries',0);
			}
		}
	}
}
logInfos('<br/>',$logFile);

// move complete WIP sentences to either the ready or big sentences list
for ( $i=0;$i<$wipSentences->getElementsByTagName('Sentence')->length;$i++ ) 
{
	$thisSentence = $wipSentences->getElementsByTagName('Sentence')->item($i);
	$thisText = $thisSentence->getAttribute('text');
	// if the sentence is complete
	if (substr($thisText,-1)=="." || substr($thisText,-1)=="?" || substr($thisText,-1)=="!")
	{
		// if the text is too big
		if (strlen($thisText)>=140) 
		{
			// move it to the "big sentences" list or delete it
			if (strlen($thisText)>=500) {
				$thisSentence->setAttribute("finishedDate", date('c'));
				$bigSentences->appendChild($thisSentence);
				logInfos('Big sentence complete : ' . $thisText . '<br/>',$logFile);
			} else {
				$wipSentences->removeChild($wipSentences->getElementsByTagName('Sentence')->item($i));
				logInfos('Big sentence deleted : ' . $thisText . '<br/>',$logFile);
			}
		} else {
			// if it hasn't been tweeted yet
			$found=false;
			for ($i=0;$i<$doneTweets->getElementsByTagName('Tweet')->length;$i++) {
				if ($doneTweets->getElementsByTagName('Tweet')->item($i)->getAttribute('text')==$thisSentence->getAttribute('text')) $found=true;
			}
			if ($found)
			{
				$wipSentences->removeChild($thisSentence);
				logInfos('Remove already tweeted : ' . $thisText . '<br/>',$logFile);
			} else {
				// try to add someone to say it to
				$messageTo = randomFollowerName($settings);
				if (strlen($thisText)+2+$messageTo < 130 && rand(0,1000)<3) {
					$thisText = '@' . $messageTo . ' ' . $thisText;
					logInfos('Address the tweet to' . $messageTo . '<br/>',$logFile);
				}
				
				// try to add a hashtag
				if ($hashtags->getElementsByTagName('Hashtag')->length>0) {
					$tagIndex = rand(0,$hashtags->getElementsByTagName('Hashtag')->length-1);
					$theTag = "#" . $hashtags->getElementsByTagName('Hashtag')->item($tagIndex)->getAttribute('tag');
					if (strlen($thisText)+2+$theTag < 130 && rand(0,1000)<500) {
						$thisText = $thisText . " " . $theTag;
						logInfos('Add hashtag to the tweet : ' . $theTag . ' and remove this hashtag<br/>',$logFile);
						$hashtags->removeChild($hashtags->getElementsByTagName('Hashtag')->item($tagIndex));
					}
				}

				// move it to the "ready" list
				$theTweet = $doc->createElement("Tweet");
				$theTweet->setAttribute("text", $thisText);
				$theTweet->setAttribute("startDate", $thisSentence->getAttribute('startDate'));
				$theTweet->setAttribute("finishedDate", date('c'));
				$readyTweets->appendChild($theTweet);
				$wipSentences->removeChild($thisSentence);
				logInfos('One tweet ready : ' . $thisText . '<br/>',$logFile);
			}
		}
		// redo the current item because of shifting
		$i--;
	}
}
logInfos('<br/>',$logFile);

// delete location logs (limit to 30)
while ($trip->getElementsByTagName('LocationLog')->length>30)
{
	logInfos('Delete LocationLog : "' . $trip->getElementsByTagName('LocationLog')->item(0)->getAttribute('name') . '" <br/>',$logFile);
	$trip->removeChild($trip->getElementsByTagName('LocationLog')->item(0));
}
logInfos('<br/>',$logFile);

// delete old WIP sentences (limit to 500)
while ($wipSentences->getElementsByTagName('Sentence')->length>500)
{
	$oldest = 0;
	$oldestIndex = -1;
	for ( $i=0;$i<$wipSentences->getElementsByTagName('Sentence')->length;$i++ ) 
	{
	$thisAge = strlen($wipSentences->getElementsByTagName('Sentence')->item($i)->getAttribute('tries'));
		if ($oldestIndex == -1 || $thisAge > $oldest)
		{
			$oldest = $thisAge;
			$oldestIndex = $i;
		}
	}
	logInfos('Delete WIP sentence : "' . $wipSentences->getElementsByTagName('Sentence')->item($oldestIndex)->getAttribute('text') . '" ',$logFile);
	logInfos('of age ' . $wipSentences->getElementsByTagName('Sentence')->item($oldestIndex)->getAttribute('tries') . '<br/>',$logFile);
	$wipSentences->removeChild($wipSentences->getElementsByTagName('Sentence')->item($oldestIndex));
}
logInfos('<br/>',$logFile);

// delete url to visit (limit to 50)
while ($urlToVisit->getElementsByTagName('Url')->length>50)
{
	logInfos('Delete Url to visit : "' . $urlToVisit->getElementsByTagName('Url')->item(0)->getAttribute('address') . '" <br/>',$logFile);
	$urlToVisit->removeChild($urlToVisit->getElementsByTagName('Url')->item(0));
}
logInfos('<br/>',$logFile);

// delete banned url (limit to 50)
while ($bannedUrl->getElementsByTagName('Url')->length>50)
{
	logInfos('Delete banned Url : "' . $bannedUrl->getElementsByTagName('Url')->item(0)->getAttribute('address') . '" <br/>',$logFile);
	$bannedUrl->removeChild($bannedUrl->getElementsByTagName('Url')->item(0));
}
logInfos('<br/>',$logFile);

// delete already twitted (limit to 50)
while ($doneTweets->getElementsByTagName('Tweet')->length>50)
{
	logInfos('Delete done tweet : "' . $doneTweets->getElementsByTagName('Tweet')->item(0)->getAttribute('address') . '" <br/>',$logFile);
	$doneTweets->removeChild($doneTweets->getElementsByTagName('Tweet')->item(0));
}
logInfos('<br/>',$logFile);

// delete hashtags (limit to 100)
while ($hashtags->getElementsByTagName('Hashtag')->length>100)
{
	logInfos('Delete hashtag : "' . $hashtags->getElementsByTagName('Hashtag')->item(0)->getAttribute('tag') . '" <br/>',$logFile);
	$hashtags->removeChild($hashtags->getElementsByTagName('Hashtag')->item(0));
}
logInfos('<br/>',$logFile);

// delete short ready tweets (limit to 50)
while ($readyTweets->getElementsByTagName('Tweets')->length>50)
{
	$shortest = 0;
	$shortestIndex = -1;
	for ( $i=0;$i<$readyTweets->getElementsByTagName('Tweet')->length;$i++ ) 
	{
	$thisLength = strlen($readyTweets->getElementsByTagName('Tweet')->item($i)->getAttribute('text'));
		if ($shortestIndex == -1 || $thisLength < $shortest)
		{
			$shortest = $thisLength;
			$shortestIndex = $i;
		}
	}
	logInfos('Delete ready tweet : "' . $readyTweets->getElementsByTagName('Tweet')->item($i)->getAttribute('text') . '" <br/>',$logFile);
	$readyTweets->removeChild($readyTweets->getElementsByTagName('Tweet')->item($i));
}

// update general infos
$generalInfos->getElementsByTagName('lastSave')->item(0)->setAttribute('date',date('c'));
logInfos('<br/>',$logFile);

// save the XML file
if ($saveXmlFile) {
	$doc->saveXML();
	$doc->save($xmlFileUrl);
	$doc->save('backups/' . date('c') . $xmlFileUrl);
	//logInfos('Xml file saved <br/>');
	logInfos('<br/>',$logFile);
}

// save the log file
$f = fopen("logFile.html", 'w');
fwrite($f, $logFile);
fclose($f);

logInfos('done !<br/>',$logFile);

?>

<?php

function appendTwitterQueries($text,$settings) 
{
	
	// shorten the length to max 3 spaces
	while (substr_count($text, " ")>3) $text = substr($text, strpos($text, " ") + 1);
	$followingText = "";
	// continue to shorten it if nothing found
	while ($followingText == "" && strpos($text, " ") && strlen($text)>2) 
	{
		$possibleResults = twitterQueryTexts($text,$settings);
		
		// make an array of first words
		$firstWordsInResults = array();
		for ($i=0 ; $i<count($possibleResults) ; $i++) 
		{
			$thisResult = $possibleResults[$i];
			if (substr_count($thisResult,strtolower($text . " "))) 
			{
				$thisResult = substr($thisResult, strpos($thisResult, strtolower($text . " ")) + strlen($text));
				while (substr($thisResult,0,1)==" " || substr($thisResult,0,1)==chr(0)) $thisResult = substr($thisResult,1);
				$firstWord = $thisResult;
				if (substr_count($firstWord, " ")>0) 
				{
					$wordsInTheResult = explode(" ",$firstWord);
					$firstWord = $wordsInTheResult[0];
				}
				if (strlen($firstWord)>1) 
				{
					$firstWordsInResults[] = $firstWord;
				}
			}
		}
		
		
		// search for a word that would end the sentence
		for ($i=0 ; $i<count($firstWordsInResults) ; $i++) 
		{
			if (substr($firstWordsInResults[$i],-1)=="." || substr($firstWordsInResults[$i],-1)=="!"  || substr($firstWordsInResults[$i],-1)=="?") 
			{
				$followingText = $firstWordsInResults[$i];
			}
		}
		
		// otherwise, just pick a short word
		if ($followingText == "") 
		{
			$shorter = "";
			for ($i=0 ; $i<count($firstWordsInResults) ; $i++) 
			{
				if ($shorter == "" || strlen($shorter)>$firstWordsInResults[$i]) $shorter = $firstWordsInResults[$i];
			}
			$followingText = $shorter;
		}
		
		// shorten the text for the next iteration
		$text = substr($text, strpos($text, " ") + 1);
	}
	
	return $followingText;
	
}

function twitterQueryTexts($text,$settings) 
{
	// returns an array of text strings for this Twitter query
	$text = urlencode($text);
	$twitter = new TwitterAPIExchange($settings);
	$getField = '?q=' . urlencode(utf8_encode($text));
	$statuses = json_decode($twitter->setGetfield($getField)->buildOauth('https://api.twitter.com/1.1/search/tweets.json', 'GET')->performRequest());
	$output = $statuses->{"statuses"};
	$textsFromOutput = array();
	for ($i=0 ; $i<count($output) ; $i++) 
	{
		$textsFromOutput[] = $output[$i]->text;
	}
	return $textsFromOutput;
}

function logInfos($txt,$logFile) {
	echo $txt;
	$logFile .= $txt;
}

function constrain($v,$min,$max) {
	return max(min($v,$max),$min);
}

function extractUrl($html) {
	$addresses = array();
	for ($i=0 ; $i<strlen($html) ; $i++) {
		if (substr($html,$i,8)=='a href="') {
			$i+=8;
			$i2=$i;
			while ($i2<strlen($html) && substr($html,$i2,1)!='"' && substr($html,$i2,1)!="'") $i2++;
			if ($i2<strlen($html)) $newAdress = substr($html,$i,$i2-$i);
			$addresses[] = $newAdress;
		}
	}
	return $addresses;
}

function pageIsEasilyReadable($address,$logFile) {
	$handle = curl_init($address);
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($handle);
	$pageSize = curl_getinfo($handle, CURLINFO_SIZE_DOWNLOAD);
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	curl_close($handle);
	logInfos('Checking page : ' . $address . ' ',$logFile);
	logInfos('HTTP Code : ' . $httpCode . ' ',$logFile);
	logInfos('Page size : ' . $pageSize . '<br/>',$logFile);
	if ($httpCode!=200) return 'HTTP code ' . $httpCode;
	if ($pageSize>500000) return 'page size ' . $pageSize;
	return 'nothing wrong';
}

function endTheSameWay($ful,$end) {
	$minLength = min(strlen($ful),strlen($end));
	if (strtolower(substr($end,-$minLength)) == 
		strtolower(substr($ful,-$minLength))) {
		if (strlen($ful)==strlen($end)) return true;
	    if (strlen($ful)>strlen($end)) {
			if (substr($ful,-(strlen($end)+1),1)==" ") return true;
		}
	    if (strlen($ful)<strlen($end)) {
			if (substr($end,-(strlen($ful)+1),1)==" ") return true;
		}		
	}
	return false;
}

function removeDestination($txt) {
	for ($i=0;$i<strlen($txt);$i++) {
		if (substr($txt,$i,1)=="@") {
			for ($i2=$i;$i2<strlen($txt);$i2++) {
				if (substr($txt,$i2,1)==" " || $i2==strlen($txt)-1) {
					$txt = substr($txt,0,$i) . substr($txt,$i2+1);
					$i2 = strlen($txt);
				}
			}
		}
	}
	return $txt;
}

function getDirectoryList($directory) {
	$results = array();
	$handler = opendir($directory);
	while ($file = readdir($handler)) {
		if ($file != "." && $file != "..") {
			$results[] = $file;
		}
	}
	closedir($handler);
	sort($results);
	return $results;
}

function randomFollowerName($settings) {
	$getField = '?screen_name=Retroslime&cursor=-1';
	$twitter = new TwitterAPIExchange($settings);
	$outputs = json_decode($twitter->setGetfield($getField)->buildOauth('https://api.twitter.com/1.1/followers/ids.json', 'GET')->performRequest());
	$chosenId = $outputs->ids[rand(0,count($outputs->ids)-1)];
	$getField = '?user_id=' . $chosenId;
	$twitter = new TwitterAPIExchange($settings);
	$outputs = json_decode($twitter->setGetfield($getField)->buildOauth('https://api.twitter.com/1.1/users/lookup.json', 'GET')->performRequest());
	return $outputs[0]->screen_name;
}

?>
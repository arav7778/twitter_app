<!DOCTYPE html>
<html>
<head>

<script class="include" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="scripts/raphael.js"></script>
<script type="text/javascript" src="scripts/jquery.enumerable.js"></script>
<script type="text/javascript" src="scripts/jquery.tufte-graph.js"></script>
<link rel="stylesheet" href="css/tufte-graph.css" type="text/css" media="screen" charset="utf-8" />

</head>
<body>
<?php
    
    # Load Twitter class
    require_once('TwitterOAuth.php');
    
    # Define constants
    define('TWEET_LIMIT', 500);
    define('TWITTER_USERNAME', 'ndtv');
    define('CONSUMER_KEY', 'Need to be filled');
    define('CONSUMER_SECRET', 'Need to be filled');
    define('ACCESS_TOKEN', 'Need to be filled');
    define('ACCESS_TOKEN_SECRET', 'Need to be filled');
    
    #tweetHourArray
    $tweetHourCount = array();
    for ($i = 0; $i < 24; $i++) {
        $tweetHourCount[$i] = 0;
    }
    
    # Create the connection
    $twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
    
    # Migrate over to SSL/TLS
    $twitter->ssl_verifypeer = true;
    
    #Total Tweet Count
    $totTwtCtr = 0;
    
    # Load the Tweets
    $tweets = $twitter->get('statuses/user_timeline', array(
                                                            'screen_name' => TWITTER_USERNAME,
                                                            'exclude_replies' => 'true',
                                                            'include_rts' => 'false',
                                                            'count' => TWEET_LIMIT
                                                            ));
    
    echo "<h1 class=header-msg> Twitter Messages Histogram  - " . TWITTER_USERNAME . "</h1>";
    # Example output
    if (!empty($tweets)) {
        foreach ($tweets as $tweet) {
            
            $totTwtCtr++;
            
            # Access as an object
            $tweetText = $tweet['text'];
            $tweetTime = $tweet['created_at'];
            
            
            # Make links active
            $tweetText = preg_replace("#(http://|(www\.))(([^\s<]{4,68})[^\s<]*)#", '<a href="http://$2$3" target="_blank">$1$2$4</a>', $tweetText);
            
            # Linkify user mentions
            $tweetText = preg_replace("/@(w+)/", '<a href="http://www.twitter.com/$1" target="_blank">@$1</a>', $tweetText);
            
            # Linkify tags
            $tweetText = preg_replace("/#(w+)/", '<a href="http://search.twitter.com/search?q=$1" target="_blank">#$1</a>', $tweetText);
            
            # Output
            echo '<div class="twitter-text">' . $tweetText . '</div>';
            echo '<div class="twitter-time">' . $tweetTime . '</div>';
            echo '<hr/>';
            
            
            #extract hour alone from the time
            $pattern = '~\s+(\d+):\d+:\d+~';
            $success = preg_match($pattern, $tweetTime, $match);
            if ($success) {
                //echo "Group 1: ".$match[1]."<br />";
                if (($match[1] >= 0) && ($match[1] <= 23))
                    $tweetHourCount[$match[1]]++;
            }
            
        }
    }
    echo "<br/>Total Tweet Counter: " . "$totTwtCtr" . "<br/>";
    
    ?>

<script type="text/javascript">
$(document).ready(function () {
                  jQuery('#awesome-graph').tufteBar({
                                                    data: [
                                                    <?php
                                                    for ($hr = 0; $hr < 24; $hr++) {
                                                    if (($tweetHourCount[$hr]) > 0) {
                                                    $graphVal = "[" . ($tweetHourCount[$hr]) . ", {label: '" . $hr . "'}]";
                                                    } else {
                                                    $graphVal = "[0.8, {label: '" . $hr . "'}]";
                                                    }
                                                    
                                                    if ($hr < 23) {
                                                    $graphVal = $graphVal . ",";
                                                    }
                                                    echo $graphVal;
                                                    }
                                                    ?>
/*
 [120, {label: '0'}],
 */
                                                    ],
                                                    barWidth: 0.8,
                                                    barLabel:  function(index) { if(this[0] < 1) return '0twts'; else return this[0] + 'twts' },
                                                    axisLabel: function(index) { return this[1].label + 'hr'},
                                                    color:     function(index) { return ['#E57536', '#82293B'][index % 2] }
                                                    });
                  
                  });
</script>
<h1> Twitter Histogram </h1>
<div id='awesome-graph' class='graph' style='height: 200px;'></div>
<div style='height:100px;'> </div>

</body>
</html>

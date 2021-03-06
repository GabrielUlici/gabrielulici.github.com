<?php
// OPTIONS - PLEASE CONFIGURE THESE BEFORE USE!

$yourEmail = "ulicigabriel@gmail.com"; // the email address you wish to receive these mails through
$yourWebsite = "My Personal WebPage"; // the name of your website
$thanksPage = ''; // URL to 'thanks for sending mail' page; leave empty to keep message on the same page 
$maxPoints = 4; // max points a person can hit before it refuses to submit - recommend 4
$requiredFields = "name,email,comments"; // names of the fields you'd like to be required as a minimum, separate each field with a comma


// DO NOT EDIT BELOW HERE
$error_msg = null;
$result = null;

$requiredFields = explode(",", $requiredFields);

function clean($data) {
	$data = trim(stripslashes(strip_tags($data)));
	return $data;
}
function isBot() {
	$bots = array("Indy", "Blaiz", "Java", "libwww-perl", "Python", "OutfoxBot", "User-Agent", "PycURL", "AlphaServer", "T8Abot", "Syntryx", "WinHttp", "WebBandit", "nicebot", "Teoma", "alexa", "froogle", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz");

	foreach ($bots as $bot)
		if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
			return true;

	if (empty($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == " ")
		return true;
	
	return false;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isBot() !== false)
		$error_msg .= "No bots please! UA reported as: ".$_SERVER['HTTP_USER_AGENT'];
		
	// lets check a few things - not enough to trigger an error on their own, but worth assigning a spam score.. 
	// score quickly adds up therefore allowing genuine users with 'accidental' score through but cutting out real spam :)
	$points = (int)0;
	
	$badwords = array("adult", "beastial", "bestial", "blowjob", "clit", "cum", "cunilingus", "cunillingus", "cunnilingus", "cunt", "ejaculate", "fag", "felatio", "fellatio", "fuck", "fuk", "fuks", "gangbang", "gangbanged", "gangbangs", "hotsex", "hardcode", "jism", "jiz", "orgasim", "orgasims", "orgasm", "orgasms", "phonesex", "phuk", "phuq", "pussies", "pussy", "spunk", "xxx", "viagra", "phentermine", "tramadol", "adipex", "advai", "alprazolam", "ambien", "ambian", "amoxicillin", "antivert", "blackjack", "backgammon", "texas", "holdem", "poker", "carisoprodol", "ciara", "ciprofloxacin", "debt", "dating", "porn", "link=", "voyeur", "content-type", "bcc:", "cc:", "document.cookie", "onclick", "onload", "javascript");

	foreach ($badwords as $word)
		if (
			strpos(strtolower($_POST['comments']), $word) !== false || 
			strpos(strtolower($_POST['name']), $word) !== false
		)
			$points += 2;
	
	if (strpos($_POST['comments'], "http://") !== false || strpos($_POST['comments'], "www.") !== false)
		$points += 2;
	if (isset($_POST['nojs']))
		$points += 1;
	if (preg_match("/(<.*>)/i", $_POST['comments']))
		$points += 2;
	if (strlen($_POST['name']) < 3)
		$points += 1;
	if (strlen($_POST['comments']) < 15 || strlen($_POST['comments'] > 1500))
		$points += 2;
	// end score assignments

	foreach($requiredFields as $field) {
		trim($_POST[$field]);
		
		if (!isset($_POST[$field]) || empty($_POST[$field]))
			$error_msg .= "Please fill in all the required fields and submit again.\r\n";
	}

	if (!preg_match("/^[a-zA-Z-'\s]*$/", stripslashes($_POST['name'])))
		$error_msg .= "The name field must not contain special characters.\r\n";
	if (!preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($_POST['email'])))
		$error_msg .= "That is not a valid e-mail address.\r\n";
	if (!empty($_POST['url']) && !preg_match('/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $_POST['url']))
		$error_msg .= "Invalid website url.\r\n";
	
	if ($error_msg == NULL && $points <= $maxPoints) {
		$subject = "Automatic Form Email";
		
		$message = "You received this e-mail message through your website: \n\n";
		foreach ($_POST as $key => $val) {
			$message .= ucwords($key) . ": " . clean($val) . "\r\n";
		}
		$message .= "\r\n";
		$message .= 'IP: '.$_SERVER['REMOTE_ADDR']."\r\n";
		$message .= 'Browser: '.$_SERVER['HTTP_USER_AGENT']."\r\n";
		$message .= 'Points: '.$points;

		if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
			$headers   = "From: $yourEmail\n";
			$headers  .= "Reply-To: {$_POST['email']}";
		} else {
			$headers   = "From: $yourWebsite <$yourEmail>\n";
			$headers  .= "Reply-To: {$_POST['email']}";
		}

		if (mail($yourEmail,$subject,$message,$headers)) {
			if (!empty($thanksPage)) {
				header("Location: $thanksPage");
				exit;
			} else {
				$result = 'Your mail was successfully sent.';
				$disable = true;
			}
		} else {
			$error_msg = 'Your mail could not be sent this time. ['.$points.']';
		}
	} else {
		if (empty($error_msg))
			$error_msg = 'Your mail looks too much like spam, and could not be sent this time. ['.$points.']';
	}
}
function get_data($var) {
	if (isset($_POST[$var]))
		echo htmlspecialchars($_POST[$var]);
}
?>

<!DOCTYPE html>
<title>Gabriel Ulici | Contact</title>
<meta charset="utf-8" />
<link rel="stylesheet" href="styles/web.css" type="text/css" />
<script src="scripts/web.js" type="text/javascript"></script>
<!--[if IE]><style>#header h1 a:hover{font-size:75px;}</style><![endif]-->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-12219494-5']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
<!-- Main Menu start -->
<div class="main-container">
  <div id="sub-headline">
    </div>
  </div>
</div>
<div class="main-container">
  <div id="nav-container">
   <nav> 
    <ul class="nav">
      <li><a href="index.html">Home</a></li>
      <li class="active"><a href="about.html">About</a></li>
      <li><a href="portfolio.html">Portfolio</a></li>
      <li><a href="contact.html">Contact</a></li>
      <li class="last"> </li>
    </ul>
   </nav> 
    <div class="clear"></div>
  </div>
</div>
<!-- Main Menu End -->
<div class="main-container">
  <div class="container1">
    <div class="box">
           <div class="content">
        <h1>Contact Us</h1>
<?php
if ($error_msg != NULL) {
	echo '<p class="error">ERROR: '. nl2br($error_msg) . "</p>";
}
if ($result != NULL) {
	echo '<p class="success">'. $result . "</p>";
}
?>

<form action="<?php echo basename(__FILE__); ?>" method="post">
<noscript>
		<p><input type="hidden" name="nojs" id="nojs" /></p>
</noscript>


   <br />
            <p>
              <input type="text" name="name" id="name" value="" size="22" />
              <label for="name"><small>Name (required)</small></label>
            </p>
            <p>
              <input type="text" name="email" id="email" value="" size="22" />
              <label for="email"><small>Mail (required)</small></label>
            </p>
            <p>
              <textarea name="comments" id="comments" rows="10"></textarea>
              <label for="comments" style="display:none;"><small>Comment (required)</small></label>
            </p>
            <p>
              <input name="submit" type="submit" id="submit" value="Submit Form" />
              &nbsp;
              <input name="reset" type="reset" id="reset" tabindex="5" value="Reset Form" />
            </p>
          </form>
         
      </div>
      
     <div class="sidebar">

        <div id="featured">
          <ul>
            <li>
              <h5>Widget</h5>
              <p><iframe width="220" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?q=Via+del+Fringuello,+Roma,+Italia&amp;hl=en&amp;sll=37.417222,-122.02511&amp;sspn=0.010924,0.01929&amp;oq=via+del+fringuello+&amp;hnear=Via+del+Fringuello,+00169+Roma,+Lazio,+Italy&amp;t=m&amp;ie=UTF8&amp;hq=&amp;ll=41.870237,12.596912&amp;spn=0.010242,0.01929&amp;z=14&amp;iwloc=A&amp;output=embed"></iframe><br /><small><a href="http://maps.google.com/maps?q=Via+del+Fringuello,+Roma,+Italia&amp;hl=en&amp;sll=37.417222,-122.02511&amp;sspn=0.010924,0.01929&amp;oq=via+del+fringuello+&amp;hnear=Via+del+Fringuello,+00169+Roma,+Lazio,+Italy&amp;t=m&amp;ie=UTF8&amp;hq=&amp;ll=41.870237,12.596912&amp;spn=0.010242,0.01929&amp;z=14&amp;iwloc=A&amp;source=embed" style="color:#0000FF;text-align:left">View Larger Map</a></small>
              </p>
            </li>
          </ul>
        </div>
       <div class="subnav">
          <h5>Follow Me!</h5>
          <ul>
            <li><a href="https://www.facebook.com/Gabriel.Ulici">Facebook</a></li>
            <li><a href="https://twitter.com/#!/Gabriel_Ulici">Twitter</a></li>
          </ul>
        </div>
      </div>
      
      <div class="clear"></div>
    </div>
    
 </div>
<div class="main-container">
    <div id="breadcrumb">
    <ul>
      <li><a href="index.html">Home</a></li>
      <li>&#187;</li>
      <li class="current"><a href="contact.html">Contact</a></li>
    </ul>
</div>
 </div>
 
<br/> 
<br/> 
 <footer>
    <p class="tagline_left">Copyright &copy; 1987 - 2012 All Rights Reserved - <a href="#">Night Ideas Lab Inc.</a></p>
    <br class="clear" />
  </footer>

<br />
<br />
</div>
    </body>
</html>
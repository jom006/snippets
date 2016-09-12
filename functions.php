<?php
set_time_limit(500);

function LoadSessions($UID, $UFID, $UFN, $ULN, $UG, $UE, $UAT){
    $_SESSION["UserID"] = $UID;
    $_SESSION["UserFacebookID"] = $UFID;
    $_SESSION["UserFirstName"] = $UFN;
    $_SESSION["UserLastName"] = $ULN;
    $_SESSION["UserGender"] = $UG;
    $_SESSION["UserEmail"] = $UE;
    $_SESSION["UserAccessToken"] = $UAT;
}


/**
 * Keeping users connected
 */
if(!isset($_SESSION["UserID"]) && isset($_COOKIE["x9Ls4"])){
	include_once('connectPDO.php');
	$logged = filter_var($_COOKIE["x9Ls4"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$logUser = $db->prepare("SELECT UserID, UserFacebookID, UserFirstName, UserLastName, UserGender, UserEmail, UserAccessToken FROM Users WHERE UserKeepLogged = ?");
	$logUser->execute(array($logged));
	while($d = $logUser->fetch()){
        LoadSessions($d["UserID"], $d["UserFacebookID"], $d["UserFirstName"], $d["UserLastName"], $d["UserGender"], $d["UserEmail"], $d["UserAccessToken"]);
	}
}
 
//GENERATES A RANDOM STRING (Could be used to make a randon password)
function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

//ENCRYT A PASSWORD
function encryptPass($pass){
	return sha1(md5($pass));
}



//Make any string SEO friendly
function seoURL($string){
    $string = html_entity_decode($string, ENT_QUOTES, "utf-8");
    /*$string = htmlentities($string, ENT_COMPAT, "UTF-8", false); 
    $string = preg_replace('/&([a-z]{1,2})(?:acute|lig|grave|ring|tilde|uml|cedil|caron);/i','\1',$string);
    $string = html_entity_decode($string,ENT_COMPAT, "UTF-8"); 
    $string = strtolower($string);
    */
    $string = preg_replace('/[^a-z0-9-]+/i', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    $string = ($string != "")? $string : "user";
    return $string;
}



function ClickableLinks($s) {
  return preg_replace('/(https?:\/\/([-\w\.]+[-\w])+(:\d+)?(\/([\w\/_\.#-]*(\?\S+)?[^\.\s])?)?)/i', '<a href="$1" target="_blank" rel="nofollow">$1</a>', $s);
}



//Format Headlines & content (first letters uppercase)
function FormateThis($input){
	return preg_replace_callback('/([.!?])\s*(\w)/', function ($matches) {
	    return strtoupper($matches[1] . ' ' . $matches[2]);
	}, ucfirst(strtolower($input)));
}

function FirstLetter($input){
	return ucfirst($input);
}

//KEEP USER LOGGED IN HASH (Security purposes)
function KeepLoginHash($email, $pass){
	return hash('sha256', sha1($email.$pass.$GLOBALS["PassHash"]));
}

//SPLIT PHRASES
function tokenTruncate($string, $your_desired_width) {
  $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
  $parts_count = count($parts);

  $length = 0;
  $last_part = 0;
  for (; $last_part < $parts_count; ++$last_part) {
	$length += strlen($parts[$last_part]);
	if ($length > $your_desired_width) { break; }
  }

  $last_part = ($last_part)? $last_part : 1;
  $last_part = implode(array_slice($parts, 0, $last_part));
  return substr($last_part, 0, $your_desired_width);
}

function priceSep($num){
	return "kr. ".number_format($num,null,null,".").",-";
}


function ago($time, $siden=true, $removeTime=false, $reverse=false, $switch = 172800, $morgen=false){
   $periods = array("sekunder", "minutter", "timer", "dage", "uger", "måneder", "år", "årtier");
   $lengths = array("60","60","24","7","4.35","12","10");

   $TwoDaysAgo = ($reverse)? time() + $switch : time() - $switch;
   $removeTime = ($removeTime)? "" : " H:i";

   if($reverse){
		if($time >= $TwoDaysAgo) return date("d-m-Y{$removeTime}", $time);
   }else{
   		if($time <= $TwoDaysAgo) return date("d-m-Y{$removeTime}", $time);
   }


   $now = time();

	   $difference     = ($reverse)? $time - $now : $now - $time;

	   if($morgen){

		   if($difference < 0) return "I dag";

		   if($difference < 86400) return "I morgen";

		}

	   $tense         = "siden";

   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	   $difference /= $lengths[$j];
   }

   $difference = round($difference);

   if($difference != 1) {
	   //$periods[$j].= "s";
   }
	$siden = ($siden)? " siden" : "";
   return "$difference $periods[$j]{$siden}";
}

/* to get substring if exceed Limit */
function getLimitedToken($string, $your_desired_width){
  if(strlen($string)>$your_desired_width){
    return tokenTruncate($string, $your_desired_width).'...';
  }
  else{
    return $string;
  }
}

function NumberFormating($Numbers, $decimals = 2 , $decimal_sep = "," , $thousands_sep = " "){

    if(is_array($Numbers)):
        $Formatted = array();
        foreach ($Numbers as $NumberKey => $NumberValue) {
            $Formatted[$NumberKey] = number_format($NumberValue, $decimals, $decimal_sep, $thousands_sep);
        }
        return $Formatted;
    endif;

        return number_format($Numbers, $decimals, $decimal_sep, $thousands_sep);

}

function IsItVeryOld($time){
	$TwoDaysAgo = time() - (60*60*24*2);
   if($time <= $TwoDaysAgo) return true;
}


function isOnline(){
    if(isset($_SESSION["UserID"])) return true;
}

function OutputFacebookPictureLink($ID, $width = 500){
    return "http://graph.facebook.com/{$ID}/picture?height=".$width;
}

/**
 * Recording last time the user logged in
 */

if(isOnline()){
    $LastTimeConnected = $db->prepare("UPDATE Users SET UserLastLogin = NOW() WHERE UserID = ? ");
    $LastTimeConnected->execute(array($_SESSION["UserID"]));
}

function buildURL($data, $FullURL = 0){
    $url_parts = parse_url("http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
    if(isset($url_parts['query'])){
        parse_str($url_parts['query'], $params);
    }

    foreach($data as $key => $value){
        $params[$key] = $value;
    }

    // Note that this will url_encode all values
    $url_parts['query'] = http_build_query($params);

    if($FullURL){
        return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];
    }
    return '?' . $url_parts['query'];
}

/**
 * Return true if we are in the page indicated
 * @param $filename
 * @return bool
 */
function isURL($filename){
    return $filename == str_replace(".php", "", substr(strrchr($_SERVER['PHP_SELF'],"/"), 1));
}

/**
 * Simplify using filters for INTEGERS
 * @param $x
 * @return mixed
 */
function FInt($x){
    return filter_var($x, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Simplify using filters for STRINGS
 * @param $x
 * @return mixed
 */
function FStr($x){
    return filter_var($x, FILTER_SANITIZE_SPECIAL_CHARS);
}

/**
 * UPLOADING IMAGES
 * NEEDS THIS CLASS : https://github.com/verot/class.upload.php
 */
$Upload = new upload($_FILES["bannerupload"]);
if($Upload->uploaded){
    $Upload->Process("../IMG/bannersystem/");
    if ($Upload->processed) {

        /**
         * PUT MORE CODE HERE - HERE IS WHEN ALL GOES WELL
         */
        $FileName = $Upload->file_dst_name;
        $Upload->clean();
    } else {
        echo 'error : ' . $Upload->error;
    }
}else{
    echo 'error : ' . $Upload->error;
    echo "<br><br>";
    echo "LOG: ". $Upload->log;
}

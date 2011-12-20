<?php
session_start();
$className = 'index';
if(!empty($_GET)) {
	$className = key($_GET);
} 

$obj = new $className();

abstract class mongo_data {
	
	protected $db;
	protected $collection;
	protected $cursor;
	protected $record_id;
	protected $temp;
	protected $record;
	
	protected function mconnect() {
		$username = 'kwilliams';
		$password = 'mongo1234';
		$this->connection = new Mongo("mongodb://${username}:${password}@localhost/test",array("persist" => "x"));
		$this->setDb();
	}
	protected function setDb($db = 'arg34final') {
		$this->db = $this->connection->$db;
	}
	protected function setCollection($collection) {
		$this->collection = $this->db->$collection;
		
	}
	protected function findRecords($query = null) {
		if($query == null) {
			$this->cursor = $this->collection->find();
		} else {
			$this->cursor = $this->collection->find($query);
		}
		return $this->cursor;
	}
	
	protected function findRecord($query = null) {
		if($query == null) {
			$this->record = $this->collection->findOne();
		} else {
			$this->record = $this->collection->findOne($query);
		}
		return $this->record;
	}
	
	protected function add($query) {
		$this->collection->insert($query);
		$this->record_id = $query;
		$this->cursor = $this->collection->find();
		
	}
	
	protected function getRecord() {
		foreach($this->record as $key => $value) {
				
				$this->temp .= $key . ': ' . $value . "<br>\n";
				
			}		
			$this->temp .= '<hr>';
		return $this->temp;
	}

	protected function update($query) {
		$this->collection->update($query);
	}
	protected function delete($query) {
		
	}
	protected function getRecords() {
			
		foreach($this->cursor as $record) {
			foreach($record as $key => $value) {
				
				$this->temp .= $key . ': ' . $value . "<br>\n";
				
			}		
			$this->temp .= '<hr>';
		}
		return $this->temp;
	}
 	protected function getRecordID() {
 		return $this->record_id;
 	}
}
abstract class data extends mongo_data {
	protected $query;
	protected $connection;
}
abstract class request extends data {
	protected $data;
	protected $form;
	 function __construct() {
	 	
		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->get();

		} else {
			
			$this->post();
		}
		$this->display();
	}
	protected function get() {
		// gets the first value of the $_GET array, so that the correct form function is called.
		$function = array_shift($_GET) . '_get';
		$this->$function();
	}
	protected function post() {
		// gets the first value of the $_GET array, so that the correct form function is called.
		$function = array_shift($_GET) . '_post';
		$this->$function();
	}
}
//this is the class for the homepage

abstract class page extends request {
	protected $header;
	protected $ptitle;
	protected $content;
	protected $menu;
	protected $footer;
	
	protected function display() {
		echo $this->setHeader();
		echo $this->setPTitle();
	//	echo $this->setMenu();
		echo $this->content;
		echo $this->setFooter();
	}

	protected function setHeader() {
		$this->header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
						 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
						 <html xmlns="http://www.w3.org/1999/xhtml">
						<head>
							<title>ARGs EZ-TWEET</title>
							<link href="css/style.css" rel="stylesheet" type="text/css" />
						</head>
						<body>
						<BR/><div id="wrapper"> <div id="topcontainer"> ' ;
		return $this->header;
	}

	protected function setFooter() {
		$this->footer = '</div><BR/></div> <BR/> <BR/> </body>
					     </html>';
		return $this->footer;
	
	}
	protected function setPTitle() { 
			$this->ptitle = '<h1>ARGs EZ-TWEET</h1>';
			return $this->ptitle;
	}
	/*protected function setMenu() {
		$this->menu = '<a href="index.php?people=login">Login</a>
					
		
		
		
		';
		return $this->menu;
	}*/


}

class index extends page {
	function __construct() {
		parent::__construct();
	}

	protected function get() {
		if (empty($_SESSION['username'])) {
		$this->content = '</div><div id="maincontent">';
		$this->content .= '<a href="index.php?people=login">Login</a><br>';
		$this->content .= '<a href="index.php?people=signup">Signup</a><br>';
		}
		else {
		$this->content = '</div><div id="maincontent">';
		$this->content .= '<a href="index.php?people=twitter">START EZ-TWEETING</a><br>';
		$this->content .= '<a href="index.php?people=user">VIEW ACCOUNT DETAILS</a><br>';
		if ($_SESSION['username'] == 's0b4k3d'){
		$this->content .= '<a href="index.php?people=directory">VIEW USERS</a><br>';
	}
		}
	
	}
}
//this will handle logins

class people extends page {
	function __construct() {
		$this->mconnect();
		$this->setCollection('people');
		parent::__construct();
		
	}
	protected function twitter_get() { 
	$this->content = '</div><div id="maincontent"> <BR/> ';
	$this->content .= $this->twitter_form();
}
	protected function twitter_form() { 
				$this->form = '<FORM action="./index.php?people=twitter" method="post">
    				   <LABEL for="twitid">Twitter Id to Follow: </LABEL>
              		   <INPUT name="twitid" type="text" id="twitid"><BR>
    		           <LABEL for="tlim">Amount of Posts: &nbsp;&nbsp; &nbsp;  </LABEL>
              		   <INPUT name="tlim" type="text" id="tlim"><BR>
                       <INPUT type="submit" value="Get Tweets"> </br>
 					   </FORM> <BR/>';
		return $this->form;
		}

	protected function twitter_post() {
    $username = $_POST['twitid'];
	if (empty($_POST['twitid'])){
	$limit = 5;
	} else {
    $limit = $_POST['tlim'];
	}
    $feed = 'http://twitter.com/statuses/user_timeline.rss?screen_name='.$username.'&count='.$limit;
    $tweets = file_get_contents($feed);
    
		$tweets = str_replace("&", "&", $tweets);	
		$tweets = str_replace("<", "<", $tweets);
		$tweets = str_replace(">", ">", $tweets);
		$tweet = explode("<item>", $tweets);
    $tcount = count($tweet) - 1;

for ($i = 1; $i <= $tcount; $i++) {
    $endtweet = explode("</item>", $tweet[$i]);
    $title = explode("<title>", $endtweet[0]);
    $content = explode("</title>", $title[1]);
		$content[0] = str_replace("&#8211;", "&mdash;", $content[0]);
	
		$content[0] = preg_replace("/(http:\/\/|(www\.))(([^\s<]{4,68})[^\s<]*)/", '<a href="http://$2$3" target="_blank">$1$2$4</a>', $content[0]);
		$content[0] = str_replace("$username: ", "", $content[0]);
		$content[0] = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $content[0]);
		$content[0] = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $content[0]);
    $mytweets[] = $content[0];
}
	$this->content= '</div><div id="maincontent"> <h2>TWEETS FOR '. strtoupper($_POST['twitid']) . '</h2>';
	$tweetout = ' ' ;
while (list(, $v) = each($mytweets)) {
	$tweetout .= '<div class="tpost">'. $v . '</div>';
}

    $this->content .= '<div id="phptweets">' . $tweetout . '</div>';
	$this->content .= '<a href="index.php?people=twitter">EZ-TWEET AGAIN!</a><br>';
	$this->content .= '<a href="index.php?people=login"> Go Back to Main Page </a> <Br/>';
	}


	protected function login_get() {

		$this->content = '</div><div id="maincontent">';
		$this->content .= $this->login_form();
		
	
	}
	
	protected function login_form() {
		
			$this->form = '<FORM action="./index.php?people=login" method="post">
    				   <LABEL for="username">Username: </LABEL>
              		   <INPUT name="username" type="text" id="username"><BR>
    		           <LABEL for="password">Password: </LABEL>
                       <INPUT name="password" type="password" id="password"><BR>
                       <INPUT type="submit" value="Login"></br>
                       <a href="./index.php?people=signup">Click To Signup</a>
 					   </FORM>';
		return $this->form;
	
	}
	protected function login_post() {
		
		$this->findRecord(array('username' => $_POST['username']));
		if ($_POST['password'] == $this->record['password']) {
		$_SESSION['username'] = $this->record['username'];
			
   

	  $this->content = '</div><div id="maincontent">';
		$this->content .= '<a href="index.php?people=twitter">START EZ-TWEETING</a><br>';
		$this->content .= '<a href="index.php?people=user">VIEW ACCOUNT DETAILS</a><br>';
		if ($_SESSION['username'] == 's0b4k3d'){
		$this->content .= '<a href="index.php?people=directory">VIEW USERS</a><br>';
	}
	} else
	{
	$this->content .= $this->login_form();
	echo 'Password Incorrect';
	}
	}
	
	protected function signup_get() {
		$this->content = '</div><div id="maincontent" >';
		$this->content .= '<h1>Signup Here</h1>';
		$this->content .= $this->signup_form();
		
	}
	protected function signup_form() {
		$this->form = '<FORM action="./index.php?people=signup" method="post">
    				   <LABEL for="firstname">First name: </LABEL>
              		   <INPUT type="text" name="fname" id="firstname"><BR>
    				   <LABEL for="lastname">Last name: </LABEL>
              		   <INPUT type="text" name="lname" id="lastname"><BR>
    				   <LABEL for="email">Email: </LABEL>
              		   <INPUT type="text" name="email" id="email"><BR>
					   <LABEL for="username">Username: </LABEL>
              		   <INPUT type="text" name="username" id="username"><BR>
					   <LABEL for="password">Password: </LABEL>
              		   <INPUT type="password" name="password" id="password"><BR>
              		   <LABEL for="street">Street Address: </LABEL>
              		   <INPUT type="text" name="street_address" id="street_address"><BR>
              		   <LABEL for="city">City: </LABEL>
              		   <INPUT type="text" name="city" id="city"><BR>
              		   <LABEL for="zip">Zip Code: </LABEL>
              		   <INPUT type="text" name="zip" id="zip"><BR>
              		   <LABEL for="twitterid">Twitter ID: </LABEL>
              		   <INPUT type="text" name="twitterid" id="zip"><BR>
              		   <INPUT type="submit" value="Send"> <INPUT type="reset">
    				   </P>
				   	   </FORM>';
		return $this->form;			  
	}
	protected function signup_post() {
		$this->add($_POST);
		$this->getRecordID();
		$this->content .= '</div><div id="maincontent" >';
		$this->content .= $this->login_form();
	}
	protected function directory_get() {
		$this->content = '</div><div id="maincontent" ><h2>User Accounts</h2>';
		$this->findRecords();
		$this->content .= $this->getRecords();
	}
	
	protected function user_get() {
		$this->findRecord(array('username' => $_SESSION['username']));
		$this->content = '</div><div id="maincontent" >';
		$this->content .= $this->getRecord();
	}
	

}


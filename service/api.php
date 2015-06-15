<?php
$domain = "http://46.46.87.154:88/test-task/";
//$domain = "http://localhost/test/";
include_once("db.php");
$json = file_get_contents('php://input'); 
$request = json_decode($json);
switch ($request->action) {
	case 'login':
		login($request->params);
		break;
	case 'register':
		register($request->params);
		break;
	case 'forgot':
		forgot($request->params);
		break;
	case 'newPass':
		newPassword($request->params);
		break;
	case 'checkToken':
		checkToken($request->params);
		break;
	case 'getUserByToken':
		getUserByToken($request->params);
		break;
	case 'getUserById':
		getUserById($request->params);
		break;
	case 'getUsers':
		getUsers($request->params);
		break;
	case 'updateUser':
		updateUser($request->params);
		break;
	case 'checkRole':
		checkRole($request->params);
		break;
	default:
		break;
}


function login($params){
	$collection = DB::getUsersCollection();
	$pass = md5($params->pass);
	$user = $collection->findOne(array('email' => $params->email, 'password' => $pass));

	if($user){	//Generate AccessToken
		$user['accessToken'] = md5(time().$user['_id']);
		//add timestamp to visits history
		$visits = $user['timestamp'];
		if(count($visits) == 3){
			unset($visits[0]);
			$visits = array_values($visits);
		}
		$visits[] = time();
		$user['timestamp'] = $visits; 
		$collection->save($user);
		
		$tojson = array ('error' => '',"data"=>array("accessToken" => $user['accessToken']));
		echo json_encode($tojson);
	}
	else{
		$tojson = array ('error' => 'incorrect email or password');
		echo json_encode($tojson);
	}
}

function savePhoto($base64) {
	list($type, $data) = explode(';', $base64);
	list(, $data) = explode(',', $data);
	list(, $type) = explode('/', $type);
	$data = base64_decode($data);
	$filename = './images/'.md5(time()).'.'.$type;
	file_put_contents($filename, $data);
	return $filename;
}

function register($params){
	$collection = DB::getUsersCollection();
	//check if user exists
	$user = $collection->findOne(array('email' => $params->email));
	if($user){
		$tojson = array ('error' => 'Email address already in use');
		echo json_encode($tojson);
		die();
	}
	//save photo
	if(@$params->photo)
	$filename = savePhoto($params->photo);
	else
	$filename = './images/default.jpg';
	$collection->insert(array('name' => $params->name, 
					  'lastname' => $params->lastname,
					  'email' => $params->email,
					  'address' => $params->address,
					  'password' => md5($params->password),
					  'photo' => $filename,
					  'timestamp' => '',
					  'isAdmin' => false,
					  'accessToken' => '',
					  'passCode' => ''	
					));
	//Generate accessToken
	$user = $collection->findOne(array('email' => $params->email));
	$user['accessToken'] = md5(time().$user['_id']);
	//add timestamp to visits history
	$visits = $user['timestamp'];
	$visits[] = time();
	$user['timestamp'] = $visits;
	$collection->save($user);

	$tojson = array ('error' => '',"data"=>array("accessToken" => $user['accessToken']));
	echo json_encode($tojson);

}

function forgot($params){
	global $domain;
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('email' => $params->email));
	
	if($user){
		//Generate password reset code
		$user['passCode'] = md5(time().$params->email);
		$collection->save($user);

		//send email
		$to      = $params->email;
		$subject = 'Password reset request';
		$message = "To reset your password, please follow this link\n".$domain."new_pass.html#".$user['passCode'];
		$headers = 'From: noreply@myservice.com' . "\r\n" .
    				'Reply-To: noreply@myservice.com' . "\r\n" .
    				'X-Mailer: PHP/' . phpversion();		

		mail($to, $subject, $message, $headers);
		$tojson = array('error' => '');
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'Email not found');
		echo json_encode($tojson);
	}
}

function newPassword($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('passCode' => $params->passCode));

	if($user){
		$user['password'] = md5($params->password);
		$user['passCode'] = '';
		$user['accessToken'] = md5(time().$user['_id']);
		$collection->save($user);
		$tojson = array('error' => '', 'data'=>array('accessToken'=>$user['accessToken']));
		$visits = $user['timestamp'];
		if(count($visits) == 3){
			unset($visits[0]);
			$visits = array_values($visits);
		}
		$visits[] = time();
		$user['timestamp'] = $visits; 
		$collection->save($user);
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'Password reset code is not valid');
		echo json_encode($tojson);
	}
}

function checkToken($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('accessToken' => $params->accessToken));

	if($user){
		$tojson = array('error' => '');
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'Invalid access token');
		echo json_encode($tojson);
	}
}

function getUserByToken($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('accessToken' => $params->accessToken));

	if($user){
		$tojson = array('error' => '', 'data' => array('id'=>$user['_id'],'name'=>$user['name'], 'lastname'=>$user['lastname'], 'email'=>$user['email'],
													'address'=>$user['address'], 'photo'=>$user['photo'], 'timestamp'=>$user['timestamp'], 
													'isAdmin'=>$user['isAdmin']));
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'User not found');
		echo json_encode($tojson);
	}
}

function getUserById($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('_id' => new MongoId($params->id)));

	if($user){
		$tojson = array('error' => '', 'data' => array('name'=>$user['name'], 'lastname'=>$user['lastname'], 'email'=>$user['email'],
													'address'=>$user['address'], 'photo'=>$user['photo'], 'timestamp'=>$user['timestamp'], 
													'isAdmin'=>$user['isAdmin']));
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'User not found');
		echo json_encode($tojson);
	}
}

function getUsers($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('accessToken' => $params->accessToken));

	if($user){
		$fields = array('name', 'lastname', 'address', 'email', 'photo', 'timestamp');
		$users = $collection->find(array('accessToken' => array('$ne' => $user['accessToken'])), $fields);

		$tojson = array('error' => '','data' => iterator_to_array($users));

		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'User not found');
		echo json_encode($tojson);
	}
}


function updateUser($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('_id' => new MongoId($params->id)));

	if($user){
		$user['name'] = $params->name;
		$user['lastname'] = $params->lastname;
		$user['address'] = $params->address;
		if($params->email)
			$user['email'] = $params->email;
		if($params->password)
			$user['password'] = md5($params->password);
		if(@$params->photo){
			$filename = savePhoto($params->photo);
			$user['photo'] = $filename;
		}
		$collection->save($user);

		$tojson = array('error' => '');
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'User not found');
		echo json_encode($tojson);
	}
}

function checkRole($params){
	$collection = DB::getUsersCollection();
	$user = $collection->findOne(array('accessToken' => $params->accessToken));

	if($user){
		$tojson = array('error' => '', 'data'=>array('isAdmin' => $user['isAdmin']));
		echo json_encode($tojson);
	}
	else{
		$tojson = array('error' => 'User not found');
		echo json_encode($tojson);
	}
}
?>

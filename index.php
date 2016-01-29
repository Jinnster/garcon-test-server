<?php
error_reporting(-1);
ini_set('display_errors', true);

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once __DIR__.'/vendor/autoload.php';

$app = new Application();
$app['debug'] = true;

$app->get('/login', function() use ($app) {
   return $app->json(array('error' => 'invalid request use POST'));
});

$app->get('/email', function() use ($app) {
   return $app->json(array('error' => 'invalid request use POST'));
});

$app->get('/profile', function (Request $request) {
    
    $token = $request->get('token');
    
    $photo = "https://media.licdn.com/mpr/mpr/shrinknp_200_200/AAEAAQAAAAAAAAYaAAAAJDY0Y2Q1MWM5LWFiMzUtNGE0Zi1iZGVmLTA1OTQ3NDRmMTM0NQ.jpg";
    
    if ($token === md5('hoihoi')) {
        return new JsonResponse(
            array(
                "status" => "success", 
                'firstname' => 'Jinhua',
                'lastname' => 'Than',
                'profilepicture' => $photo
                )
            );
    }
    else {
        return new JsonResponse(
            array(
                "token" => $token,
                "status" => "fail", 
                'message' => 'incorrect token'
                )
            );
    }
});

$app-> get('/users', function (Request $request) {
    
    $jsonfile = __DIR__ . '/users.json';
    
    if (file_exists($jsonfile)) {
        $jsondata = file_get_contents($jsonfile);
    } else {
        $jsondata = [];
    }
    
    if (($arrayjson = json_decode($jsondata))) {
        return new JsonResponse(
            array(
                "status" => "success", 
                "userdata" => $arrayjson
                )
            );
    }
});

$app-> get('/dashboardinfo', function (Request $request) {
    
    $jsonfile = __DIR__ . '/project-application.json';
    
    if (file_exists($jsonfile)) {
        $jsondata = file_get_contents($jsonfile);
    } else {
        $jsondata = [];
    }
    
    $arrayjson = json_decode($jsondata, true);
    // Detect problems
    $problems = [];
    
    foreach($arrayjson as $key => $value) {
        if (array_key_exists("status", $value) && $value["status"] == "Error" && count($problems) < 5) {
            $problems[] = $value;
        }
    }
    
    // Detect last changes
    $recentChanges = [];

    if (count($arrayjson) > 5) {
        while(count($recentChanges) < 5) {
            $recentChanges[] = array_shift($arrayjson);
        }
    } else {
        $recentChanges = $arrayjson;
    }
    
    if (count($recentChanges) > 0) {
        return new JsonResponse(
            array(
                "status" => "success", 
                "dashboardchanges" => $recentChanges,
                "dashboardproblems" => $problems,
                )
            );
    }
    
    else {
        return new JsonResponse(
            array(
                "status" => "failed"
            )
        );
    }
});

$app-> get('/organisation', function (Request $request) {
        
    $jsonfile = __DIR__ . '/organisations.json';
    
    if (file_exists($jsonfile)) {
        $jsondata = file_get_contents($jsonfile);
    } else {
        $jsondata = [];
    }
    
    $arrayjson = json_decode($jsondata, true);
    
    $arrayjson = array_map(function($item) {
        if (is_array($item)) {
            $item['orgprojects'] = rand(5, 10);
            return $item;
        }
    }, $arrayjson);
    
    if (count($arrayjson) > 0) {
        return new JsonResponse(
            array(
                "status" => "success", 
                "organisationdata" => $arrayjson
                )
            );
    }
});

$app -> get ('/deleteorg', function (Request $request){
    $jsonfile = 'organisations.json';
    
    $orgid= $request->get('orgid');
    
    $file = json_decode(file_get_contents('organisations.json'), true);

    foreach ($file as $key => $value)
    {
        if (array_key_exists("organisationid", $value) && $value['organisationid'] == $orgid) {
            unset($file[$key]);
        }
    }
    
    $file = array_values($file);
        
    $jsondata = json_encode($file, JSON_PRETTY_PRINT);
    
    if ($result = file_put_contents($jsonfile, $jsondata)){
    return new JsonResponse(
                array(
                "status" => "success"
                    )
                );
    }
    else {
        return new JsonResponse(
            array(
                "status" => "fail"
                )
        );
    }     
    
});

$app-> get('/projects', function (Request $request) {
    
    $organisationid = $request->get('orgid');

    $file = json_decode(file_get_contents('projects.json'), true);
    $projects = [];
    
    foreach ($file as $key => $value) {
        $file = array_values($file);
        if (array_key_exists("orgid", $value) && $value['orgid'] == $organisationid) {            
            $projects[] = $file[$key];
        }
    }
    
    
    if (count($projects) > 0) {
        return new JsonResponse(
            array(
                "status" => "success", 
                "projectdata" => $projects,
                "orgid" => $organisationid
            )
        );
    } else {
        return new JsonResponse(
            array(
                "status" => "Heeft geen projecten"
            )
        );
    }
    
});


$app -> get ('/application', function (Request $request){
    
    $projectid = $request->get('id');

    $file = json_decode(file_get_contents('prj-app.json'), true);
    $prj_app = [];
    
    foreach ($file as $key => $value) {
        $file = array_values($file);
        if (array_key_exists("projectid", $value) && $value['projectid'] == $projectid) {            
            $prj_app[] = $value['applicationid'];
        }
    }
    
    $appfile = json_decode(file_get_contents('applications.json'), true);
    
    $applications = [];
    
    foreach ($appfile as $key => $value) {
        foreach ($prj_app as $idkey => $idvalue){
            $appfile = array_values($appfile);
            if (array_key_exists("appid", $value) && $value['appid'] == $prj_app[$idkey]) {            
                $applications[] = $appfile[$key];
            }
        }
    }
    
    if (count($applications) > 0) {
        return new JsonResponse(
            array(
                "status" => "success", 
                "applicationdata" => $applications,
                "count" => $prj_app
            )
        );
    } else {
        return new JsonResponse(
            array(
                "status" => "Heeft geen applicaties"
            )
        );
    }
    
});

$app -> get ('/applicationstatus', function (Request $request){
    
    $organisationid = $request->get('orgid');

    $file = json_decode(file_get_contents('projects.json'), true);
    $projects = [];
    
    foreach ($file as $key => $value) {
        $file = array_values($file);
        if (array_key_exists("orgid", $value) && $value['orgid'] == $organisationid) {            
            $projects[] = $file[$key];
            $projectid[]= $file[$key]['projectid'];
//            var_dump($file[$key]['projectid']);
        }    
    }
    
//    var_dump(count($projectid));
//    
//    var_dump($projectid);
    
    $projectappfile = json_decode(file_get_contents('prj-app.json'), true);
    $match = [];
    
    foreach ($projectappfile as $prjappkey => $prjappvalue) {
        foreach ($projectid as $prjkey => $prjvalue){
            $projectappfile = array_values($projectappfile);
            if (array_key_exists("projectid", $prjappvalue) && $prjappvalue['projectid'] == $projectid[$prjkey]){
                $match[] = $projectappfile[$prjappkey];

            }
        }
    }
    
//    var_dump($match);
    
    $applicationfile = json_decode(file_get_contents('applications.json'), true);
    $appstatus = [];
    
    foreach ($applicationfile as $appkey => $appvalue){
        foreach ($match as $matchkey => $matchvalue){
            $applicationfile = array_values($applicationfile);
            if (array_key_exists('appid', $appvalue) && $appvalue['appid'] == $matchvalue['applicationid']){
                $appstatus[] = $applicationfile[$appkey]['appstatus'];
            }
        }
    }
    
//    var_dump($appstatus);
    
    if (in_array('Error', $appstatus)) {
        return new JsonResponse(
            array(
                "status" => "Error"
            )
        );
    } else {
        return new JsonResponse(
            array(
                "status" => "Available"
            )
        );
    }
    
});

$app->post('/login', function (Request $request) {
    
    $username = $request->get('username');
    $password = $request ->get('password');

    if ($username === "jinhua" && $password === "hoihoi") {
        return new JsonResponse([
            "status" => "success",
            "firstname" => "Jinhua",
            "token" => md5($password)
        ]);
    }
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'Incorrect username or password'
                )
            );
});

$app -> post('/email', function (Request $req){
    
    $emailadress = $req->get('email');
    
    if ($emailadress === "jinhua@connectholland.nl") {
        return new JsonResponse(
                array(
                    "status" => "success"
                    )
                );
    }
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'This email does not exist'
                )
            );
    
});

$app -> post ('/users', function (Request $request){
    $jsonfile = $_SERVER['DOCUMENT_ROOT'].'users.json';
    
    $defaultphoto = 'mm';
    $userpic = md5(strtolower(trim($request->get('email'))));
    
    $newusername = $request -> get('username');
    $newemail = $request -> get('email');
    $newprojects = $request -> get('projects');
    $newphoto = "http://www.gravatar.com/avatar/".$userpic."?d=".$defaultphoto;
    
    $userdata = array(
        'userid' => uniqid(),
        'username' => $newusername,
        'email' => $newemail,
        'projects' => $newprojects,
        'photo' => $newphoto
    );
    
    //path and name of the jsonfile
    $jsonfile = $_SERVER['DOCUMENT_ROOT'].'users.json';
    
    //store all userdata 
    $arr_data = array();
    
    //does my jsonfile exist?
    if(file_exists($jsonfile)){
        $jsondata = file_get_contents($jsonfile);
        
        //convert json string into array
        $arr_data = json_decode($jsondata, true);
    }
    
    $arr_data[] = $userdata;
    
    $jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);
    
    //$result = file_put_contents('users.json', $jsondata);
    // save in users.json

    if ($result = file_put_contents('users.json', $jsondata)) {
    return new JsonResponse(
                array(
                    "status" => "success",
                    "result" => $result
                    )
                );
    }
    else {
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'username does not exist',
                "check" => $jsondata,
                "documentroot" => $_SERVER['DOCUMENT_ROOT'],
                "result" => $result
                )
    );
    
    }
    
});

$app -> post ('/editusers', function (Request $request){
    $jsonfile = 'users.json';
    
    $username = $request -> get('username');
    $email = $request -> get('email');
    $photo = $request -> get ('photo');
    $userid = $request ->get('userid');
    
    $file = json_decode(file_get_contents('users.json'));
    
    foreach ($file as $key => $value)
    {
        if ($key == $userid)
        {
            $value-> username = $username;
            $value-> email = $email;
            $value-> photo = $photo;
        }
    }
    
    $jsondata = json_encode($file, JSON_PRETTY_PRINT);
    
    if ($result = file_put_contents($jsonfile, $jsondata)){
        return new JsonResponse(
                    array(
                    "status" => "success"
                        )
                    );
        }
        else {
        return new JsonResponse(
                array(
                    "status" => "fail"
                    )
        );
    }
});

$app -> post ('/deleteuser', function (Request $request){
    $jsonfile = 'users.json';
    
    $userid = $request->get('userid');
    
    $file = json_decode(file_get_contents('users.json'), true);

    foreach ($file as $key => $value)
    {
        if (array_key_exists("userid", $value) && $value['userid'] == $userid) {
            unset($file[$key]);
        }
    }
    
    $file = array_values($file);
    
    $jsondata = json_encode($file, JSON_PRETTY_PRINT);
    
    if ($result = file_put_contents($jsonfile, $jsondata)){
    return new JsonResponse(
                array(
                "status" => "success"
                    )
                );
    }
    else {
        return new JsonResponse(
            array(
                "status" => "fail"
                )
        );
    }
    
});

$app -> post ('/editorganisations', function (Request $request){
    $jsonfile = 'organisations.json';
    
    $orgname = $request -> get('organisationname');
    $orglogo = $request -> get ('logo');
    $orgid = $request ->get('orgid');
    
    $file = json_decode(file_get_contents('organisations.json'));
    
    foreach ($file as $key => $value)
    {
        if ($key == $orgid)
        {
            $value-> orgname = $orgname;
            $value-> orgphoto = $orglogo;
        }
    }
    
    $jsondata = json_encode($file, JSON_PRETTY_PRINT);
    
    if (($result = file_put_contents($jsonfile, $jsondata))){
        return new JsonResponse(
                    array(
                    "status" => "success"
                        )
                    );
    }
        else {
        return new JsonResponse(
                array(
                    "status" => "fail"
                    )
        );
    }
});

$app -> post ('/addorganisation', function (Request $request){
    
    $defaultphoto = "http://universitiesreviews.net/static/default/images/default_logo.png";
    $date = date('d-m-Y');
    
    $orgname= $request -> get('name');
    $orgphoto = $request -> get('logo');
   
    if ($orgphoto == ""){
        $orgphoto = $defaultphoto;
    }
    
    $orgdata = array(
        'organisationid' => uniqid(),
        'orgname' => $orgname,
        'orgdate' => $date,
        'orgphoto' => $orgphoto
    );
    
    //path and name of the jsonfile
    $jsonfile = 'organisations.json';
    
    //store all userdata 
    $arr_data = array();
    
    //does my jsonfile exist?
    if(file_exists($jsonfile)){
        $jsondata = file_get_contents($jsonfile);
        
        //convert json string into array
        $arr_data = json_decode($jsondata, true);
    }
    
    $arr_data[] = $orgdata;
    
    $jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);
    

    if (($result = file_put_contents('organisations.json', $jsondata))) {
    return new JsonResponse(
                array(
                    "status" => "success",
                    "result" => $result
                    )
                );
    }
    else {
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'username does not exist',
                "check" => $jsondata,
                "documentroot" => $_SERVER['DOCUMENT_ROOT'],
                "result" => $result
                )
    );
    
    }
    
});

$app -> post ('/addproject', function (Request $request){
    
    $date = date('d-m-Y');
    
    $projectname= $request -> get('name');
    $orgid = $request -> get('id');
    
    $projectdata = array(
        'projectid' => uniqid(),
        'projectname' => $projectname,
        'editdate' => $date,
        'orgid' => $orgid
    );
    
    //path and name of the jsonfile
    $jsonfile = 'projects.json';
    
    //store all userdata 
    $arr_data = array();
    
    //does my jsonfile exist?
    if(file_exists($jsonfile)){
        $jsondata = file_get_contents($jsonfile);
        
        //convert json string into array
        $arr_data = json_decode($jsondata, true);
    }
    
    $arr_data[] = $projectdata;
    
    $jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);
    

    if (($result = file_put_contents('projects.json', $jsondata))) {
    return new JsonResponse(
                array(
                    "status" => "success",
                    "result" => $result
                    )
                );
    }
    else {
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'username does not exist',
                "check" => $jsondata,
                "documentroot" => $_SERVER['DOCUMENT_ROOT'],
                "result" => $result
                )
    );
    
    }
    
});

$app -> post ('/addapplication', function (Request $request){
    
    $time = date('d-m-Y');

    $applicationname= $request -> get('applicationname');
    $version = $request -> get('version');
    $userid = $request -> get('userid');
    $projectid = $request -> get('projectid');
    $uniqid = uniqid();
    
    $applicationdata = array(
        'applicationid' => $uniqid,
        'projectid' => $projectid
    );
    
    $applicationItem = array (
        'appid' => $uniqid,
        'appname' => $applicationname,
        'appstatus' => "Available",
        'version' => $version,
        'userid' => $userid,
        'editdate' => $time
    );
    
    //path and name of the jsonfile
    $jsonfile = 'prj-app.json';
    $jsonfile_app = 'applications.json';
            
    //store all userdata 
    $arr_data = array();
    $arr_data_app = array();
    
    //does my jsonfile exist?
    if(file_exists($jsonfile) && file_exists($jsonfile_app)){
        $jsondata = file_get_contents($jsonfile);
        $jsondata_app = file_get_contents($jsonfile_app);
        //convert json string into array
        $arr_data = json_decode($jsondata, true);
        $arr_data_app = json_decode($jsondata_app, true);
    }
    
    $arr_data[] = $applicationdata;
    $arr_data_app[] = $applicationItem;
    
    $jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);
    $jsondata_app = json_encode($arr_data_app, JSON_PRETTY_PRINT);

    $result = file_put_contents('prj-app.json', $jsondata);
    $result_app= file_put_contents('applications.json', $jsondata_app);
    
    if (($result = file_put_contents('prj-app.json', $jsondata) && ($result_app= file_put_contents('applications.json', $jsondata_app)))) {
    return new JsonResponse(
                array(
                    "status" => "success",
                    "result" => $result,
                    "result_app" => $result_app
                    )
                );
    }
    else {
    return new JsonResponse(
            array(
                "status" => "fail", 
                'message' => 'username does not exist',
                "check" => $jsondata,
                "documentroot" => $_SERVER['DOCUMENT_ROOT'],
                "result" => $result
                )
    );
    
    }
    
});
$app->run();



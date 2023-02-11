<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/autoload.php';

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

use Aws\S3\S3Client;

$app = new \Slim\Slim();

function verifyRequiredParams($required_fields) {
    // Assuming there is no error
    $error = false;

    // Error fields are blank
    $error_fields = "";

    // Getting the request parameters
    $request_params = $_REQUEST;

    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        // Getting the app instance
        $app = \Slim\Slim::getInstance();

        // Getting put parameters in request params variable
        parse_str($app->request()->getBody(), $request_params);
    }

    // Looping through all the parameters
    foreach ($required_fields as $field) {

        // if any requred parameter is missing
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            // error is true
            $error = true;

            // Concatnating the missing parameters in error fields
            $error_fields .= $field . ', ';
        }
    }

    // if there is a parameter missing then error is true
    if ($error) {
        // Creating response array
        $response = array();

        // Getting app instance
        $app = \Slim\Slim::getInstance();

        // Adding values to response array
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, - 2) . ' is missing or empty';

        // Displaying response with error code 400
        echoResponse(400, $response);

        // Stopping the app
        $app->stop();
    }
}

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization']) || isset($headers['authorization'])) {
        $db = new DbHandler();
        // get the api key
        $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];

        $token = explode(' ', $head);

        $api_key = $token[1];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid User Token";
            echoResponse(401, $response);
            $app->stop();
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "User Token is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function () use ($app) {

    verifyRequiredParams(array(
        'country_code',
        'username',
        'password'
    ));

    $response = array();

    $country_code = $app->request->post('country_code');
    $username = $app->request->post('username');
    $password = $app->request->post('password');

    $db = new DbHandler();
    $info = $db->checkUser($country_code, $username, $password);

    // get the user by email
    if ($info['status'] == 0) {
        $response['error'] = true;
        $response['message'] = "Login Credentials are Invalid";
        $response['flag'] = "invalid";
    } else if ($info['status'] == 5) {
        $response['error'] = true;
        $response['message'] = "Account Inactive";
        $response['flag'] = "inactive";
        $response['info'] = [
            'user_id' => $info['user_id'],
            'otp' => $info['otp'],
        ];
    } else {
        $message = "Login Successful";
        push_notification($info['device_id'], $message);

        $response['error'] = false;
        $response['message'] = "Login Successful";
        $response['info'] = $info;
    }

    echoResponse(200, $response);
});

$app->post('/signup', function () use ($app) {

    verifyRequiredParams(array(
        'name',
        'email',
        'country_code',
        'mobile',
        'password',
        'dob',
    ));
    $response = array();

    $name = $app->request->post('name');
    $username = $app->request->post('mobile');
    $email = $app->request->post('email');
    $country_code = $app->request->post('country_code');
    $mobile = $app->request->post('mobile');
    $password = $app->request->post('password');
    $dob = date('Y-m-d', strtotime($app->request->post('dob')));
    $anniversary = date('Y-m-d', strtotime($app->request->post('anniversary')));
    $device_id = $app->request->post('device_id');

    $valid = valid_email($email);
    $pass = checkPassword($password);

    if (!empty($pass)) {
        $response['error'] = true;
        $response['message'] = $pass;
        echoResponse(200, $response);
        die();
    }

    if ($valid == 0) {
        $response['error'] = true;
        $response['message'] = "Email is not in valid Format";
        echoResponse(200, $response);
        die();
    }



    $db = new DbHandler();
    $res = $db->UserSignup($name, $username, $email, $country_code, $mobile, $password, $dob, $anniversary, $device_id);

    // get the user by email
    if ($res['status'] == 1) {
        // get the user by email
        $message = "OTP Sent Successfully";
        push_notification($device_id, $message);
        $response['error'] = false;
        $response['message'] = "OTP Sent Successfully";
        $response['country_code'] = $res['country_code'];
        $response['mobile'] = $res['mobile'];
        $response['user_id'] = $res['user_id'];
        $response['OTP'] = $res['OTP'];
        $response['flag'] = "success";
    } else if ($res['status'] == 0) {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "User Registration Failed, Please try again";
        $response['flag'] = "failed";
    } else if ($res['status'] == 2) {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "User With same Email or Mobile No already Existed";
        $response['flag'] = "existed";
    } else if ($res['status'] == 5) {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "User Account Inactive";
        $response['flag'] = "inactive";
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
        $response['flag'] = "error";
    }

    echoResponse(200, $response);
});

$app->post('/otp_verify', function () use ($app) {

    verifyRequiredParams(array(
        'country_code',
        'mobile',
        'otp',
    ));

    $country_code = $app->request->post('country_code');
    $mobile = $app->request->post('mobile');
    $otp = $app->request->post('otp');
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->otpverficaion($country_code, $mobile, $otp);

    if ($info['status'] == 1) {
        $response['error'] = false;
        $response['message'] = "Account Verified";
        $response['details'] = $info;
    } if ($info['status'] == 2) {
        $response['error'] = false;
        $response['message'] = "Account is already Active";
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid OTP";
    }

    echoResponse(200, $response);
});

$app->post('/otp_resend', function () use ($app) {

    verifyRequiredParams(array(
        'country_code',
        'mobile',
    ));

    $country_code = $app->request->post('country_code');
    $mobile = $app->request->post('mobile');

    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->otpresend($country_code, $mobile);

    if ($info['status'] == 1) {
        $response['error'] = false;
        $response['message'] = "OTP Resend Successfully, Please check the mobile for SMS";
        $response['result'] = $info['otp'];
    } else {
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->post('/mobile-verify', function () use ($app) {

    verifyRequiredParams(array(
        'country_code',
        'mobile',
    ));

    $country_code = $app->request->post('country_code');
    $mobile = $app->request->post('mobile');
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->mobileverify($country_code, $mobile);

    if ($info['status'] == 1) {
        $response['error'] = false;
        $response['message'] = "OTP sent on registed mobile";
        $response['info'] = $info;
    } else if ($info['status'] == 5) {
        $response['error'] = false;
        $response['message'] = "Please activate the account first";
    } else {
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->post('/password-otp', function () use ($app) {

    verifyRequiredParams(array(
        'user_id',
        'otp',
    ));

    $user_id = $app->request->post('user_id');
    $otp = $app->request->post('otp');
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->otpverfy($user_id, $otp);

    if ($info['status'] == 1) {
        $response['error'] = false;
        $response['message'] = "Account Verify";
        $response['info'] = $info;
    } else {
        $response['error'] = true;
        $response['message'] = "OTP Didn't get verified, Please check again";
    }

    echoResponse(200, $response);
});

$app->post('/forget-password', function () use ($app) {

    verifyRequiredParams(array(
        'user_id',
        'password',
    ));

    $user_id = $app->request->post('user_id');
    $password = $app->request->post('password');
    $response = array();

    $pass = checkPassword($password);

    if (!empty($pass)) {
        $response['error'] = true;
        $response['message'] = $pass;
        echoResponse(200, $response);
        die();
    }

    $db = new DbHandler();
    // get the user by email
    $info = $db->forgot($user_id, $password);

    if ($info == 1) {
        $response['error'] = false;
        $response['message'] = "Password Change Successfully";
    } else {
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->post('/reset-password', 'authenticate', function () use ($app) {

    $current_password = $app->request->post('current_password');
    $new_password = $app->request->post('new_password');

    $pass = checkPassword($new_password);

    if (!empty($pass)) {
        $response['error'] = true;
        $response['message'] = $pass;
        echoResponse(200, $response);
        die();
    }

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->password_reset($user_token, $current_password, $new_password);

    if ($info) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Password Update Successfully";
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->post('/profile-update', 'authenticate', function () use ($app) {

//    verifyRequiredParams(array(
//        'name',
//        'dob',
//    ));

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];

    $response = array();
    $media_link = 0;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Check if file was uploaded without errors
        if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {
            $filename = $_FILES["profile_image"]["name"];
            $filetype = $_FILES["profile_image"]["type"];
            $types = explode('/', $filetype);

            $filesize = $_FILES["profile_image"]["size"];
            $path = $_SERVER['DOCUMENT_ROOT'] . '/api/upload/';
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $file = time() . '.' . $ext;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $path . $file)) {
                // Check whether file exists before uploading it
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => 'us-east-1',
                    'credentials' => [
                        'key' => 'AKIAWQRPCX74W4GH7RCI',
                        'secret' => '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'
                    ]
                ]);

                $bucket = 'wishmeee';
                $file_Path = $path . $file;
                $key = $user_token . '/profile/' . basename($file_Path);
                try {
                    $result = $s3Client->putObject([
                        'Bucket' => $bucket,
                        'Key' => $key,
                        'Body' => fopen($file_Path, 'r'),
                        'ACL' => 'public-read' // make file 'public'
                    ]);
                    $media_link = $result->get('ObjectURL');
                    // echo "Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
                } catch (Aws\S3\Exception\S3Exception $e) {
                    echo "There was an error uploading the file.\n";
                    echo $e->getMessage();
                }
            }
        }
    }


    $name = $app->request->post('name');
    $dob = date('Y-m-d', strtotime($app->request->post('dob')));
    $anniversary = date('Y-m-d', strtotime($app->request->post('anniversary')));

    $db = new DbHandler();
    $res = $db->ProfileUpdate($user_token, $name, $dob, $anniversary, $media_link);

    // get the user by email
    if ($res == 1) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "User Profile Updated Successfully";
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->post('/upload_media', 'authenticate', function () use ($app) {

    verifyRequiredParams(array(
        //'title',
        //'tags',
        'friend_id',
            //'mymedia'
            //'description'
    ));
    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $title = $app->request->post('title');
    $tags = $app->request->post('tags');
    $friend_id = $app->request->post('friend_id');
    $description = $app->request->post('description');
    $emojis = $app->request->post('emojis');
    $gifs = $app->request->post('gifs');
    $media = [];
    $audio = [];
    $image = [];
    $videoss = [];
    $thumb = [];
    $other = [];
    $response = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {


        for ($i = 0; $i < count($_FILES["mymedia"]["name"]); $i++) {

            // Check if file was uploaded without errors
            if (isset($_FILES["mymedia"]["name"][$i]) && $_FILES["mymedia"]["error"][$i] == 0) {
                $filename = $_FILES["mymedia"]["name"][$i];
                $filetype = mime_content_type($_FILES["mymedia"]["tmp_name"][$i]);
                $types = explode('/', $filetype);

                $filesize = $_FILES["mymedia"]["size"][$i];
                $path = $_SERVER['DOCUMENT_ROOT'] . '/api/upload/';
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $file = time() . $i . '.' . $ext;

                if (move_uploaded_file($_FILES["mymedia"]["tmp_name"][$i], $path . $file)) {
                    // Check whether file exists before uploading it
                    $s3Client = new S3Client([
                        'version' => 'latest',
                        'region' => 'us-east-1',
                        'credentials' => [
                            'key' => 'AKIAWQRPCX74W4GH7RCI',
                            'secret' => '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'
                        ]
                    ]);
                    if ($types[0] == 'image') {
                        $folder = $user_token . '/images/';
                        $key_type[$i] = 'images';
                    } else if ($types[0] == 'video') {
                        $folder = $user_token . '/videos/';
                        $key_type[$i] = 'videos';
                    } else if ($types[0] == 'audio') {
                        $folder = $user_token . '/audios/';
                        $key_type[$i] = 'audios';
                    } else {
                        $folder = $user_token . '/others/';
                        $key_type[$i] = 'others';
                    }

                    $bucket = 'wishmeee';
                    $file_Path = $path . $file;
                    $key = $folder . basename($file_Path);
                    try {
                        $result = $s3Client->putObject([
                            'Bucket' => $bucket,
                            'Key' => $key,
                            'Body' => fopen($file_Path, 'r'),
                            'ACL' => 'public-read' // make file 'public'
                        ]);

                        if ($types[0] == 'image') {
                            $image[] = ['images' => $result->get('ObjectURL')];
                        } else if ($types[0] == 'video') {

                            $sec = 1;

                            $thumbnail = $path . time() . '.png';
                            $ffmpeg = FFMpeg\FFMpeg::create();
                            $video = $ffmpeg->open($file_Path);
                            $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($sec));
                            $frame->save($thumbnail);
                            $source_img = $thumbnail;
                            $destination_img = $path . time() . '.png';

                            $d = compress($source_img, $destination_img, 80);
                            $thumbnail = $d;
                            $file_Path_thumb = $thumbnail;

                            $key_thumb = $user_token . '/thumbnail/' . basename($file_Path_thumb);

                            $result_thumb = $s3Client->putObject([
                                'Bucket' => $bucket,
                                'Key' => $key_thumb,
                                'Body' => fopen($file_Path_thumb, 'r'),
                                'ACL' => 'public-read' // make file 'public'
                            ]);

                            $videoss[] = ['videos' => [
                                    'link' => $result->get('ObjectURL'),
                                    'thumb' => $result_thumb->get('ObjectURL')
                            ]];
                        } else if ($types[0] == 'audio') {
                            $audio[] = ['audios' => $result->get('ObjectURL')];
                        } else {
                            $other[] = ['others' => $result->get('ObjectURL')];
                        }

                        // echo "Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
                    } catch (Aws\S3\Exception\S3Exception $e) {
                        echo "There was an error uploading the file.\n";
                        echo $e->getMessage();
                    }
                }
            }
        }
    }

    $media = array_merge($image, $videoss, $audio, $other);
    $media = array_2d_to_1d($media);

    $db = new DbHandler();
    // get the user by email
    $info = $db->getMediaUpload($title, $media, $tags, $user_token, $friend_id, $description, $emojis, $gifs);

    if ($info) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Media Successfully Uploaded";
        $response['media'] = $media;
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->post('/search_media', 'authenticate', function () use ($app) {

    $keyword = $app->request->post('keyword');
    $month = $app->request->post('month');
    $year = $app->request->post('year');
    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->getMediaFetch($user_token, $keyword, $month, $year);

    if ($info) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Search Data";
        $response['private_message_count'] = $info['message'];
        $response['data'] = $info['res'];
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "Search data not available";
    }


    echoResponse(200, $response);
});

$app->post('/media_privacy', 'authenticate', function () use ($app) {

    //$data = json_decode( file_get_contents('php://input') );

    $media_id = $app->request->post('media_id');
    $private = $app->request->post('private');

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();

    // get the user by email
    $info = $db->getMediaPrivacy($user_token, $media_id, $private);

    if ($info == 1) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Privacy Updated Successfully";
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }


    echoResponse(200, $response);
});

$app->post('/private_media', 'authenticate', function () use ($app) {

    //$data = json_decode( file_get_contents('php://input') );

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();

    // get the user by email
    $info = $db->getPrivateMedia($user_token);

    if ($info) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Private Media List";
        $response['data'] = $info;
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }


    echoResponse(200, $response);
});

$app->post('/media_delete', 'authenticate', function () use ($app) {

    $media_id = $app->request->post('media_id');

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->getMediaDelete($user_token, $media_id);

    if ($info == 1) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Media Deleted Successfully";
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }


    echoResponse(200, $response);
});

$app->post('/friends', 'authenticate', function () use ($app) {

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->getFriendList($user_token);

    if (!empty($info)) {
        $response['error'] = false;
        $response['message'] = "Friend List";
        $response['data'] = $info;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Available";
    }

    echoResponse(200, $response);
});

$app->post('/profile_view', 'authenticate', function () use ($app) {

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $info = $db->getProfileView($user_token);

    if (!empty($info)) {
        $response['error'] = false;
        $response['message'] = "Profile Details";
        $response['data'] = $info;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Available";
    }

    echoResponse(200, $response);
});

$app->get('/adminproperties', function () use ($app) {

    $link = url() . '/api/images/logo.png';
    $response = array();
    $images = array();
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.43.0');
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/emojis');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $result = json_decode($result, 1);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    foreach ($result as $name => $img) {
        $images[] = $img;
    };

    $db = new DbHandler();
    $gallery = $db->gifgallery();

    // get the user by email
    $info = [
        'applogo' => $link,
        'videosize' => '10MB',
        'emojis' => $images,
        'gifs' => $gallery,
    ];

    if (!empty($info)) {
        $response['error'] = false;
        $response['message'] = "Admin Properties";
        $response['data'] = $info;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Available";
    }

    echoResponse(200, $response);
});

$app->post('/notification', function () use ($app) {

    $device_id = $app->request->post('device_id');

    $message = "notification testing";
    push_notification($device_id, $message);

    $response['error'] = false;
    $response['message'] = "notification testing";

    echoResponse(200, $response);
});

$app->post('/fblogin', function () use ($app) {

    $token = $app->request->post('token');

    $user_id = $app->request->post('user_id');
    $name = $app->request->post('name');
    $profile_pic = $app->request->post('profile');
    $device_id = $app->request->post('device_id');

    $db = new DbHandler();
    $res = $db->UserfbSignup($token, $user_id, $name, $profile_pic, $device_id);

    // get the user by email
    if ($res['status'] == 1) {
        // get the user by email
        $response['error'] = false;
        $response['info'] = $res;
        $response['flag'] = "success";
    } else if ($res['status'] == 0) {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "User Registration Failed, Please try again";
        $response['flag'] = "failed";
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
        $response['flag'] = "error";
    }

    echoResponse(200, $response);
});

$app->post('/receive_wish', function () use ($app) {

    $response = array();
    $headers = apache_request_headers();
    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];

    $db = new DbHandler();
    $info = $db->receivewish($user_token);

    // get the user by email
    if (isset($info)) {
        $response['error'] = false;
        $response['message'] = "Receiver List";
        $response['data'] = $info;
    } else {
        $response['error'] = true;
        $response['message'] = "Recever List Empty";
    }

    echoResponse(200, $response);
});

$app->post('/responses', 'authenticate', function () use ($app) {

    verifyRequiredParams(array(
        'wish_id',
    ));
    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $wish_id = $app->request->post('wish_id');
    $comments = $app->request->post('comments');
    $emojis = $app->request->post('emojis');
    $gifs = $app->request->post('gifs');

    $media = [];
    $audio = [];
    $image = [];
    $videoss = [];
    $thumb = [];
    $other = [];
    $response = array();

    if (isset($_FILES["mymedia"])) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            for ($i = 0; $i < count($_FILES["mymedia"]["name"]); $i++) {

                // Check if file was uploaded without errors
                if (isset($_FILES["mymedia"]["name"][$i]) && $_FILES["mymedia"]["error"][$i] == 0) {
                    $filename = $_FILES["mymedia"]["name"][$i];
                    $filetype = mime_content_type($_FILES["mymedia"]["tmp_name"][$i]);
                    $types = explode('/', $filetype);

                    $filesize = $_FILES["mymedia"]["size"][$i];
                    $path = $_SERVER['DOCUMENT_ROOT'] . '/api/upload/';
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $file = time() . $i . '.' . $ext;

                    if (move_uploaded_file($_FILES["mymedia"]["tmp_name"][$i], $path . $file)) {
                        // Check whether file exists before uploading it
                        $s3Client = new S3Client([
                            'version' => 'latest',
                            'region' => 'us-east-1',
                            'credentials' => [
                                'key' => 'AKIAWQRPCX74W4GH7RCI',
                                'secret' => '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'
                            ]
                        ]);
                        if ($types[0] == 'image') {
                            $folder = $user_token . '/images/';
                            $key_type[$i] = 'images';
                        } else if ($types[0] == 'video') {
                            $folder = $user_token . '/videos/';
                            $key_type[$i] = 'videos';
                        } else if ($types[0] == 'audio') {
                            $folder = $user_token . '/audios/';
                            $key_type[$i] = 'audios';
                        } else {
                            $folder = $user_token . '/others/';
                            $key_type[$i] = 'others';
                        }

                        $bucket = 'wishmeee';
                        $file_Path = $path . $file;
                        $key = $folder . basename($file_Path);
                        try {
                            $result = $s3Client->putObject([
                                'Bucket' => $bucket,
                                'Key' => $key,
                                'Body' => fopen($file_Path, 'r'),
                                'ACL' => 'public-read' // make file 'public'
                            ]);

                            if ($types[0] == 'image') {
                                $image[] = ['images' => $result->get('ObjectURL')];
                            } else if ($types[0] == 'video') {

                                $sec = 1;

                                $thumbnail = $path . time() . '.png';
                                $ffmpeg = FFMpeg\FFMpeg::create();
                                $video = $ffmpeg->open($file_Path);
                                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($sec));
                                $frame->save($thumbnail);
                                $source_img = $thumbnail;
                                $destination_img = $path . time() . '.png';

                                $d = compress($source_img, $destination_img, 80);
                                $thumbnail = $d;
                                $file_Path_thumb = $thumbnail;

                                $key_thumb = $user_token . '/thumbnail/' . basename($file_Path_thumb);

                                $result_thumb = $s3Client->putObject([
                                    'Bucket' => $bucket,
                                    'Key' => $key_thumb,
                                    'Body' => fopen($file_Path_thumb, 'r'),
                                    'ACL' => 'public-read' // make file 'public'
                                ]);

                                $videoss[] = ['videos' => [
                                        'link' => $result->get('ObjectURL'),
                                        'thumb' => $result_thumb->get('ObjectURL')
                                ]];
                            } else if ($types[0] == 'audio') {
                                $audio[] = ['audios' => $result->get('ObjectURL')];
                            } else {
                                $other[] = ['others' => $result->get('ObjectURL')];
                            }

                            // echo "Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
                        } catch (Aws\S3\Exception\S3Exception $e) {
                            echo "There was an error uploading the file.\n";
                            echo $e->getMessage();
                        }
                    }
                }
            }
        }

        $media = array_merge($image, $videoss, $audio, $other);
        $media = array_2d_to_1d($media);
    }

    $db = new DbHandler();
    // get the user by email
    $info = $db->getresponseUpload($wish_id, $media, $comments, $emojis, $gifs, $user_token);

    if ($info) {
        // get the user by email
        $response['error'] = false;
        $response['message'] = "Response on Wish Successfully Updated";
        $response['media'] = $media;
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
});

$app->get('/subscription', function () use ($app) {

    $response = array();

    $db = new DbHandler();
    // get the user by email
    $subscription = $db->subscriptionlist();
    
    
    if (!empty($subscription)) {
        $response['error'] = false;
        $response['message'] = "Subscription List";
        $response['data'] = $subscription;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Available";
    }

    echoResponse(200, $response);
});

$app->post('/transaction', function () use ($app) {

    $headers = apache_request_headers();

    $head = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = explode(' ', $head);

    $user_token = $token[1];
    $subscription_id = $app->request->post('subscription_id');
    $paid_amount = $app->request->post('paid_amount');
    $paid_amount_currency = $app->request->post('paid_amount_currency');
    $txn_id = $app->request->post('txn_id');
    $payment_status = $app->request->post('payment_status');

    $response = array();

    $db = new DbHandler();
    // get the user by email
    $trans = $db->transaction($user_token, $subscription_id, $paid_amount, $paid_amount_currency, $txn_id, $payment_status);

    if (!empty($trans)) {
        $response['error'] = false;
        $response['message'] = "Order Successfully Created";
        $response['data'] = $trans;
    } else {
        $response['error'] = true;
        $response['message'] = "Order Failed";
    }

    echoResponse(200, $response);
});

$app->post('/payment-intent', function () use ($app) {
    
    $amount = $app->request->post('amount');
    $currency = $app->request->post('currency');
    $payment_method = $app->request->post('payment_method');
    $stripe = new \Stripe\StripeClient(
            'sk_test_51MHRU0ClxXY713GvgyUUS7kTJPNft8KPSeGuWeCT3EvZHDAoZk60diOxOtzqPQe70coyr8sry82N3LsZQSq5Qsl700bRYVXGLs'
    );
    $response = $stripe->paymentIntents->create([
        'amount' => $amount,
        'currency' => $currency,
        'payment_method_types' => ['card'],
    ]);
    
    //print_r($result);

//    if (!empty($trans)) {
//        $response['error'] = false;
//        $response['message'] = "Order Successfully Created";
//        $response['data'] = $trans;
//    } else {
//        $response['error'] = true;
//        $response['message'] = "Order Failed";
//    }

    echoResponse(200, $response);
});

$app->post('/sms', function () use ($app) {

    $sid = $app->request->post('sid');
    $token = $app->request->post('token');
    $from = $app->request->post('from');
    $to = $app->request->post('to');
    $body = $app->request->post('body');
    $response = array();

    $db = new DbHandler();
    // get the user by email
    $sms = send_twilio_text_sms($sid, $token, $from, $to, $body);

    print_r($sms);
    die;

    if (!empty($info)) {
        $response['error'] = false;
        $response['message'] = "Profile Details";
        $response['data'] = $info;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Available";
    }

    echoResponse(200, $response);
});

function fblogin() {
    $fb = new Facebook\Facebook([
        'app_id' => '533559981913357',
        'app_secret' => '818c6bbd61fd3044b389a7987707f83d',
        'default_graph_version' => 'v2.10',
    ]);

    $helper = $fb->getRedirectLoginHelper();

    try {
        $accessToken = $helper->getAccessToken();
        print_r($accessToken);
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }


    if (!isset($accessToken)) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
    }

// Logged in
    echo '<h3>Access Token</h3>';
    var_dump($accessToken->getValue());

// The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);
    echo '<h3>Metadata</h3>';
    var_dump($tokenMetadata);

// Validation (these will throw FacebookSDKException's when they fail)
    $tokenMetadata->validateAppId($config['app_id']);
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
    $tokenMetadata->validateExpiration();

    if (!$accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
        }

        echo '<h3>Long-lived</h3>';
        var_dump($accessToken->getValue());
    }

    $_SESSION['fb_access_token'] = (string) $accessToken;
}

/**
 * Echoing json response to client
 *
 * @param String $status_code
 *            Http response code
 * @param Int $response
 *            Json response
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->response->headers->set('Content-Type', 'application/json');
    $app->contentType('application/json');

    echo json_encode($response);
}

function url() {
    return sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['REQUEST_URI']
    );
}

function valid_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Validate email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 1;
    } else {
        return 0;
    }
}

function array_2d_to_1d($a) {

    $out = array();
    foreach ($a as $k => $b) {
        foreach ($b as $key => $c) {
            if (isset($c)) {
                $out[$key][] = $c;
            }
        }
    }
    return $out;
}

function compress($source, $destination, $quality) {

    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg')
        $image = imagecreatefromjpeg($source);

    elseif ($info['mime'] == 'image/gif')
        $image = imagecreatefromgif($source);

    elseif ($info['mime'] == 'image/png')
        $image = imagecreatefrompng($source);

    imagejpeg($image, $destination, $quality);

    return $destination;
}

function checkPassword($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "Password too short!, minimum length should be 8";
    }

    if (strlen($password) > 20) {
        $errors[] = "Password too long!, maximum length should be 20";
    }

    if (!preg_match("#[0-9]+#", $password)) {
        $errors[] = "Password must include at least one number!";
    }

    if (!preg_match("#[A-Z]+#", $password)) {
        $errors[] = "Password must include at least one uppercase letter!";
    }

    if (!preg_match("#[a-z]+#", $password)) {
        $errors[] = "Password must include at least one lowercase letter!";
    }

    if (!preg_match("#\W+#", $password)) {
        $errors[] = "Password must include at least one special character!";
    }

    return $errors;
}

function push_notification($device_id, $message) {

    //API URL of FCM
    $url = 'https://fcm.googleapis.com/fcm/send';

    /* api_key available in:
      Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key */
    $api_key = 'AAAAmdnoPVA:APA91bGh9Y04lWgTofkbUkqOL10BAWJoP4ErPZ3LMAtrAsAPhasfKXx0nIXint0zwh5_GH4gThhwc12NEi0qRf_q7qylgFwtlamzkl6c73RUiWOzUA2xvosY9-0z7Ox5_YyUOJF7Nmoc';

    $fields = array(
        'registration_ids' => array(
            $device_id
        ),
        'data' => array(
            "message" => $message
        )
    );

    //header includes Content type and api key
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $api_key
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function mailsend($toemail, $toname, $subject, $content) {
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";
    $mail->SMTPDebug = 0;

    $mail->SMTPAuth = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;
    $mail->Host = "smtp.gmail.com";
    $mail->Username = "medlogdev0505@gmail.com";
    $mail->Password = "Abcd@123";

    $mail->IsHTML(true);

    $mail->AddAddress($toemail, $toname);
    $mail->SetFrom("info@strivevision.com", "Strive Vision");
    $mail->AddReplyTo("info@strivevision.com", "Strive Vision");
    // $mail->AddCC("cc-recipient-email@domain", "cc-recipient-name");
    $mail->Subject = $subject;
    $mail->MsgHTML($content);
    if (!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}

function send_twilio_text_sms($id, $token, $from, $to, $body) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . $id . "/SMS/Messages";
    $data = array(
        'From' => $from,
        'To' => $to,
        'Body' => $body,
    );
    $post = http_build_query($data);
    $x = curl_init($url);
    curl_setopt($x, CURLOPT_POST, true);
    curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
    curl_setopt($x, CURLOPT_POSTFIELDS, $post);
    $y = curl_exec($x);
    curl_close($x);
    return $y;
}

$app->run();
?>
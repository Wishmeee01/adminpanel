<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* ------------- users table method ------------------ */

    /**
     * Creating new user
     *
     * @param String $name
     *            User full name
     * @param String $email
     *            User login email id
     * @param String $password
     *            User login password
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from user WHERE username = ?");
        $stmt->execute([
            $email
        ]);
        $num_rows = $stmt->fetchColumn();
        return $num_rows > 0;
    }

    private function isEmailExists($email) {
        $stmt = $this->conn->prepare("SELECT id from user WHERE email = ? and status = 10");
        $stmt->execute([
            $email
        ]);
        $num_rows = $stmt->fetchColumn();
        return $num_rows > 0;
    }

    private function isUserActive($country_code, $mobile) {

        $stmt = $this->conn->prepare("SELECT id from user WHERE country_code = ? and mobile = ? and status = 0");
        $stmt->execute([
            $country_code,
            $mobile
        ]);
        $num_rows = $stmt->fetchColumn();
        return $num_rows > 0;
    }

    private function isMobileExists($mobile) {
        $stmt = $this->conn->prepare("SELECT id from user WHERE mobile = ? and status = 10");
        $stmt->execute([
            $mobile
        ]);
        $num_rows = $stmt->fetchColumn();
        return $num_rows > 0;
    }

    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT id from user WHERE user_token = ?");
        $stmt->execute([
            $api_key
        ]);
        $num_rows = $stmt->fetchColumn();

        return $num_rows > 0;
    }

    public function checkUser($country_code, $username, $password) {
        // fetching user by email
        if (!$this->isUserActive($country_code, $username)) {
            $stmt = $this->conn->prepare("SELECT id,password_hash,user_token, email, country_code, mobile,device_id FROM user WHERE username = ? and country_code = ?  and status=10");
            $stmt->execute([
                $username,
                $country_code
            ]);
            $count = $stmt->rowCount();
            $row = $stmt->fetch();
            $res = array();

            if ($count > 0) {
                // Found user with the email
                // Now verify the password

                if (PassHash::check_password($row['password_hash'], $password)) {

                    $stmt_details = $this->conn->prepare("SELECT id,user_id, name, date_of_birth,anniversary_date,profile_image from user_details  WHERE user_id = ?  AND status=1");

                    $stmt_details->execute([
                        $row['id']
                    ]);
                    $row_det = $stmt_details->fetch();

                    $res["id"] = $row_det['user_id'];
                    $res["user_token"] = $row['user_token'];
                    $res["name"] = $row_det['name'];
                    $res["email"] = $row['email'];
                    $res["country_code"] = $row['country_code'];
                    $res["mobile"] = $row['mobile'];
                    $res["date_of_birth"] = date('m/d/Y', strtotime($row_det['date_of_birth']));
                    $res["anniversary_date"] = date('m/d/Y', strtotime($row_det['anniversary_date']));
                    $res["profile_image"] = $row_det['profile_image'];
                    $res["device_id"] = $row['device_id'];
                    $res["status"] = 1;

                    // User password is correct
                    return $res;
                } else {
                    // user password is incorrect
                    return $res = [
                        "status" => 0
                    ];
                }
            } else {
                // user not existed with the email
                return $res = [
                    "status" => 0
                ];
            }
        } else {
            $stmt = $this->conn->prepare("SELECT id from user WHERE country_code = ? and mobile = ? and status = 0");
            $stmt->execute([
                $country_code,
                $username
            ]);
            $row = $stmt->fetch();

            return $res = [
                "user_id" => $row['id'],
                "otp" => '12345',
                "status" => USER_NOT_ACTIVATED
            ];
        }
    }

    public function UserSignup($name, $username, $email, $country_code, $mobile, $password, $dob, $anniversary, $device_id) {
        // fetching user by email
        require_once 'PassHash.php';
        $usertoken = $this->generateApiKey();
        //$otp = sprintf("%06d", mt_rand(1, 999999));
        $otp = '12345';
        $status = '0';
        $time = time();

        if (!$this->isUserActive($country_code, $mobile)) {

            if (!$this->isUserExists($username) && !$this->isEmailExists($email) && !$this->isMobileExists($mobile)) {

                $password_hash = PassHash::hash($password);
                $authkey = md5($email . time());
                $stmt = $this->conn->prepare("insert into user (username, user_token, auth_key, password_hash, email, country_code, mobile, device_id, otp, status, created_at, updated_at) values (?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $username,
                    $usertoken,
                    $authkey,
                    $password_hash,
                    $email,
                    $country_code,
                    $mobile,
                    $device_id,
                    $otp,
                    $status,
                    $time,
                    $time
                ]);
                $insert = $this->conn->lastInsertId();

                $stmt_user = $this->conn->prepare("insert into user_details (user_id, name, date_of_birth, anniversary_date, created,updated) values (?,?,?,?,?,?)");
                $result = $stmt_user->execute([
                    $insert,
                    $name,
                    $dob,
                    $anniversary,
                    $time,
                    $time
                ]);
                $insertm = $this->conn->lastInsertId();

                // Check for successful insertion

                if ($result) {

                    return $res = [
                        'country_code' => $country_code,
                        'mobile' => $mobile,
                        'OTP' => $otp,
                        'user_id' => $insert,
                        'status' => USER_CREATED_SUCCESSFULLY
                    ];
                } else {
                    // Failed to create user
                    return $res = [
                        'status' => USER_CREATE_FAILED
                    ];
                }
            } else {
                // User with same email already existed in the db
                return $res = [
                    'status' => USER_ALREADY_EXISTED
                ];
            }
        } else {
            // User with same email already existed in the db
            return $res = [
                'status' => USER_NOT_ACTIVATED
            ];
        }
    }

    public function otpverficaion($country_code, $mobile, $otp) {
        // fetching user by email
        $res = [];

        $stmts = $this->conn->prepare("SELECT id, user_token, email, country_code, mobile  FROM user WHERE country_code = ? and mobile = ? and status=10");
        $stmts->execute([
            $country_code,
            $mobile,
        ]);

        $rows = $stmts->fetch();
        $counts = $stmts->rowCount();

        if ($counts == 0) {

            $stmt = $this->conn->prepare("SELECT id, user_token, email, country_code, mobile  FROM user WHERE country_code = ? and mobile = ? and otp = ? and status=0");
            $stmt->execute([
                $country_code,
                $mobile,
                $otp
            ]);

            $row = $stmt->fetch();
            $count = $stmt->rowCount();

            if ($count == 1) {
                $stmtupdate = $this->conn->prepare("update user set otp = NULL, status = 10 WHERE id = ?");
                $stmtupdate->execute([
                    $row['id'],
                ]);

                $stmt_details = $this->conn->prepare("SELECT id,user_id, name, date_of_birth,anniversary_date,profile_image from user_details  WHERE user_id = ?  AND status=1");

                $stmt_details->execute([
                    $row['id']
                ]);
                $row_det = $stmt_details->fetch();

                $res["id"] = $row_det['user_id'];
                $res["name"] = $row_det['name'];
                $res["user_token"] = $row['user_token'];
                $res["email"] = $row['email'];
                $res["country_code"] = $row['country_code'];
                $res["mobile"] = $row['mobile'];
                $res["date_of_birth"] = date('m/d/Y', strtotime($row_det['date_of_birth']));
                $res["anniversary_date"] = date('m/d/Y', strtotime($row_det['anniversary_date']));
                $res["profile_image"] = $row_det['profile_image'];
                $res["status"] = 1;
                // User password is correct
                return $res;
            } else {
                $res["status"] = 0;
            }
        } else {
            $res["status"] = 2;
        }

        return $res;
    }

    public function ProfileUpdate($user_token, $name, $dob, $anniversary, $media_link) {
        $time = time();

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);

        $row = $stmt->fetch();

        $stmti = $this->conn->prepare("SELECT profile_image FROM user_details WHERE user_id = ?");
        $stmti->execute([
            $row['id']
        ]);

        $rowi = $stmti->fetch();

        if ($media_link == '0') {
            $link = $rowi['profile_image'];
        } else {
            $link = $media_link;
        }

        $stmt_user = $this->conn->prepare("update user_details set name = ?, date_of_birth =?, anniversary_date = ?, profile_image = ?, updated = ? where user_id = ?");
        $result = $stmt_user->execute([
            $name,
            $dob,
            $anniversary,
            $link,
            $time,
            $row['id']
        ]);

        // Check for successful insertion
        if ($result) {
            return USER_CREATED_SUCCESSFULLY;
            // return USER_CREATED_SUCCESSFULLY;
        }
    }

    public function mobileverify($country_code, $mobile) {
        // fetching user by email
        $otp = '12345';
        if (!$this->isUserActive($country_code, $mobile)) {
            $stmt = $this->conn->prepare("SELECT id FROM user WHERE country_code = ? and username =? and status = 10");
            $stmt->execute([
                $country_code,
                $mobile
            ]);
            $count = $stmt->rowCount();
            $row = $stmt->fetch();

            $stmt_user = $this->conn->prepare("update user set otp = ? where id = ?");
            $result = $stmt_user->execute([
                $otp,
                $row['id']
            ]);

            if ($count == 1) {
                return $res = [
                    'user_id' => $row['id'],
                    'otp' => $otp,
                    'status' => '1',
                ];
            } else {
                return $res = [
                    'status' => '0',
                ];
            }
        } else {
            return $res = [
                'status' => USER_NOT_ACTIVATED,
            ];
        }

//        if ($count == 1) {
//
//            require_once 'PassHash.php';
//
//            $password_hash = PassHash::hash($password);
//
//            $stmt = $this->conn->prepare("update user set password_hash= ? where country_code = ? and username =?");
//            $stmt->execute([
//                $password_hash,
//                $country_code,
//                $mobile_no
//            ]);
//            return true;
//        } else {
//            return $count;
//        }
    }

    public function otpresend($country_code, $mobile) {
        // fetching user by email
        $otp = '12345';
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE country_code = ? and username = ? and status=10");
        $stmt->execute([
            $country_code,
            $mobile
        ]);

        $row = $stmt->fetch();
        $count = $stmt->rowCount();

        if ($count == 1) {
            $stmtupdate = $this->conn->prepare("update user set otp = ? WHERE id = ?");
            $stmtupdate->execute([
                $otp,
                $row['id'],
            ]);
        }
        if ($count == 1) {
            return $res = [
                'otp' => $otp,
                'status' => '1',
            ];
        } else {
            return $res = [
                'status' => '0',
            ];
        }
    }

    public function otpverfy($userid, $otp) {
        // fetching user by email

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE id = ? and otp = ? and status=10");
        $stmt->execute([
            $userid,
            $otp
        ]);

        $row = $stmt->fetch();
        $count = $stmt->rowCount();

        if ($count == 1) {
            $stmtupdate = $this->conn->prepare("update user set otp = NULL WHERE id = ?");
            $stmtupdate->execute([
                $userid,
            ]);
        }
        if ($count == 1) {
            return $res = [
                'user_id' => $userid,
                'status' => '1',
            ];
        } else {
            return $res = [
                'status' => '0',
            ];
        }
    }

    public function forgot($user_id, $password) {
        // fetching user by email

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE id = ?");
        $stmt->execute([
            $user_id,
        ]);
        $count = $stmt->rowCount();

        if ($count == 1) {

            require_once 'PassHash.php';

            $password_hash = PassHash::hash($password);

            $stmt = $this->conn->prepare("update user set password_hash= ? where id = ? ");
            $stmt->execute([
                $password_hash,
                $user_id,
            ]);
            return true;
        } else {
            return $count;
        }
    }

    public function password_reset($user_token, $current_password, $new_password) {
        // fetching user by email

        $stmt = $this->conn->prepare("SELECT * FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);

        $row = $stmt->fetch();
        $count = $stmt->rowCount();

        if ($count == 1) {

            if (PassHash::check_password($row['password_hash'], $current_password)) {

                require_once 'PassHash.php';

                $password_hash = PassHash::hash($new_password);

                $stmt = $this->conn->prepare("update user set password_hash= ? where user_token = ?");
                $stmt->execute([
                    $password_hash,
                    $user_token
                ]);
                return true;
            } else {
                return false;
            }
        } else {
            return $count;
        }
    }

    public function random_strings($length_of_string) {

        // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result), 0, $length_of_string);
    }

    public function getMediaUpload($title, $link, $tags, $user_token, $friend_id, $description, $emojis, $gifs) {

        $link = json_encode($link);

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);
        $count = $stmt->rowCount();
        $row = $stmt->fetch();

        $stmt = $this->conn->prepare("insert into media (user_id,friend_id, title, link,tags,description, emojis, gifs, month, year, created) values (?,?,?,?,?,?,?,?,?,?,?)");

        $stmt->execute([
            $row['id'],
            $friend_id,
            $title,
            $link,
            $tags,
            $description,
            $emojis,
            $gifs,
            date('m'),
            date('Y'),
            time()
        ]);

        return true;
    }

    public function getMediaFetch($user_token, $keyword, $month, $year) {
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);
        $count = $stmt->rowCount();
        $row = $stmt->fetch();
        $user_id = $row['id'];
        $response = [];
        if ($keyword == '' && $month == '' && $year == '') {
            $stmt = $this->conn->prepare("select * from media where user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $user_id
            ]);
        } else if ($keyword != '' && $month == '' && $year == '') {
            $stmt = $this->conn->prepare("select * from media where (title like '%$keyword%' or tags like '%$keyword%') and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $user_id
            ]);
        } else if ($keyword == '' && $month != '' && $year == '') {
            $stmt = $this->conn->prepare("select * from media where month = ? and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $month,
                $user_id
            ]);
        } else if ($keyword == '' && $month == '' && $year != '') {
            $stmt = $this->conn->prepare("select * from media where year = ? and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $year,
                $user_id
            ]);
        } else if ($keyword != '' && $month == '' && $year != '') {
            $stmt = $this->conn->prepare("select * from media where (title like '%$keyword%' or tags like '%$keyword%') and year = ? and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $year,
                $user_id
            ]);
        } else if ($keyword != '' && $month != '' && $year == '') {
            $stmt = $this->conn->prepare("select * from media where (title like '%$keyword%' or tags like '%$keyword%') and month = ? and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $month,
                $user_id
            ]);
        } else if ($keyword != '' && $month != '' && $year != '') {
            $stmt = $this->conn->prepare("select * from media where (title like '%$keyword%' or tags like '%$keyword%') and year = ? and month = ? and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $year,
                $month,
                $user_id
            ]);
        } else if ($keyword == '' && $month != '' && $year != '') {
            $stmt = $this->conn->prepare("select * from media where year = ? and month = ? and user_id = ? and status = 1 order by created desc");
            $stmt->execute([
                $year,
                $month,
                $user_id
            ]);
        }

        $res = array();
        // TODO
        // $task = $stmt->get_result()->fetch_assoc();
        $row = $stmt->fetchAll();

        $stmtc = $this->conn->prepare("select * from media where user_id = ? and status = 1 and media_privacy =1 order by created desc");
        $stmtc->execute([
            $user_id
        ]);
        // TODO
        // $task = $stmt->get_result()->fetch_assoc();
        $rowc = $stmtc->fetchAll();

        $message_count = count($rowc);
        foreach ($row as $i => $r) {

            $stmtr = $this->conn->prepare("SELECT id,media, emojis, gifs,comments, createdAt,updatedAt,createdBy, updatedBy FROM wish_response WHERE wish_id = ? and status=1");
            $stmtr->execute([
                $r['id']
            ]);

            $rowr = $stmtr->fetchAll();

            foreach ($rowr as $k => $p) {

                $response[$k]["id"] = $p['id'];
                $response[$k]["media"] = json_decode($p['media']);
                $response[$k]["emojis"] = $p['emojis'];
                $response[$k]["gifs"] = $p['gifs'];
                $response[$k]["comments"] = $p['comments'];
                $response[$k]["createdAt"] = date('m/d/Y', strtotime($p['createdAt']));
                $response[$k]["updatedAt"] = date('m/d/Y', strtotime($p['updatedAt']));
                $response[$k]["createdBy"] = $this->user_name($p['createdBy']);
                $response[$k]["updatedBy"] = $this->user_name($p['updatedBy']);
            }

            $res[$i]["id"] = $r['id'];
            $res[$i]["media"] = json_decode($r['link']);
            $res[$i]["title"] = $r['title'];
            $res[$i]["tags"] = $r['tags'];
            $res[$i]["privacy_status"] = $r['media_privacy'];
            $res[$i]["privacy"] = $r['media_privacy'] == 0 ? 'Public' : 'Private';
            $res[$i]["description"] = $r['description'];
            $res[$i]["emojis"] = $r['emojis'];
            $res[$i]["gifs"] = $r['gifs'];
            $res[$i]["publish_date"] = date('m/d/Y', $r['created']);
            $res[$i]["response"] = $response;
        }

        return [
            'message' => $message_count,
            'res' => $res,
        ];
    }

    public function getPrivateMedia($user_token) {
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);
        $count = $stmt->rowCount();
        $row = $stmt->fetch();
        $user_id = $row['id'];

        $stmt = $this->conn->prepare("select * from media where user_id = ? and status = 1 and media_privacy =1 order by created desc");
        $stmt->execute([
            $user_id
        ]);

        $res = array();
        // TODO
        // $task = $stmt->get_result()->fetch_assoc();
        $row = $stmt->fetchAll();
        foreach ($row as $i => $r) {
            $res[$i]["id"] = $r['id'];
            $res[$i]["media"] = json_decode($r['link']);
            $res[$i]["title"] = $r['title'];
            $res[$i]["tags"] = $r['tags'];
            $res[$i]["privacy_status"] = $r['media_privacy'];
            $res[$i]["privacy"] = $r['media_privacy'] == 0 ? 'Public' : 'Private';
            $res[$i]["description"] = $r['description'];
            $res[$i]["publish_date"] = date('m/d/Y', $r['created']);
        }

        return $res;
    }

    public function getMediaPrivacy($user_token, $video_id, $private) {

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);

        $row = $stmt->fetch();

        $stmtv = $this->conn->prepare("SELECT id FROM media where id = ? and user_id = ?");
        $stmtv->execute([
            $video_id,
            $row['id']
        ]);

        $rowv = $stmtv->fetch();

        if (isset($rowv['id'])) {

            $stmtu = $this->conn->prepare("update media set media_privacy = ? where id = ? and user_id = ?");
            $stmtu->execute([
                $private,
                $video_id,
                $row['id']
            ]);
            return 1;
        } else {
            return 0;
        }
    }

    public function getMediaDelete($user_token, $video_id) {

        $status = 0;

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);

        $row = $stmt->fetch();

        $stmtv = $this->conn->prepare("SELECT id FROM media where id = ? and user_id = ?");
        $stmtv->execute([
            $video_id,
            $row['id']
        ]);

        $rowv = $stmtv->fetch();

        if (isset($rowv['id'])) {

            $stmtu = $this->conn->prepare("update media set status = ? where id = ? and user_id = ?");
            $stmtu->execute([
                $status,
                $video_id,
                $row['id']
            ]);
            return 1;
        } else {
            return 0;
        }
    }

    public function getFriendList($user_token) {
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);

        $row = $stmt->fetch();

        $stmtd = $this->conn->prepare("select a.id, b.name, b.profile_image,b.date_of_birth, b.anniversary_date from user a, user_details b where a.id = b.user_id and a.status =10 and a.id != ?");

        $stmtd->execute([
            $row['id'],
        ]);

        $rowd = $stmtd->fetchAll();
        foreach ($rowd as $i => $r) {
            $res[$i]["id"] = $r['id'];
            $res[$i]["name"] = $r['name'];
            $res[$i]["profile_image"] = $r['profile_image'];
            $res[$i]["date_of_birth"] = date('m/d/Y', strtotime($r['date_of_birth']));
            $res[$i]["anniversary_date"] = date('m/d/Y', strtotime($r['anniversary_date']));
        }
        return $res;
    }

    public function getProfileView($user_token) {
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);

        $row = $stmt->fetch();

        $stmtd = $this->conn->prepare("select a.id, a.email,a.country_code,a.mobile, b.name,b.profile_image, b.date_of_birth,b.anniversary_date from user a, user_details b where a.id = b.user_id and a.status =10 and a.id = ?");

        $stmtd->execute([
            $row['id'],
        ]);

        $r = $stmtd->fetch();

        $res["id"] = $r['id'];
        $res["name"] = $r['name'];
        $res["email"] = $r['email'];
        $res["country_code"] = $r['country_code'];
        $res["mobile"] = $r['mobile'];
        $res["date_of_birth"] = date('m/d/Y', strtotime($r['date_of_birth']));
        $res["anniversary_date"] = date('m/d/Y', strtotime($r['anniversary_date']));
        $res["profile_image"] = $r['profile_image'];

        return $res;
    }

    public function UserfbSignup($token, $user_id, $name, $profile_pic, $device_id) {
        // fetching user by email
        $stmtt = $this->conn->prepare("SELECT * FROM user WHERE user_token = ? ");
        $stmtt->execute([
            $token
        ]);
        $rowt = $stmtt->fetch();
        $insert = 0;

        if (!$this->isUserExists($rowt['username'])) {
            $time = time();
            $status = 1;
            $social_login = 1;
            $authkey = md5($user_id . time());
            $stmt = $this->conn->prepare("insert into user (username, user_token, auth_key, social_login, device_id, status, created_at, updated_at) values (?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $user_id,
                $token,
                $authkey,
                $social_login,
                $device_id,
                $status,
                $time,
                $time
            ]);
            $insert = $this->conn->lastInsertId();

            $stmt_user = $this->conn->prepare("insert into user_details (user_id, name, profile_image, created,updated) values (?,?,?,?,?)");
            $result = $stmt_user->execute([
                $insert,
                $name,
                $profile_pic,
                $time,
                $time
            ]);
            $insertm = $this->conn->lastInsertId();
        } else {
            $insert = $rowt['id'];
        }

        $stmt_details = $this->conn->prepare("SELECT id,user_id, name, date_of_birth,anniversary_date,profile_image from user_details  WHERE user_id = ?  AND status=1");

        $stmt_details->execute([
            $insert
        ]);
        $row_det = $stmt_details->fetch();

        $res["id"] = $row_det['user_id'];
        $res["name"] = $row_det['name'];
        $res["user_token"] = $rowt['user_token'];
        $res["email"] = $rowt['email'];
        $res["country_code"] = $rowt['country_code'];
        $res["mobile"] = $rowt['mobile'];
        $res["date_of_birth"] = date('m/d/Y', strtotime($row_det['date_of_birth']));
        $res["anniversary_date"] = date('m/d/Y', strtotime($row_det['anniversary_date']));
        $res["profile_image"] = $row_det['profile_image'];
        // User password is correct

        return $res = [
            'info' => $res,
            'status' => USER_CREATED_SUCCESSFULLY
        ];
    }

    public function receivewish($user_token) {

        $stmtu = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmtu->execute([
            $user_token
        ]);
        $count = $stmtu->rowCount();
        $rowu = $stmtu->fetch();

        $stmt = $this->conn->prepare("select * from media where friend_id = ? and status = 1 order by created desc");
        $stmt->execute([
            $rowu['id']
        ]);

        $res = array();
        $response = array();
        // TODO
        // $task = $stmt->get_result()->fetch_assoc();
        $row = $stmt->fetchAll();
        foreach ($row as $i => $r) {

            $stmtr = $this->conn->prepare("SELECT id,media, emojis, gifs, comments, createdAt,updatedAt,createdBy, updatedBy FROM wish_response WHERE wish_id = ? and status=1");
            $stmtr->execute([
                $r['id']
            ]);

            $rowr = $stmtr->fetchAll();

            foreach ($rowr as $k => $p) {

                $response[$k]["id"] = $p['id'];
                $response[$k]["media"] = json_decode($p['media'], 1);
                $response[$k]["emojis"] = $p['emojis'];
                $response[$k]["gifs"] = $p['gifs'];
                $response[$k]["comments"] = $p['comments'];
                $response[$k]["createdAt"] = date('m/d/Y', strtotime($p['createdAt']));
                $response[$k]["updatedAt"] = date('m/d/Y', strtotime($p['updatedAt']));
                $response[$k]["createdBy"] = $this->user_name($p['createdBy']);
                $response[$k]["updatedBy"] = $this->user_name($p['updatedBy']);
            }

            $res[$i]["id"] = $r['id'];
            $res[$i]["media"] = json_decode($r['link'], 1);
            $res[$i]["title"] = $r['title'];
            $res[$i]["tags"] = $r['tags'];
            $res[$i]["privacy_status"] = $r['media_privacy'];
            $res[$i]["privacy"] = $r['media_privacy'] == 0 ? 'Public' : 'Private';
            $res[$i]["description"] = $r['description'];
            $res[$i]["publish_date"] = date('m/d/Y', $r['created']);
            $res[$i]["sender_id"] = $r['user_id'];
            $res[$i]["sender_name"] = $this->user_name($r['user_id']);
            $res[$i]["response"] = $response;
        }

        return $res;
    }

    public function getresponseUpload($wish_id, $media, $comments, $emojis, $gifs, $user_token) {

        $link = json_encode($media);

        $stmt = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmt->execute([
            $user_token
        ]);
        $count = $stmt->rowCount();
        $row = $stmt->fetch();

        $stmtr = $this->conn->prepare("SELECT user_id FROM media WHERE id = ? and status=1");
        $stmtr->execute([
            $wish_id
        ]);

        $rowr = $stmtr->fetch();

        $stmt = $this->conn->prepare("insert into wish_response (wish_id, sender_id, receiver_id, media, emojis, gifs, comments, createdAt, updatedAt, createdBy, updatedBy) values (?,?,?,?,?,?,?,?,?,?,?)");

        $stmt->execute([
            $wish_id,
            $rowr['user_id'],
            $row['id'],
            $link,
            $emojis,
            $gifs,
            $comments,
            date('y-m-d h:i:s'),
            date('y-m-d h:i:s'),
            $row['id'],
            $row['id']
        ]);

        return true;
    }

    public function subscriptionlist() {

        $stmt = $this->conn->prepare("select * from subscription where status = 1");
        $stmt->execute();

        $res = array();
        
        // TODO
        // $task = $stmt->get_result()->fetch_assoc();
        $row = $stmt->fetchAll();
        foreach ($row as $i => $r) {
            $response = [];
            if ($r['offer_status'] == 1) {
                $response = [];
                $stmtr = $this->conn->prepare("SELECT subscription_id,offer_name, offer_price, offer_start_date, offer_end_date,createdAt, updatedAt FROM offers WHERE subscription_id = ? and status=1");
                $stmtr->execute([
                    $r['id']
                ]);

                $p = $stmtr->fetch();

                    $response["offer_name"] = $p['offer_name'];
                    $response["offer_price"] = $p['offer_price'];
                    $response["offer_start_date"] = date('m/d/Y', strtotime($p['offer_start_date']));
                    $response["offer_end_date"] = date('m/d/Y', strtotime($p['offer_end_date']));
                    $response["createdAt"] = date('m/d/Y', strtotime($p['createdAt']));
                    $response["updatedAt"] = date('m/d/Y', strtotime($p['updatedAt']));
                $response = [$response];
            }

            $res[$i]["id"] = $r['id'];
            $res[$i]["plan_name"] = $r['plan_name'];
            $res[$i]["validity_in_days"] = $r['validity_in_days'];
            $res[$i]["amount"] = $r['amount'];
            $res[$i]["currency"] = $r['currency'];
            $res[$i]["icon"] = $r['icon'];
            $res[$i]["currency"] = $r['currency'];
            $res[$i]["features"] = [$r['feature1'],$r['feature2']];
            $res[$i]["description"] = $r['description'];
            $res[$i]["cycle"] = $r['cycle'];
            $res[$i]["offer_status"] = $r['offer_status'];
            $res[$i]["offer"] = $r['offer_status'] == 0 ? 'Inactive' : 'Active';
            $res[$i]["offer_details"] = isset($response)? $response :[];
        }

        return $res;
    }

    public function transaction($user_token, $subscription_id, $paid_amount, $paid_amount_currency, $txn_id, $payment_status) {
        // fetching user by email
        $stmtu = $this->conn->prepare("SELECT id FROM user WHERE user_token = ? and status=10");
        $stmtu->execute([
            $user_token
        ]);
        $count = $stmtu->rowCount();
        $rowu = $stmtu->fetch();
        $time = time();

        $stmt = $this->conn->prepare("insert into transactions (user_id, subscription_id,paid_amount, paid_amount_currency, txn_id, payment_status, created, modified) values (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $rowu['id'],
            $subscription_id,
            $paid_amount,
            $paid_amount_currency,
            $txn_id,
            $payment_status,
            $time,
            $time
        ]);
        $insert = $this->conn->lastInsertId();

        // Check for successful insertion

        if (!empty($insert)) {

            return $res = [
                'order_id' => $insert,
                'status' => "Success"
            ];
        } else {
            // Failed to create user
            return $res = [
                'status' => "Order Failed"
            ];
        }
    }

    public function user_name($user_id) {
        $stmtu = $this->conn->prepare("SELECT name FROM user_details WHERE user_id = ? and status=1");
        $stmtu->execute([
            $user_id
        ]);

        $rowu = $stmtu->fetch();
        $name = isset($rowu['name']) ? $rowu['name'] : NULL;
        return $name;
    }

    public function gifgallery() {

        $stmt = $this->conn->prepare("select * from gallery_category where status = 1");
        $stmt->execute();

        $res = array();
        
        // TODO
        // $task = $stmt->get_result()->fetch_assoc();
        $row = $stmt->fetchAll();
        foreach ($row as $i => $r) {
            $name = str_replace(' ', '_', $r['name']);
            $response = array();
            $stmtr = $this->conn->prepare("SELECT image_link FROM gallery WHERE category_id = ? and status=1");
            $stmtr->execute([
                $r['id']
            ]);

            $rowr = $stmtr->fetchAll();

            foreach ($rowr as $k => $p) {
                $response[$k] = $p['image_link'];
            }
            $res[$i][$name] = $response;
        }

        return $res;
    }

}

?>

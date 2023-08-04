<?php

namespace Src\Controller;

use Src\System\DatabaseMethods;
use Src\Controller\PaymentController;
use Src\Gateway\CurlGatewayAccess;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ExposeDataController
{
    private $dm;

    public function __construct()
    {
        $this->dm = new DatabaseMethods();
    }

    public function genCode($length = 6)
    {
        $digits = $length;
        $first = pow(10, $digits - 1);
        $second = pow(10, $digits) - 1;
        return rand($first, $second);
    }

    public function validateEmail($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_email = htmlentities(htmlspecialchars($input));
        $sanitized_email = filter_var($user_email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL))
            die(json_encode(array("success" => false, "message" => "Invalid email address!" . $sanitized_email)));
        return $user_email;
    }

    public function validateInput($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateCountryCode($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9()+]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validatePassword($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9()+@#.-_=$&!`]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validatePhone($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateText($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateDate($date)
    {
        if (strtotime($date) === false) die("Invalid date!");
        list($year, $month, $day) = explode('-', $date);
        if (checkdate($month, $day, $year)) return $date;
    }

    public function validateImage($files)
    {
        if (!isset($files['file']['error']) || !empty($files["pics"]["name"])) {
            $allowedFileType = ['image/jpeg', 'image/png', 'image/jpg'];
            for ($i = 0; $i < count($files["pics"]["name"]); $i++) {
                $check = getimagesize($files["pics"]["tmp_name"][$i]);
                if ($check !== false && in_array($files["pics"]["type"][$i], $allowedFileType)) {
                    return $files;
                }
            }
        }
        die(json_encode(array("success" => false, "message" => "Invalid file uploaded!")));
    }

    public function validateInputTextOnly($input): array
    {
        if (empty($input)) {
            return array("success" => false, "message" => "Input required!");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z]/', $user_input);

        if ($validated_input) {
            return array("success" => true, "message" => $user_input);
        }

        return array("success" => false, "message" => "Invalid input!");
    }

    public function validateInputTextNumber($input): array
    {
        if (empty($input)) {
            return array("status" => "error", "message" => "required");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9]/', $user_input);

        if ($validated_input) {
            return array("status" => "success", "message" => $user_input);
        }

        return array("status" => "error", "message" => "invalid");
    }

    public function validateYearData($input): array
    {
        if (empty($input) || strtoupper($input) == "YEAR") {
            return array("status" => "error", "message" => "required");
        }

        if ($input < 1990 || $input > 2022) {
            return array("status" => "error", "message" => "invalid");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[0-9]/', $user_input);

        if ($validated_input) {
            return array("status" => "success", "message" => $user_input);
        }

        return array("status" => "error", "message" => "invalid");
    }

    public function validateGrade($input): array
    {
        if (empty($input) || strtoupper($input) == "GRADE") {
            return array("status" => "error", "message" => "required");
        }

        if (strlen($input) < 1 || strlen($input) > 2) {
            return array("status" => "error", "message" => "invalid");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        return array("status" => "success", "message" => $user_input);
    }

    public function getCurrentAdmissionPeriodID()
    {
        //return $this->dm->getData("SELECT * FROM `admission_period` WHERE `active` = 1 OR deadline <> NOW()");
        return $this->dm->getID("SELECT `id` FROM `admission_period` WHERE `active` = 1");
    }

    public function getIPAddress()
    {
        //whether ip is from the share internet  
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from the proxy  
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //whether ip is from the remote address  
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function getDeciveInfo()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function getFormPriceA(int $form_id)
    {
        return $this->dm->getData("SELECT * FROM `forms` WHERE `id` = :fi", array(":fi" => $form_id));
    }

    public function getFormPriceB($form_name)
    {
        return $this->dm->getData("SELECT * FROM `forms` WHERE `name` = :fn", array(":fn" => $form_name));
    }

    /*public function getFormPrice(string $form_type, int $admin_period)
    {
        $sql = "SELECT `amount` FROM `forms` AS p, `form_type` AS t 
        WHERE t.`name` LIKE '%$form_type%' AND p.`admin_period` = :a AND p.`form_type` = t.`id`";
        return $this->dm->getData($sql, array(":a" => $admin_period));
    }*/

    public function getAdminYearCode()
    {
        $sql = "SELECT EXTRACT(YEAR FROM (SELECT `start_date` FROM admission_period WHERE active = 1)) AS 'year'";
        $year = (string) $this->dm->getData($sql)[0]['year'];
        return (int) substr($year, 2, 2);
    }

    public function getAvailableForms()
    {
        return $this->dm->getData("SELECT * FROM `forms`");
    }

    public function getUndergradAndPostgradForms()
    {
        return $this->dm->getData("SELECT f.* FROM `forms` AS f, `form_categories` AS fc 
        WHERE f.form_category = fc.id AND fc.name IN ('UNDERGRADUATE', 'POSTGRADUATE')");
    }

    public function getOtherForms()
    {
        return $this->dm->getData("SELECT f.* FROM `forms` AS f, `form_categories` AS fc 
        WHERE f.form_category = fc.id AND fc.name IN ('UNDERGRADUATE', 'POSTGRADUATE')");
    }

    /*public function getFormTypes()
    {
        return $this->dm->getData("SELECT * FROM `form_type`");
    }*/

    /*public function getPaymentMethods()
    {
        return $this->dm->getData("SELECT * FROM `payment_method`");
    }*/

    /*public function getPrograms($type)
    {
        $sql = "SELECT * FROM `programs` WHERE `type` = :t";
        $param = array(":t" => $type);
        return $this->dm->getData($sql, $param);
    }

    public function getHalls()
    {
        return $this->dm->getData("SELECT * FROM `halls`");
    }*/

    public function sendEmail($recipient_email, $subject, $message)
    {
        //PHPMailer Object
        $mail = new PHPMailer(true); //Argument true in constructor enables exceptions
        //From email address and name
        $mail->From = "rmuicton@rmuictonline.com";
        $mail->FromName = "RMU Forms Online";

        //To address and name
        $mail->addAddress($recipient_email);
        //Send HTML or Plain Text email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        try {
            if ($mail->send()) return array("success" => true);
        } catch (Exception $e) {
            return array("success" => false, "message" => "Mailer Error: " . $mail->ErrorInfo);
        }
    }

    public function sendHubtelSMS($url, $payload)
    {
        $client = getenv('HUBTEL_CLIENT');
        $secret = getenv('HUBTEL_SECRET');
        $secret_key = base64_encode($client . ":" . $secret);

        $httpHeader = array("Authorization: Basic " . $secret_key, "Content-Type: application/json");
        $gateAccess = new CurlGatewayAccess($url, $httpHeader, $payload);
        return $gateAccess->initiateProcess();
    }

    public function sendSMS($to, $message)
    {
        $url = "https://sms.hubtel.com/v1/messages/send";
        $payload = json_encode(array("From" => "RMU", "To" => $to, "Content" => $message));
        return $this->sendHubtelSMS($url, $payload);
    }

    public function sendOTP($to)
    {
        $otp_code = $this->genCode(6);
        $message = 'Your OTP verification code: ' . $otp_code;
        $response = json_decode($this->sendSMS($to, $message), true);
        if (!$response["status"]) $response["otp_code"] = $otp_code;
        return $response;
    }

    public function sendEmailVerificationCode($email)
    {
        $v_code = $this->genCode(6);

        $subject = 'RMU Forms Online Verification Code';
        $message = "Hi,";
        $message .= "<br><p>This is your verification code <b style='font-size: 24px'>" . $v_code . "</b></p>";
        $message .= "<br><p>Codes expires after 30 minutes.</p>";
        $message .= "<br><br><p>Thank you.</p>";

        if (!$this->sendEmail($email, $subject, $message)) return 0;
        return $v_code;
    }

    public function getVendorPhone($vendor_id)
    {
        $sql = "SELECT `country_code`, `phone_number` FROM `vendor_details` WHERE `id`=:i";
        return $this->dm->getData($sql, array(':i' => $vendor_id));
    }

    /**
     * @param int transaction_id //transaction_id
     */
    public function callOrchardGateway($data)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->orchardPaymentController($data);
    }

    /**
     * @param int transaction_id //transaction_id
     */
    public function confirmPurchase(int $transaction_id)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->processTransaction($transaction_id);
    }

    public function processVendorPay($data)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->vendorPaymentProcess($data);
    }

    public function verifyPurchaseStatus($data)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->verifyPurchaseStatus($data);
    }

    public function vendorExist($vendor_id)
    {
        $str = "SELECT `id` FROM `vendor_details` WHERE `id`=:i";
        return $this->dm->getID($str, array(':i' => $vendor_id));
    }

    public function requestLogger($request)
    {
        $query = "INSERT INTO `ussd_request_logs` (`request`) VALUES(:nc)";
        $params = array(":nc" => $request);
        $this->dm->inputData($query, $params);
    }

    /*public function confirmVendorPurchase(int $vendor_id, int $transaction_id)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->verifyVendorPurchase($vendor_id, $transaction_id);
    }*/

    /*public function verifyVendorLogin($username, $password)
    {
        $sql = "SELECT `vendor`, `password` FROM `vendor_login` WHERE `user_name` = :u";
        $data = $this->dm->getData($sql, array(':u' => sha1($username)));
        if (!empty($data)) {
            if (password_verify($password, $data[0]["password"])) {
                return array("success" => true, "message" => $data[0]["vendor"]);
            } else {
                return array("success" => false, "message" => "No match found!");
            }
        }
        return array("success" => false, "message" => "User does not exist!");
    }*/

    /*public function getApplicationInfo(int $transaction_id)
    {
        $sql = "SELECT p.`app_number`, p.`pin_number`, tp.`name`, fp.`amount`, v.`vendor_name`, a.`info` 
        FROM `purchase_detail` AS p, `form_type` AS tp, `forms` AS fp, `vendor_details` AS v, `admission_period` AS a 
        WHERE p.`form_type` = tp.`id` AND p.vendor = v.`id` AND p.`admission_period` = a.`id` AND p.`id` = :i AND 
        fp.`form_type` = tp.`id` AND fp.`admin_period` = a.`id`";
        return $this->dm->getData($sql, array(':i' => $transaction_id));
    }*/
}

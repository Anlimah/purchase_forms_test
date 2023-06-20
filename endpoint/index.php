<?php
session_start();
/*
* Designed and programmed by
* @Author: Francis A. Anlimah
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require "../bootstrap.php";

use Src\Controller\ExposeDataController;

$expose = new ExposeDataController();

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($_GET["url"] == "verifyStepFinal") {
        $arr = array();
        array_push($arr, $_SESSION["step1"], $_SESSION["step2"], $_SESSION["step4"], $_SESSION["step6"], $_SESSION["step7"]);
        echo json_encode($arr);
        //verify all sessions
        //save all user data
        //echo success message
    }
}

// All POST request will be sent here
elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
    // verify customer first and last name details
    if ($_GET["url"] == "verifyStep1") {
        if (isset($_SESSION["_step1Token"]) && !empty($_SESSION["_step1Token"]) && isset($_POST["_v1Token"]) && !empty($_POST["_v1Token"]) && $_POST["_v1Token"] == $_SESSION["_step1Token"]) {
            if (!isset($_SESSION["admin_period"]) || empty($_SESSION["admin_period"])) $_SESSION["admin_period"] = $expose->getCurrentAdmissionPeriodID();

            $_SESSION["step1"] = array(
                "first_name" => $expose->validateInput($_POST["first_name"]),
                "last_name" => $expose->validateInput($_POST["last_name"])
            );
            $_SESSION['step1Done'] = true;
            $data["success"] = true;
            $data["message"] = "step2.php";
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }
    //
    else if ($_GET["url"] == "verifyStep2") {
        if (isset($_SESSION["_step2Token"]) && !empty($_SESSION["_step2Token"]) && isset($_POST["_v2Token"]) && !empty($_POST["_v2Token"]) && $_POST["_v2Token"] == $_SESSION["_step2Token"]) {
            $_SESSION["step2"] = array("email_address" => $expose->validateInput($_POST["email_address"]));

            $v_code = $expose->genCode(6);
            $subject = 'VERIFICATION CODE';
            $message = "Hi " . $_SESSION["step1"]["first_name"] . " " . $_SESSION["step1"]["last_name"] . ",";
            $message .= " <br> Your verification code is <b>" . $v_code . "</b>";

            if ($expose->sendEmail($_SESSION['step2']["email_address"], $subject, $message)) {
                $_SESSION['email_code'] = $v_code;
                $_SESSION['step2Done'] = true;
                $data["success"] = true;
                $data["message"] = "step3.php";
            } else {
                $data["success"] = false;
                $data["message"] = "Error occured while sending email!";
            }
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }
    //
    elseif ($_GET["url"] == "verifyStep3") {
        if (isset($_SESSION["_step3Token"]) && !empty($_SESSION["_step3Token"]) && isset($_POST["_v3Token"]) && !empty($_POST["_v3Token"]) && $_POST["_v3Token"] == $_SESSION["_step3Token"]) {
            if ($_POST["num"]) {
                $otp_code = $expose->validatePhone($_POST["num"]);

                if ($otp_code == $_SESSION['email_code']) {
                    $_SESSION['step3Done'] = true;
                    $data["success"] = true;
                    $data["message"] = "step4.php";
                } else {
                    $data["success"] = false;
                    $data["message"] = "Email verification code provided is incorrect!";
                }
            }
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }
    // verify step 4
    elseif ($_GET["url"] == "verifyStep4") {
        if (isset($_SESSION["_step4Token"]) && !empty($_SESSION["_step4Token"]) && isset($_POST["_v4Token"]) && !empty($_POST["_v4Token"]) && $_POST["_v4Token"] == $_SESSION["_step4Token"]) {
            if (isset($_POST["country"]) && !empty($_POST["country"]) && isset($_POST["phone_number"]) && !empty($_POST["phone_number"])) {

                $country = $expose->validateCountryCode($_POST["country"]);
                $charPos = strpos($country, ")");

                $country_name = substr($country, ($charPos + 2));
                $country_code = substr($country, 1, ($charPos - 1));

                $phone_number = $expose->validatePhone($_POST["phone_number"]);

                $_SESSION["step4"] = array(
                    "country_name" => $country_name,
                    "country_code" => $country_code,
                    "phone_number" => $phone_number,
                );
                $to = $country_code . $phone_number;
                $response = $expose->sendOTP($to);

                if (isset($response["otp_code"])) {
                    $_SESSION['sms_code'] = $response["otp_code"];
                    $_SESSION['step4Done'] = true;
                    $data["success"] = true;
                    $data["message"] = "step5.php";
                    //$data["message"] = $response["statusDescription"];
                } else {
                    $data["success"] = false;
                    $data["message"] = $response["statusDescription"];
                }
            } else {
                $data["success"] = false;
                $data["message"] = "Invalid request! 2";
            }
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request! 1";
        }
        die(json_encode($data));
    }
    // verify step 5
    elseif ($_GET["url"] == "verifyStep5") {
        if (isset($_SESSION["_step5Token"]) && !empty($_SESSION["_step5Token"]) && isset($_POST["_v5Token"]) && !empty($_POST["_v5Token"]) && $_POST["_v5Token"] == $_SESSION["_step5Token"]) {
            if ($_POST["code"]) {
                $otp_code = $expose->validatePhone($_POST["code"]);

                if ($otp_code == $_SESSION['sms_code']) {
                    $_SESSION['step5Done'] = true;
                    $data["success"] = true;
                    $data["message"] = "step6.php";
                } else {
                    $data["success"] = false;
                    $data["message"] = "OTP code provided is incorrect!";
                }
            }
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }

    //
    elseif ($_GET["url"] == "formInfo") {
        if (!isset($_POST["form_id"]) || empty($_POST["form_id"])) {
            die(json_encode(array("success" => false, "message" => "Error: Form has not been set properly in database!")));
        }

        $form_id = $expose->validateInput($_POST["form_id"]);
        $result = $expose->getFormPriceA($form_id);

        if (empty($result)) die(json_encode(array("success" => false, "message" => "Forms' price has not set in the database!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    // verify step 6
    elseif ($_GET["url"] == "verifyStep6") {
        if (isset($_SESSION["_step6Token"]) && !empty($_SESSION["_step6Token"]) && isset($_POST["_v6Token"]) && !empty($_POST["_v6Token"]) && $_POST["_v6Token"] == $_SESSION["_step6Token"]) {
            $form_id = $expose->validateInput($_POST["available_forms"]);
            $amount = $_POST["form_price"];
            $payment_method = $expose->validateText($_POST["payment_method"]);

            if (!empty($amount)) {
                $_SESSION["step6"] = array(
                    "form_id" => $form_id,
                    "amount" => $amount,
                    "pay_method" => $payment_method,
                    "vendor_id" => $_SESSION["vendor_id"]
                );
                $_SESSION['step6Done'] = true;

                if (isset($_SESSION['step1Done']) && isset($_SESSION['step2Done']) && isset($_SESSION['step3Done']) && isset($_SESSION['step4Done']) && isset($_SESSION['step5Done']) && isset($_SESSION['step6Done'])) {
                    if ($_SESSION['step1Done'] == true && $_SESSION['step2Done'] == true && $_SESSION['step3Done'] == true && $_SESSION['step4Done'] == true && $_SESSION['step5Done'] == true && $_SESSION['step6Done'] == true) {
                        $_SESSION["customerData"] = array(
                            "first_name" => $_SESSION["step1"]["first_name"],
                            "last_name" => $_SESSION["step1"]["last_name"],
                            "email_address" => $_SESSION["step2"]["email_address"],
                            "country_name" => $_SESSION["step4"]["country_name"],
                            "country_code" => $_SESSION["step4"]["country_code"],
                            "phone_number" => $_SESSION["step4"]["phone_number"],
                            "form_id" => $_SESSION["step6"]["form_id"],
                            "pay_method" => $_SESSION["step6"]["pay_method"],
                            "amount" => $_SESSION["step6"]["amount"],
                            "vendor_id" => $_SESSION["vendor_id"],
                            "admin_period" => $_SESSION["admin_period"]
                        );
                        $data = $expose->callOrchardGateway($_SESSION["customerData"]);

                        session_unset();
                        session_destroy();
                        $_SESSION = array();

                        if (ini_get("session.use_cookies")) {
                            $params = session_get_cookie_params();
                            setcookie(
                                session_name(),
                                '',
                                time() - 42000,
                                $params["path"],
                                $params["domain"],
                                $params["secure"],
                                $params["httponly"]
                            );
                        }
                    }
                }
            } else {
                $data["success"] = false;
                $data["message"] = "Error occured while processing selected amount!";
            }
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }

    // Resend verification code
    elseif ($_GET["url"] == "resend-code") {

        if (!isset($_POST["resend_code"])) die(json_encode(array("success" => false, "message" => "Invalid request!")));
        if (empty($_POST["resend_code"])) die(json_encode(array("success" => false, "message" => "Missing input!")));

        $code_type = $expose->validateText($_POST["resend_code"]);
        switch ($code_type) {
            case 'sms':
                // For vendor resend otp code
                if (isset($_SESSION["_verifySMSToken"]) && !empty($_SESSION["_verifySMSToken"]) && isset($_POST["_vSMSToken"]) && !empty($_POST["_vSMSToken"]) && $_POST["_vSMSToken"] == $_SESSION["_verifySMSToken"]) {

                    $vendorPhone = $expose->getVendorPhone($_SESSION["vendor_id"]);

                    if (!empty($vendorPhone)) {

                        $to = $vendorPhone[0]["country_code"] . $vendorPhone[0]["phone_number"];
                        $response = $expose->sendOTP($to);

                        if (isset($response["otp_code"])) {
                            $_SESSION['sms_code'] = $response["otp_code"];
                            $_SESSION['verifySMSCode'] = true;
                            $data["success"] = true;
                            $data["message"] = "Verification code resent!";
                        } else {
                            $_SESSION['verifySMSCode'] = false;
                            $data["success"] = false;
                            $data["message"] = $response["statusDescription"];
                        }
                    } else {
                        $data["success"] = false;
                        $data["message"] = "No phone number entry found for this user!";
                    }
                }

                // for user/applicant/online resend otp code
                else if (isset($_SESSION["_step5Token"]) && !empty($_SESSION["_step5Token"]) && isset($_POST["_v5Token"]) && !empty($_POST["_v5Token"]) && $_POST["_v5Token"] == $_SESSION["_step5Token"]) {

                    $to = $_SESSION["step4"]["country_code"] . $_SESSION["step4"]["phone_number"];
                    $response = $expose->sendOTP($to);

                    if (isset($response["otp_code"])) {
                        $_SESSION['sms_code'] = $response["otp_code"];
                        $data["success"] = true;
                        $data["message"] = "Verification code resent!";
                    } else {
                        $data["success"] = false;
                        $data["message"] = $response["statusDescription"];
                    }
                } else {
                    die(json_encode(array("success" => false, "message" => "Invalid OTP SMS request!")));
                }
                break;

            case 'email':
                if (isset($_SESSION["_step3Token"]) && !empty($_SESSION["_step3Token"]) && isset($_POST["_v3Token"]) && !empty($_POST["_v3Token"]) && $_POST["_v3Token"] == $_SESSION["_step3Token"]) {
                    $v_code = $expose->sendEmailVerificationCode($_SESSION['step2']["email_address"]);
                    if (!$v_code) die(json_encode(array("success" => false, "message" => "Failed to resend code!")));
                    $_SESSION['email_code'] = $v_code;
                    $data["success"] = true;
                    $data["message"] = "Verification code resent!";
                } else {
                    die(json_encode(array("success" => false, "message" => "Invalid OTP Email request!")));
                }
                break;
        }
        die(json_encode($data));
    }

    //Online Payment confirmation
    elseif ($_GET["url"] == "confirm") {
        if (isset($_POST["status"]) && !empty($_POST["status"]) && isset($_POST["exttrid"]) && !empty($_POST["exttrid"])) {
            $status = $expose->validateInput($_POST["status"]);
            $transaction_id = $expose->validatePhone($_POST["exttrid"]);
            die(json_encode($expose->verifyPurchaseStatus($transaction_id)));
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }

    //vendor login
    /*elseif ($_GET["url"] == "loginVendor") {
		if (isset($_SESSION["_loginToken"]) && !empty($_SESSION["_loginToken"]) && isset($_POST["_vlToken"]) && !empty($_POST["_vlToken"]) && $_POST["_vlToken"] == $_SESSION["_loginToken"]) {
			if (isset($_POST["username"]) && !empty($_POST["username"])) {
				if (isset($_POST["password"]) && !empty($_POST["password"])) {

					$username = $expose->validateText($_POST["username"]);
					$password = $expose->validatePassword($_POST["password"]);

					$data = $expose->verifyVendorLogin($username, $password);

					if ($data["success"]) {
						$_SESSION["vendor_id"] = $data["message"];
						$vendorPhone = $expose->getVendorPhone($_SESSION["vendor_id"]);

						if (!empty($vendorPhone)) {

							$to = $vendorPhone[0]["country_code"] . $vendorPhone[0]["phone_number"];
							$response = $expose->sendOTP($to);

							if (isset($response["otp_code"])) {
								$_SESSION['sms_code'] = $response["otp_code"];
								$_SESSION['verifySMSCode'] = true;
								$data["success"] = true;
								$data["message"] = "verify.php?verify=vendor";
							} else {
								$_SESSION['verifySMSCode'] = false;
								$data["success"] = false;
								$data["message"] = $response["statusDescription"];
							}
						} else {
							$data["success"] = false;
							$data["message"] = "No phone number entry found for this user!";
						}
					}
				} else {
					$data["success"] = false;
					$data["message"] = "Password field is required!";
				}
			} else {
				$data["success"] = false;
				$data["message"] = "Username field is required!";
			}
		}
		die(json_encode($data));
	}*/

    //After a successfull login, verify vendor mobile phone before redirection to home page
    /*elseif ($_GET["url"] == "verifyVendor") {
		if (isset($_SESSION["_verifySMSToken"]) && !empty($_SESSION["_verifySMSToken"]) && isset($_POST["_vSMSToken"]) && !empty($_POST["_vSMSToken"]) && $_POST["_vSMSToken"] == $_SESSION["_verifySMSToken"]) {
			if (isset($_POST["code"]) && !empty($_POST["code"])) {
				$otp = "";
				foreach ($_POST["code"] as $code) {
					$otp .= $code;
				}

				$otp_code = (int) $expose->validatePhone($otp);

				if ($otp_code == $_SESSION['sms_code']) {
					$_SESSION["admin_period"] = $expose->getCurrentAdmissionPeriodID();
					$_SESSION["SMSLogin"] = true;
					$_SESSION["loginSuccess"] = true;
					$data["success"] = true;
					$data["message"] = "index.php";
				} else {
					$data["success"] = false;
					$data["message"] = "Entry did not match OTP code sent!!";
				}
			} else {
				$data["success"] = false;
				$data["message"] = "Code entries are needed!";
			}
		} else {
			$data["success"] = false;
			$data["message"] = "Invalid request!";
		}
		die(json_encode($data));
	}*/

    //Vendor endpoint
    /*elseif ($_GET["url"] == "vendor") {
		if (isset($_SESSION["_vendor1Token"]) && !empty($_SESSION["_vendor1Token"]) && isset($_POST["_v1Token"]) && !empty($_POST["_v1Token"]) && $_POST["_v1Token"] == $_SESSION["_vendor1Token"]) {
			if (isset($_POST["form_type"]) && isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["country"]) && isset($_POST["phone_number"])) {
				if (!empty($_POST["form_type"]) && !empty($_POST["first_name"]) && !empty($_POST["last_name"]) && !empty($_POST["country"]) && !empty($_POST["phone_number"])) {

					$first_name = $expose->validateText($_POST["first_name"]);
					$last_name = $expose->validateText($_POST["last_name"]);
					$phone_number = $expose->validatePhone($_POST["phone_number"]);
					$country = $expose->validateCountryCode($_POST["country"]);

					$charPos = strpos($country, ")");
					$country_name = substr($country, ($charPos + 2));
					$country_code = substr($country, 1, ($charPos - 1));

					$form_type = $expose->validateInput($_POST["form_type"]);
					$amount = $expose->getFormPrice($form_type, $_SESSION["admin_period"])[0]["amount"];

					if (!empty($amount)) {
						$_SESSION["vendorData"] = array(
							"first_name" => $first_name,
							"last_name" => $last_name,
							"country_name" => $country_name,
							"country_code" => $country_code,
							"phone_number" => $phone_number,
							"email_address" => "",
							"form_type" => $form_type,
							"pay_method" => "CASH",
							"amount" => $amount,
							"vendor_id" => $_SESSION["vendor_id"],
							"admin_period" => $_SESSION["admin_period"]
						);

						if (!empty($_SESSION["vendorData"])) {

							$to = $_SESSION["vendorData"]["country_code"] . $_SESSION["vendorData"]["phone_number"];
							$response = $expose->sendOTP($to);

							if (isset($response["otp_code"])) {
								$_SESSION['sms_code'] = $response["otp_code"];
								$_SESSION['verifySMSCode'] = true;
								$data["success"] = true;
								$data["message"] = "verify.php?verify=customer";
							} else {
								$_SESSION['verifySMSCode'] = false;
								$data["success"] = false;
								$data["message"] = $response["statusDescription"];
							}
						} else {
							$data["success"] = false;
							$data["message"] = "Failed in preparing data submitted!";
						}
					} else {
						$data["success"] = false;
						$data["message"] = "Unset data values!";
					}
				} else {
					$data["success"] = false;
					$data["message"] = "Some required fields might be empty!";
				}
			} else {
				$data["success"] = false;
				$data["message"] = "Invalid inputs!";
			}
		} else {
			$data["success"] = false;
			$data["message"] = "Invalid request!1";
		}
		die(json_encode($data));
	}*/

    //Verify customer phone number before sending Application login details
    elseif ($_GET["url"] == "verifyCustomer") {
        if (isset($_SESSION["_verifySMSToken"]) && !empty($_SESSION["_verifySMSToken"]) && isset($_POST["_vSMSToken"]) && !empty($_POST["_vSMSToken"]) && $_POST["_vSMSToken"] == $_SESSION["_verifySMSToken"]) {
            if (isset($_POST["code"]) && !empty($_POST["code"])) {
                $otp = "";
                foreach ($_POST["code"] as $code) {
                    $otp .= $code;
                }

                $otp_code = (int) $expose->validatePhone($otp);

                if ($otp_code == $_SESSION['sms_code']) {
                    if (isset($_SESSION["vendorData"]) && !empty($_SESSION["vendorData"])) {
                        if ($expose->vendorExist($_SESSION["vendorData"]["vendor_id"])) {
                            $data = $expose->processVendorPay($_SESSION["vendorData"]);
                        } else {
                            $data["success"] = false;
                            $data["message"] = "Process can only be performed by a vendor!";
                        }
                    } else {
                        $data["success"] = false;
                        $data["message"] = "Empty data payload!";
                    }
                } else {
                    $data["success"] = false;
                    $data["message"] = "Entry did not match OTP code sent!";
                }
            } else {
                $data["success"] = false;
                $data["message"] = "Code entries are needed!";
            }
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }

    //Vendor Payment confirmation
    /*elseif ($_GET["url"] == "vendorConfirm") {
		if (isset($_POST["status"]) && !empty($_POST["status"]) && isset($_POST["exttrid"]) && !empty($_POST["exttrid"])) {
			$status = $expose->validatePhone($_POST["status"]);
			$transaction_id = $expose->validatePhone($_POST["exttrid"]);
			$data = $expose->confirmVendorPurchase($_SESSION["vendor_id"], $transaction_id);
		} else {
			$data["success"] = false;
			$data["message"] = "Invalid request! 1";
		}
		die(json_encode($data));
	}*/
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    parse_str(file_get_contents("php://input"), $_PUT);
    die(json_encode($data));
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    parse_str(file_get_contents("php://input"), $_DELETE);
    die(json_encode($data));
} else {
    http_response_code(405);
}

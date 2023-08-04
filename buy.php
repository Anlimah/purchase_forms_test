<?php
session_start();

if (!isset($_SESSION["_purchaseToken"])) {
    $rstrong = true;
    $_SESSION["_purchaseToken"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
    $_SESSION["vendor_type"] = "ONLINE";
    $_SESSION["vendor_id"] = "1665605087";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("inc/head-section.php"); ?>
    <title>Form Purchase | Step 1</title>
</head>

<body class="fluid-container">

    <div id="wrapper">

        <?php require_once("inc/page-nav.php"); ?>

        <div id="flashMessage" class="alert text-center" role="alert" style="display: none;"></div>

        <main class="container flex-container">
            <div class="flex-card">
                <div class="form-card card">

                    <div class="purchase-card-header mb-4">
                        <h1>Purchase Information</h1>
                    </div>

                    <div class="purchase-card-body">
                        <form id="purchaseForm" method="post" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label" for="first-name">First Name</label>
                                <input title="Provide your first name" class="form-control" type="text" name="first-name" id="first-name" placeholder="Type your first name" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="last-name">Last Name</label>
                                <input style="width:100% !important" title="Provide your last name" class="form-control" type="text" name="last-name" id="last-name" placeholder="Type your last name" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="available-forms">Forms Type</label>
                                <select name="available-forms" id="available-forms" title="Select the type of form you want to purchase." class="form-select form-info" required>
                                    <option selected disabled value="">Choose...</option>
                                    <?php
                                    require_once('bootstrap.php');

                                    use Src\Controller\ExposeDataController;

                                    $expose = new ExposeDataController();
                                    $data = $expose->getAvailableForms();
                                    foreach ($data as $fp) {
                                    ?>
                                        <option value="<?= $fp['id'] ?>"><?= $fp['name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4" id="form-cost-display" style="display: none;">

                                <p class="alert alert-info" style="line-height: normal !important;">
                                    <b><span id="form-name"></span></b> forms cost <b> GHc<span id="form-cost"></span></b>.
                                </p>

                                <div class="mb-4">
                                    <label class="form-label" for="payment-method">Choose the mode for payment</label>
                                    <select title="Choose the mode to pay for the selected form" class="form-select form-select-sm" name="payment-method" id="payment-method" required>
                                        <option selected disabled value="">Choose...</option>
                                        <option value="CRD">Credit/Debit Card</option>
                                        <option value="MOM">Mobile Money</option>
                                    </select>
                                </div>

                                <div class="mb-4 mt-4">
                                    <p class="form-label">How do you want to receive voucher details?</p>
                                    <div id="displayVerified" style="display: <?= (isset($_SESSION["verification"]["vStatus"]) && $_SESSION["verification"]["vStatus"] == "success") ? "block" : "none" ?>">
                                        <div class="flex-row justify-space-between">
                                            <p class='text-success'><b id="displayVerifiedContent"></b></p>
                                            <span id="changeVerification" class="text-danger" style="text-decoration: underline; cursor: pointer;"><b>Change</b></span>
                                        </div>
                                    </div>
                                    <div id="verificationTypeSelect" style="display: <?= (isset($_SESSION["verification"]["vStatus"]) && $_SESSION["verification"]["vStatus"] == "success") ? "none" : "block" ?>;">
                                        <input type="radio" class="btn-check verificationType" name="verification-type" id="smsVoucher" value="sms" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="smsVoucher">SMS</label>

                                        <input type="radio" class="btn-check verificationType" name="verification-type" id="emailVoucher" value="email" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="emailVoucher">Email</label>
                                    </div>

                                </div>

                                <input type="hidden" name="form-price" id="form-price" value="0">
                                <input type="hidden" name="form-type" id="form-type" value="0">
                                <input type="hidden" name="_vPToken" value="<?= $_SESSION["_purchaseToken"]; ?>">
                                <input type="hidden" name="verifiedAccount" id="verifiedAccount" value="<?php
                                                                                                        if (isset($_SESSION["verification"]["type"]) && $_SESSION["verification"]["type"] == "sms") echo "(" . $_SESSION["verification"]["data"]["country_code"] . ") " . $_SESSION["verification"]["data"]["phone_number"] . " verified.";
                                                                                                        elseif (isset($_SESSION["verification"]["type"]) && $_SESSION["verification"]["type"] == "email") echo $_SESSION["verification"]["data"]["email_address"] . " verified.";
                                                                                                        ?>">
                                <button class="btn btn-primary" type="submit" id="submitBtn" style="width:100%" disabled>Pay</button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="emailVoucherVerificationModal" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="emailVoucherVerificationModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered madal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="emailVoucherVerificationModalTitle">Email Address Verification</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="email-message"></div>

                            <div id="emailSuccessVerificationMessage" style="width:100%; display: none">
                                <div style="width:100%; display: flex; flex-direction:column; align-items:center">
                                    <img id="image" src="assets/images/icons8-success-96.png" alt="successful">
                                    <div class="purchase-card-header mt-4 mb-2" style="border-bottom: none !important;">
                                        <h1>Verification successful</h1>
                                    </div>
                                    <p><b>Pay</b> button is now active. Close this popover to proceed.</p>
                                </div>
                            </div>

                            <div id="emailCodeVerifyBoxNumber" class="mb-4">
                                <form action="#" method="post" id="emailCodeVerificationFormNumber">
                                    <p class="mb-4" style="color:#003262;">
                                        We'll send you a message with a 6 digit code to verify your email address. Please provide your email address.<br>
                                    </p>
                                    <div class="mb-4 flex-row">
                                        <div style="flex-grow: 9; margin-right: 5px">
                                            <input title="Provide your email address" class="form-control" type="email" name="email-address" id="email-address" placeholder="example@company.com" required>
                                        </div>
                                        <button style="flex-grow: 2;" class="btn btn-primary" type="submit" id="verifyEmailBtn">Send</button>
                                    </div>
                                </form>
                            </div>

                            <div id="emailCodeVerifyBoxCode" style="width: 100%; display: none;">
                                <form action="" method="post" id="emailVerificationForm">
                                    <div style="width: 100%; display:flex; flex-direction:column; align-items:center">
                                        <p class="mb-4" style="color:#003262;">A 6 digit code has been sent to your email address. Check your inbox and enter the code</p>
                                        <div class="mb-4 flex-row" style="width:100%; justify-content: center">
                                            <input required name="code" id="emailVerificationCode" class="form-control" type="text" maxlength="6" style="text-align:center;" placeholder="XXXXXX">
                                            <span id="emailVerificationCodeLoadArea" style="display: none; margin-left: 5px"></span>
                                        </div>
                                        <div class="mb-4 flex-row align-items-baseline justify-space-between" style="width: 100%;">
                                            <a href="javascript:void()" id="change-ea">Change email address</a>
                                            <span id="email-timer" class="timer" style="display: none;" data-timerType="email"></span>
                                            <button id="email-resend-code" class="resend-code btn btn-outline-dark btn-xs" data-resendType="email">Resend code</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="smsVoucherInformationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="smsVoucherVerificationModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered madal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="smsVoucherInformationModalTitle">Phone Number Verification</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="sms-message"></div>

                            <div id="smsSuccessVerificationMessage" style="width:100%; display: none">
                                <div style="width:100%; display: flex; flex-direction:column; align-items:center">
                                    <img id="image" src="assets/images/icons8-success-96.png" alt="successful">
                                    <div class="purchase-card-header mt-4 mb-2" style="border-bottom: none !important;">
                                        <h1>Verification successful</h1>
                                    </div>
                                    <p><b>Pay</b> button is now active. Close this popover to proceed.</p>
                                </div>
                            </div>

                            <div id="smsCodeVerifyBoxNumber" class="mb-4">
                                <form action="#" method="post" id="smsCodeVerificationFormNumber">
                                    <p class="mb-4" style="color:#003262;">
                                        We'll send you an OTP message with a 6 digit code to verify your phone number. Please provide your phone number.<br>
                                        <span class="text-danger"><b>Note:</b> We don't accept VoIP or Skype numbers.</span>
                                    </p>
                                    <div class="mb-2 flex-row">
                                        <div class="col-md-6 me-2">
                                            <label class="form-label" for="country-code">Country Code</label>
                                            <select required name="country-code" id="country-code" title="Choose country and country code" class="form-select form-control country-code">
                                                <option hidden value="">Choose...</option>
                                                <?php
                                                require_once('inc/page-data.php');
                                                foreach (COUNTRIES as $cn) {
                                                    echo '<option value="(' . $cn["code"] . ') ' . $cn["name"] . '">(' . $cn["code"] . ') ' . $cn["name"] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="phone-number">Phone Number</label>
                                            <input required name="phone-number" id="phone-number" maxlength="11" title="Provide your Provide Number" class="form-control" type="tel" placeholder="12345678901">
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2 " style="width: 100% !important;">
                                        <button class="btn btn-primary form-control" type="submit" id="verifySMSBtn">Send</button>
                                    </div>
                                </form>
                            </div>

                            <div id="smsCodeVerifyBoxCode" style="width: 100%; display: none;">
                                <form action="" method="post" id="smsVerificationForm">
                                    <div style="width: 100%; display:flex; flex-direction:column; align-items:center">
                                        <p class="mb-4" style="color:#003262;">A 6 digit code has been sent to your phone number. Check your inbox and enter the code</p>
                                        <div class="mb-4 flex-row" style="width:100%; justify-content: center">
                                            <input required name="code" id="smsVerificationCode" class="form-control" type="text" maxlength="6" style="text-align:center;" placeholder="XXXXXX">
                                            <span id="smsVerificationCodeLoadArea" style="display: none; margin-left: 5px"></span>
                                        </div>
                                        <div class="mb-4 flex-row align-items-baseline justify-space-between" style="width: 100%;">
                                            <a href="javascript:void()" id="change-pn">Change number</a>
                                            <span id="sms-timer" class="timer" style="display: none;" data-timerType="sms"></span>
                                            <button id="sms-resend-code" class="resend-code btn btn-outline-dark btn-xs" data-resendType="sms">Resend code</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </main>

        <?php require_once("inc/page-footer.php"); ?>
    </div>


    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {

            var triggeredBy = 0;

            if ($("#verifiedAccount").val() != "") $("#displayVerifiedContent").text($("#verifiedAccount").val());

            $(".form-info").change("blur", function() {
                $.ajax({
                    type: "POST",
                    url: "endpoint/formInfo",
                    data: {
                        form_id: this.value,
                    },
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            $("#form-cost-display").slideDown();
                            $("#form-name").text(result.message[0]["name"]);
                            $("#form-cost").text(result.message[0]["amount"]);
                            $("#form_price").val(result.message[0]["amount"]);
                            $("#form_type").val(result.message[0]["form_category"]);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#changeVerification").on("click", function() {
                $("#verificationTypeSelect").slideToggle();
            });

            $(".verificationType").on("click", function() {
                if ($(this).attr("id") == "smsVoucher") $("#smsVoucherInformationModal").modal("toggle");
                if ($(this).attr("id") == "emailVoucher") $("#emailVoucherVerificationModal").modal("toggle");
            });

            $("#smsCodeVerificationFormNumber").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 1;

                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyUserPhoneNumber",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            $("#smsCodeVerifyBoxNumber").slideUp();
                            $("#smsCodeVerifyBoxCode").slideDown();
                            flashMessage("sms-message", "alert-success", result.message);
                        } else {
                            flashMessage("sms-message", "alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#smsVerificationCode").on("keyup", function(e) {
                if (this.value.length == 6) $("#smsVerificationForm").submit();
            });

            $("#smsVerificationForm").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 2;

                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyCode",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            $("#submitBtn").prop("disabled", false);
                            $("#smsCodeVerifyBoxNumber").slideUp();
                            $("#smsCodeVerifyBoxCode").slideUp();
                            $("#smsSuccessVerificationMessage").fadeIn(1000);
                            $("#displayVerified").slideDown();
                            $("#displayVerifiedContent").html(result.vData + " verified.");
                            $("#verificationTypeSelect").slideDown();
                        } else {
                            flashMessage("sms-message", "alert-danger", result.message);
                            $("#submitBtn").prop("disabled", true);
                            $("#smsVerificationCode").focus();
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
                return;
            });

            $("#change-pn").on("click", function() {
                $("#smsCodeVerifyBoxNumber").slideDown();
                $("#smsCodeVerifyBoxCode").slideUp();
            });

            $("#emailCodeVerificationFormNumber").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 3;

                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyUserEmailAddress",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            $("#emailCodeVerifyBoxNumber").slideUp();
                            $("#emailCodeVerifyBoxCode").slideDown();
                            flashMessage("email-message", "alert-success", result.message);
                        } else {
                            flashMessage("email-message", "alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#emailVerificationCode").on("keyup", function(e) {
                if (this.value.length == 6) $("#emailVerificationForm").submit();
            });

            $("#emailVerificationForm").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 4;

                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyCode",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            $("#submitBtn").prop("disabled", false);
                            $("#emailCodeVerifyBoxNumber").slideUp();
                            $("#emailCodeVerifyBoxCode").slideUp();
                            $("#emailSuccessVerificationMessage").fadeIn(1000);
                            $("#displayVerified").slideDown();
                            $("#displayVerifiedContent").html(<?= isset($_SESSION["verification"]["data"]["email_address"]) ? $_SESSION["verification"]["data"]["email_address"] . " verified." : "" ?>);
                            $("#verificationTypeSelect").slideDown();
                        } else {
                            flashMessage("email-message", "alert-danger", result.message);
                            $("#submitBtn").prop("disabled", true);
                            $("#emailVerificationCode").focus();
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
                return;
            });

            $("#change-ea").on("click", function() {
                $("#emailCodeVerifyBoxNumber").slideDown();
                $("#emailCodeVerifyBoxCode").slideUp();
            });

            var count = 60;
            var intervalId = setInterval(() => {
                $(".timer").html("Resend code <b>(" + count + " sec)</b>");
                count = count - 1;
                if (count <= 0) {
                    clearInterval(intervalId);
                    $('.timer').hide();
                    return;
                }
            }, 1000); //1000 will  run it every 1 second

            $(".resend-code").click(function() {
                triggeredBy = 5;
                var rtType = this.dataset.resendtype;

                $.ajax({
                    type: "POST",
                    url: "endpoint/resend-code",
                    success: function(result) {
                        console.log(result);
                        //$("#num1").focus();
                        if (result.success) {
                            flashMessage(rtType + "-message", "alert-success", result.message);

                            clearInterval(intervalId);
                            $("#" + rtType + "-timer").show();
                            $("#" + rtType + "-resend-code").hide();
                            $("#" + rtType + "-resend-code").attr("disabled", true);

                            count = 60;
                            intervalId = setInterval(() => {
                                $("#" + rtType + "-timer").html("Resend code <b>(" + count + " sec)</b>");
                                count = count - 1;
                                if (count <= 0) {
                                    clearInterval(intervalId);
                                    $("#" + rtType + "-timer").hide();
                                    $("#" + rtType + "-resend-code").show();
                                    $("#" + rtType + "-resend-code").attr("disabled", false);
                                    return;
                                }
                            }, 1000);
                        } else {
                            flashMessage(rtType + "-message", "alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#step1Form").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 6;

                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyStep1",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            window.location.href = result.message;
                        } else {
                            flashMessage("alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy == 1) $("#verifySMSBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                    if (triggeredBy == 2) {
                        $("#smsVerificationCode").prop("disabled", true);
                        $("#smsVerificationCodeLoadArea").show().html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Verifying...');
                    }
                    if (triggeredBy == 3) $("#verifyEmailBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Sending...');
                    if (triggeredBy == 4) {
                        $("#emailVerificationCode").prop("disabled", true);
                        $("#emailVerificationCodeLoadArea").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Verifying...');
                    }
                    if (triggeredBy == 5) $(".resend-code").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Sending...');
                    if (triggeredBy == 6) $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Processing...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 1) $("#verifySMSBtn").prop("disabled", false).html('Send');
                    if (triggeredBy == 2) {
                        $("#smsVerificationCode").prop("disabled", false);
                        $("#smsVerificationCodeLoadArea").hide().html('');
                    }
                    if (triggeredBy == 3) $("#verifyEmailBtn").prop("disabled", false).html('Send');
                    if (triggeredBy == 4) {
                        $("#emailVerificationCode").prop("disabled", false);
                        $("#emailVerificationCodeLoadArea").hide().html('');
                    }
                    if (triggeredBy == 5) $(".resend-code").prop("disabled", false).html('Resend code');
                    if (triggeredBy == 6) $("#submitBtn").prop("disabled", false).html('Pay');
                }
            });
        });
    </script>
</body>

</html>
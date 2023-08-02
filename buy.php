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
                                        <option value="" hidden>Choose...</option>
                                        <option value="CRD">Credit/Debit Card</option>
                                        <option value="MOM" selected>Mobile Money</option>
                                    </select>
                                </div>

                                <div class="mb-4 mt-4">
                                    <p class="form-label">How do you want to receive voucher details?</p>

                                    <input type="radio" class="btn-check verificationType" name="verification-type" id="smsVoucher" value="sms" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="smsVoucher">SMS</label>

                                    <input type="radio" class="btn-check verificationType" name="verification-type" id="emailVoucher" value="email" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="emailVoucher">Email</label>
                                </div>

                                <input type="hidden" name="form-price" id="form-price" value="0">
                                <input type="hidden" name="form-type" id="form-type" value="0">
                                <input type="hidden" name="_vPToken" value="<?= $_SESSION["_purchaseToken"]; ?>">

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
                            <h1 class="modal-title fs-5" id="emailVoucherVerificationModalTitle">Purchase Information</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div class="msg-info" style="width:100%; display: flex; flex-direction:column; align-items:center">
                                <img id="image" src="assets/images/icons8-success-96.png" alt="successful">
                                <p id="message">Verification successful</p>
                            </div>

                            <div id="emailCodeVerifyInputBoxEmail">
                                <label class="form-label" for="email_addr">Provide your email address</label>
                                <div class="mb-4" style="display: flex;">
                                    <div style="flex-grow: 8; margin-right: 5px">
                                        <input title="Provide your email address" class="form-control" type="email" name="email_address" id="email_address" placeholder="surname@gmail.com" required>
                                    </div>
                                    <button style="flex-grow: 3;" class="btn btn-primary" type="button" id="verifyEmailBtn">
                                        <span class="bi bi-send">Send Code</span>
                                    </button>
                                </div>
                            </div>

                            <div id="emailCodeVerifyInputBoxCode" style="display: none;">
                                <p class="mb-2" style="color:#003262;">
                                    A 6 digit code has been sent to the provide email address. Check you inbox and enter the code
                                </p>
                                <div class="mb-4" style="width:100%; display: flex; flex-direction:row; align-items:baseline; justify-content:space-around">
                                    <input name="verification-code" class="form-control verification-code" data-verification-code="sms" type="text" maxlength="6" style="text-align:center;" placeholder="XXXXXX" required>
                                </div>
                                <div class="purchase-card-footer flex-row align-items-baseline justify-space-between" style="width: 100%;">
                                    <a href="step2.php">Change email address</a>
                                    <span class="timer" style="display: none;"></span>
                                    <button class="resend-code btn btn-outline-dark btn-xs">Resend code</button>
                                </div>
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

                            <div class="success-verification" style="width:100%; display: none">
                                <div style="width:100%; display: flex; flex-direction:column; align-items:center">
                                    <img id="image" src="assets/images/icons8-success-96.png" alt="successful">
                                    <p id="message">Verification successful</p>
                                </div>
                            </div>

                            <div id="smsCodeVerifyBoxNumber" class="mb-4">
                                <p class="mb-4" style="color:#003262;">
                                    We'll send you an OTP message with a code to verify your phone number. Please provide your phone number for verification.<br>
                                    <span class="text-danger"><b>Note:</b> We don't accept VoIP or Skype numbers.</span>
                                </p>
                                <form action="#" method="post" id="smsCodeVerificationFormNumber" style="margin: 0 !important;">
                                    <div class="mb-2 row">
                                        <div class="col-md-6 mb-2">
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
                                        <div class="col-md-6  mb-2">
                                            <label class="form-label" for="phone-number">Phone Number</label>
                                            <input required name="phone-number" id="phone-number" maxlength="11" title="Provide your Provide Number" class="form-control" type="tel" placeholder="12345678901">
                                        </div>
                                        <div class="col-md-12 mb-2 " style="width: 100% !important;">
                                            <button class="btn btn-primary form-control" type="submit" id="verifySMSBtn">
                                                <span class="bi bi-send"> Send Code</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="mb-4" id="smsCodeVerifyBoxCode" style="width: 100%; display:none;">
                                <div style="width: 100%; display:flex; flex-direction:column; align-items:center">
                                    <p class="mb-4">Enter the 6 digits verification code here.</p>
                                    <div class="mb-4">
                                        <input required name="verification-code" class="form-control verification-code" type="text" data-verification-code="sms" maxlength="6" style="text-align:center;" placeholder="XXXXXX">
                                    </div>
                                    <div class="purchase-card-footer flex-row align-items-baseline justify-space-between" style="width: 100%;">
                                        <a href="javascript:void()" id="change-pn">Change number</a>
                                        <span class="timer" style="display: none;" data-timer="sms"></span>
                                        <button class="resend-code btn btn-outline-dark btn-xs" data-resend-data="sms">Resend code</button>
                                    </div>
                                </div>
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

            $(".verificationType").on("click", function() {
                if ($(this).attr("id") == "smsVoucher") $("#smsVoucherInformationModal").modal("toggle");
                if ($(this).attr("id") == "emailVoucher") $("#emailVoucherVerificationModal").modal("toggle");
            });

            $("#smsCodeVerificationFormNumber").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 1;

                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyStep4",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            $("#smsCodeVerifyBoxNumber").slideUp();
                            $("#smsCodeVerifyBoxCode").slideDown();
                            flashMessage("alert-success", result.message);
                        } else {
                            flashMessage("alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $(".verification-code").on("keyup", function(e) {
                if (this.value.length == 6) {
                    triggeredBy = 2;
                    data = {
                        code: $(this).val()
                    };
                    $(".verification-code").prop("disabled", true);

                    $.ajax({
                        type: "POST",
                        url: "endpoint/verifyCode",
                        data: data,
                        success: function(result) {
                            console.log(result);
                            if (result.success) {
                                flashMessage("alert-success", result.message);
                                $("#submitBtn").prop("disabled", false);
                                $(".verification-code").prop("disabled", false).val("");
                                $(".verification-code").blur();
                                $("#smsCodeVerifyBoxNumber").slideUp();
                                $("#smsCodeVerifyBoxCode").slideUp();
                                $(".success-verification").fadeIn(2000);
                            } else {
                                flashMessage("alert-danger", result.message);
                                $("#submitBtn").prop("disabled", true);
                                $(".verification-code").prop("disabled", false);
                                $(".verification-code").focus();
                            }
                        },
                        error: function(error) {
                            console.log(error.statusText);
                        }
                    });
                    return;
                }
            });

            $(".form-info").change("blur", function() {
                triggeredBy = 3;
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

            $("#change-pn").on("click", function() {
                $("#smsCodeVerifyBoxNumber").slideDown();
                $("#smsCodeVerifyBoxCode").slideUp();
            })

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
                triggeredBy = 4;

                $.ajax({
                    type: "POST",
                    url: "endpoint/resend-code",
                    success: function(result) {
                        console.log(result);
                        //$("#num1").focus();
                        if (result.success) {
                            flashMessage("alert-success", result.message);

                            clearInterval(intervalId);
                            $(".timer").show();
                            $(".resend-code").hide();
                            $('.resend-code').attr("disabled", true);

                            count = 60;
                            intervalId = setInterval(() => {
                                $(".timer").html("Resend code <b>(" + count + " sec)</b>");
                                count = count - 1;
                                if (count <= 0) {
                                    clearInterval(intervalId);
                                    $('.timer').hide();
                                    $(".resend-code").show();
                                    $('.resend-code').attr("disabled", false);
                                    return;
                                }
                            }, 1000);
                        } else {
                            flashMessage("alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#step1Form").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 5;

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
                    if (triggeredBy == 1) $("#smsCodeVerificationFormNumber").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                    if (triggeredBy == 2) $("#verification-code").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...');
                    if (triggeredBy == 4) $(".resend-code").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                    if (triggeredBy == 4) $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 1) $("#smsCodeVerificationFormNumber").prop("disabled", false).html('<span class="bi bi-send" role="status" aria-hidden="true"> Send</span>');
                    if (triggeredBy == 2) $("#verification-code").prop("disabled", false);
                    if (triggeredBy == 4) $("#submitBtn").prop("disabled", false).html('Pay');
                }
            });
        });
    </script>
</body>

</html>
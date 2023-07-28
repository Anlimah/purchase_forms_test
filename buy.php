<?php
session_start();

if (!isset($_SESSION["_step1Token"])) {
    $rstrong = true;
    $_SESSION["_step1Token"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
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

        <div id="flashMessage" class="alert text-center" role="alert"></div>

        <main class="container flex-container">
            <div class="flex-card">
                <div class="form-card card">

                    <div class="purchase-card-header mb-4">
                        <h1>Purchase Information</h1>
                    </div>

                    <div class="purchase-card-body">
                        <form id="step1Form" method="post" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label" for="first_name">First Name</label>
                                <input title="Provide your first name" class="form-control" type="text" name="first_name" id="first_name" placeholder="Type your first name" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="last_name">Last Name</label>
                                <input style="width:100% !important" title="Provide your last name" class="form-control" type="text" name="last_name" id="last_name" placeholder="Type your last name" required>
                            </div>

                            <input type="hidden" name="_v1Token" value="<?= $_SESSION["_step1Token"]; ?>">

                            <div class="mb-4">
                                <label class="form-label" for="gender">Forms Type</label>
                                <select name="available_forms" id="available_forms" title="Select the type of form you want to purchase." class="form-select form-info" required>
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
                                    <label class="form-label" for="payment_method">Choose the mode for payment</label>
                                    <select title="Choose the mode to pay for the selected form" class="form-select form-select-sm" name="payment_method" id="payment_method" required>
                                        <option value="" hidden>Choose...</option>
                                        <option value="CRD">Credit/Debit Card</option>
                                        <option value="MOM" selected>Mobile Money</option>
                                    </select>
                                </div>

                                <input type="hidden" name="form_price" id="form_price" value="0">
                                <input type="hidden" name="form_type" id="form_type" value="0">

                                <div class="mb-4 mt-4">
                                    <label class="form-label" for="last_name">How do you want to receive voucher details?</label>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="verificationType" name="verificationType" id="smsVoucher" value="sms">
                                        <label class="form-check-label" for="smsVoucher">SMS</label>
                                    </div>
                                    <div class="form-check form-check-inline" data-bs-toggle="modal" data-bs-target="#emailVoucherVerificationModal">
                                        <input type="radio" class="verificationType" name="verificationType" id="emailVoucher" value="email">
                                        <label class="form-check-label" for="emailVoucher">Email</label>
                                    </div>
                                </div>

                                <!--<button class="btn btn-primary" type="submit" id="submitBtn" style="width:100%" disabled>Pay</button>-->

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="emailVoucherVerificationModal" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="emailVoucherVerificationModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="emailVoucherVerificationModalTitle">Purchase Information</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label" for="email_addr">Provide your email address</label>

                            <div class="mb-4" style="display: flex;">
                                <div style="flex-grow: 8; margin-right: 5px">
                                    <input title="Provide your email address" class="form-control" type="email" name="email_address" id="email_address" placeholder="surname@gmail.com" required>
                                </div>
                                <button style="flex-grow: 3;" class="btn btn-primary" type="button" id="verifyEmailBtn">
                                    <span class="bi bi-send">Send Code</span>
                                </button>
                            </div>

                            <div id="emailVoucherVerificationInputBox">
                                <p class="mb-2" style="color:#003262;">
                                    A 6 digit code has been sent to the provide email address. Check you inbox and enter the code
                                </p>

                                <div class="mb-4" style="width:100%; display: flex; flex-direction:row; align-items:baseline; justify-content:space-around">
                                    <input class="form-control num" type="text" maxlength="6" style="text-align:center;" name="num" id="num" placeholder="XXXXXX" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="smsVoucherInformationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="smsVoucherVerificationModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="smsVoucherInformationModalTitle">Purchase Information</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-4">
                                <p class="mb-4" style="color:#003262;">
                                    We'll send you an OTP message with a code to verify your phone number.<br>
                                    <span class="text-danger"><b>Note:</b> We don't accept VoIP or Skype numbers.</span>
                                </p>
                                <label class="form-label" for="phone-number">Phone Number</label>
                                <div class="mb-2 row">
                                    <div style="margin-right: 10px;" class="col">
                                        <select title="Choose country and country code" class="form-select country-code" name="country" id="country" required>
                                            <option hidden disabled value="">Choose...</option>
                                            <?php
                                            require_once('inc/page-data.php');
                                            foreach (COUNTRIES as $cn) {
                                                echo '<option value="(' . $cn["code"] . ') ' . $cn["name"] . '">(' . $cn["code"] . ') ' . $cn["name"] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <input maxlength="11" title="Provide your Provide Number" class="form-control" type="tel" name="phone_number" id="phone_number" placeholder="12345678901" required>
                                    </div>
                                    <button class="btn btn-primary col" type="submit" id="verifySMSBtn">
                                        <span class="bi bi-send"> Send Code</span>
                                    </button>
                                </div>
                            </div>

                            <div id="smsVoucherVerificationInputBox">
                                <p class="mb-4">Enter the verification code here.</p>
                                <div class="mb-4">
                                    <input class="form-control num" type="text" maxlength="4" style="text-align:center;" name="code" id="code" placeholder="XXXX" required>
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

            $(".verificationType").on("click", function() {
                if ($(this).attr("id") == "smsVoucher") $("#smsVoucherInformationModal").modal("toggle");
            })

            $("#step1Form").on("submit", function(e) {
                e.preventDefault();
                //window.location.href = "purchase_step2.php";
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

            $("#step1Form").on("submit", function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyStep2",
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

            var triggeredBy = 0;

            var count = 60;
            var intervalId = setInterval(() => {
                $("#timer").html("Resend code <b>(" + count + " sec)</b>");
                count = count - 1;
                if (count <= 0) {
                    clearInterval(intervalId);
                    $('#timer').hide();
                    $('#resend-code').removeClass("hide").addClass("display");
                    return;
                }
            }, 1000); //1000 will  run it every 1 second

            $("#resend-code").click(function() {
                triggeredBy = 1;

                $.ajax({
                    type: "POST",
                    url: "endpoint/resend-code",
                    data: {
                        resend_code: "email",
                        _v3Token: $("#_v3Token").val(),
                    },
                    success: function(result) {
                        console.log(result);
                        $("#num1").focus();
                        if (result.success) {
                            flashMessage("alert-success", result.message);

                            clearInterval(intervalId);
                            $("#timer").show();
                            $('#resend-code').removeClass("display").addClass("hide");

                            count = 60;
                            intervalId = setInterval(() => {
                                $("#timer").html("Resend code <b>(" + count + " sec)</b>");
                                count = count - 1;
                                if (count <= 0) {
                                    clearInterval(intervalId);
                                    $('#timer').hide();
                                    $('#resend-code').removeClass("hide").addClass("display").attr("disabled", false);
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
                triggeredBy = 2;
                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyStep3",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            window.location.href = result.message;
                        } else {
                            alert(result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#step1Form").on("submit", function(e) {
                e.preventDefault();
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

            var triggeredBy = 0;

            var count = 1;
            var intervalId = setInterval(() => {
                $("#timer").html("Resend code <b>(" + count + " sec)</b>");
                count = count - 1;
                if (count <= 0) {
                    clearInterval(intervalId);
                    $('#timer').hide();
                    $('#resend-code').removeClass("hide").addClass("display");
                    return;
                }
            }, 1000); //1000 will  run it every 1 second

            $("#resend-code").click(function() {
                triggeredBy = 60;

                let data = {
                    resend_code: "sms",
                    _v5Token: $("#_v5Token").val()
                };

                $.ajax({
                    type: "POST",
                    url: "endpoint/resend-code",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            flashMessage("alert-success", result.message);

                            clearInterval(intervalId);
                            $("#timer").show();
                            $('#resend-code').removeClass("display").addClass("hide");

                            count = 60;
                            intervalId = setInterval(() => {
                                $("#timer").html("Resend code <b>(" + count + " sec)</b>");
                                count = count - 1;
                                if (count <= 0) {
                                    clearInterval(intervalId);
                                    $('#timer').hide();
                                    $('#resend-code').removeClass("hide").addClass("display").attr("disabled", false);
                                    return;
                                }
                            }, 1000); /**/
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
                triggeredBy = 2;
                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyStep5",
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
                            alert(result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $("#step1Form").on("submit", function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "endpoint/verifyStep6",
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
                            $(':input[type="submit"]').prop('disabled', false);
                        }
                    },
                    error: function(error) {
                        console.log(error.statusText);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    $("#submitBtn").prop("disabled", false).html('Continue');
                }
            });
        });
    </script>
</body>

</html>
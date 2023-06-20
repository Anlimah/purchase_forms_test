<?php
session_start();
if (isset($_SESSION['step2Done']) && $_SESSION['step2Done'] == true && isset($_SESSION["vendor_id"]) && !empty($_SESSION["vendor_id"]) && $_SESSION["vendor_type"] == "ONLINE") {
    if (!isset($_SESSION["_step3Token"])) {
        $rstrong = true;
        $_SESSION["_step3Token"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
    }
} else {
    header('Location: step2.php');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("inc/head-section.php"); ?>
    <title>Form Purchase | Step 3</title>
</head>

<body class="fluid-container">

    <div id="wrapper">

        <?php require_once("inc/page-nav.php"); ?>

        <div id="flashMessage" class="alert text-center" role="alert"></div>

        <main class="container flex-container">
            <div class="flex-card">
                <div class="form-card card">
                    <div class="purchase-card-header">
                        <h1>Verify Email Address</h1>
                    </div>

                    <div class="purchase-card-step-info">
                        <span class="step-capsule">Step 3 of 6</span>
                    </div>

                    <hr style="color:#999">

                    <div class="purchase-card-body">
                        <form action="#" id="step1Form" method="post" enctype="multipart/form-data" style="margin: 0px 12%;">
                            <p class="mb-4" style="color:#003262;">
                                A 6 digit code has been sent to the email <?= $_SESSION["step2"]["email_address"] ?>. Enter the code
                            </p>
                            <div class="mb-4" style="width:100%; display: flex; flex-direction:row; align-items:baseline; justify-content:space-around">
                                <input class="form-control num" type="text" maxlength="6" style="text-align:center;" name="num" id="num" placeholder="XXXX" required>
                            </div>
                            <button class="btn btn-primary mb-4" type="submit" id="submitBtn" style="padding: 10px 10px; width:100%">Verify</button>
                            <input type="hidden" name="_v3Token" id="_v3Token" value="<?= $_SESSION["_step3Token"]; ?>">
                        </form>
                    </div>

                    <div class="purchase-card-footer flex-row align-items-baseline justify-space-between" style="width: 100%;">
                        <a href="step2.php">Change email address</a>
                        <span id="timer"></span>
                        <button id="resend-code" class="btn btn-outline-dark btn-xs hide">Resend code</button>
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

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy == 1) $("#resend-code").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> sending...');
                    else $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 1) $("#resend-code").prop("disabled", false).html('Resend code');
                    else $("#submitBtn").prop("disabled", false).html('Verify');
                }
            });

            $("#num1").focus();

            $(".num").on("keyup", function() {
                if (this.value == 6) {
                    $(this).next(":input").focus().select(); //.val(''); and as well clesr
                }
            });

            $("input[type='text']").on("click", function() {
                $(this).select();
            });
        });
    </script>
</body>

</html>
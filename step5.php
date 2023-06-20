<?php
session_start();
if (isset($_SESSION['step4Done']) && $_SESSION['step4Done'] == true && isset($_SESSION["vendor_id"]) && !empty($_SESSION["vendor_id"]) && $_SESSION["vendor_type"] == "ONLINE") {
    if (!isset($_SESSION["_step5Token"])) {
        $rstrong = true;
        $_SESSION["_step5Token"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
    }
} else {
    header('Location: step4.php');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("inc/head-section.php"); ?>
    <title>Form Purchase | Step 5</title>
</head>

<body class="fluid-container">

    <div id="wrapper">

        <?php require_once("inc/page-nav.php"); ?>

        <div id="flashMessage" class="alert text-center" style="margin-bottom: 0 !important;" role="alert"></div>
        <div class="clearfix"></div>

        <main class="container flex-container">
            <div class="flex-card">
                <div class="form-card card">
                    <div class="purchase-card-header">
                        <h1>Verify Phone Number</h1>
                    </div>

                    <div class="purchase-card-step-info">
                        <span class="step-capsule">Step 5 of 6</span>
                    </div>

                    <hr style="color:#999">

                    <div class="purchase-card-body">
                        <form action="#" id="step1Form" method="post" enctype="multipart/form-data" style="margin: 0px 12%;">
                            <p class="mb-4">Enter the verification code we sent to your phone.</p>
                            <div class="mb-4" style="display:flex !important; flex-direction:row !important; justify-content: space-between !important; align-items:baseline">
                                <input class="form-control num" type="text" maxlength="4" style="text-align:center;" name="code" id="code" placeholder="XXXX" required>
                            </div>
                            <button class="btn btn-primary mb-4" type="submit" id="submitBtn" style="padding: 10px 10px; width:100%">Verify</button>
                            <input class="form-control" type="hidden" name="_v5Token" id="_v5Token" value="<?= $_SESSION["_step5Token"]; ?>">
                        </form>
                    </div>
                    <div class="purchase-card-footer flex-row align-items-baseline justify-space-between" style="width: 100%;">
                        <a href="step4.php">Change number</a>
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

            $("#code").focus();

            $(".num").on("keyup", function() {
                if (this.value == 4) {
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
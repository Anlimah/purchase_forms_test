<?php
session_start();
if (isset($_SESSION['step3Done']) && $_SESSION['step3Done'] == true && isset($_SESSION["vendor_id"]) && !empty($_SESSION["vendor_id"]) && $_SESSION["vendor_type"] == "ONLINE") {
    if (!isset($_SESSION["_step4Token"])) {
        $rstrong = true;
        $_SESSION["_step4Token"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
    }
} else {
    header('Location: step3.php');
}

?>
<?php
require_once('inc/page-data.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("inc/head-section.php"); ?>
    <title>Form Purchase | Step 4</title>
</head>

<body class="fluid-container">

    <div id="wrapper">

        <?php require_once("inc/page-nav.php"); ?>

        <div id="flashMessage" class="alert text-center" style="display: none" role="alert"></div>

        <main class="container flex-container">
            <div class="flex-card">
                <div class="form-card card">
                    <div class="purchase-card-header">
                        <h1>Provide Phone Number</h1>
                    </div>

                    <div class="purchase-card-step-info">
                        <span class="step-capsule">Step 4 of 6</span>
                    </div>

                    <hr style="color:#999">

                    <div class="purchase-card-body">
                        <form action="#" id="step1Form" method="post" enctype="multipart/form-data" style="margin: 0px 12%;">
                            <div class="mb-4">
                                <p class="mb-4" style="color:#003262;">
                                    We'll send you an OTP message with a code to verify your phone number.<br>
                                    <span class="text-danger"><b>Note:</b> We don't accept VoIP or Skype numbers.</span>
                                </p>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="phone-number">Phone Number</label>
                                <div style="display:flex !important; flex-direction:row !important; justify-content: space-between !important">
                                    <select title="Choose country and country code" class="form-select form-select-sm country-code" name="country" id="country" style="margin-right: 10px; width: 45%" required>
                                        <option hidden disabled value="">Choose...</option>
                                        <?php
                                        foreach (COUNTRIES as $cn) {
                                            echo '<option value="(' . $cn["code"] . ') ' . $cn["name"] . '">(' . $cn["code"] . ') ' . $cn["name"] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <input maxlength="11" title="Provide your Provide Number" class="form-control form-control-sm" style="width: 70%" type="tel" name="phone_number" id="phone_number" placeholder="12345678901" required>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit" id="submitBtn" style="padding: 10px 10px; width:100%">Continue</button>
                            <input class="form-control" type="hidden" name="_v4Token" value="<?= $_SESSION["_step4Token"]; ?>">
                        </form>
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

            $(document).on({
                ajaxStart: function() {
                    $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    $("#submitBtn").prop("disabled", false).html('Continue');
                }
            });

            $("#phone_number").focus();
        });
    </script>
</body>

</html>
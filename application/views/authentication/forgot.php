<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="">
    <meta name="author" content="techtune">
    <meta name="description" content="">
    <title><?php echo translate('Forgot'); ?></title>
    <link rel="shortcut icon" href="<?php echo base_url('uploads/app_image/emp.png'); ?>">

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/login_2/css/style.css?v1.2'); ?>">

    <!-- Sweetalert js/css -->
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css'); ?>">
    <script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>

    <!-- Web Fonts  -->
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/font-awesome/css/all.min.css'); ?>">

    <script type="text/javascript">
        var base_url = '<?php echo base_url() ?>';
    </script>

    <style>
        body,
        html {
            height: 100%;
            margin: 0;
        }

        .wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .img {
            flex: 1;
            text-align: center;
            padding: 20px;
        }

        .img img {
            max-width: 100%;
            height: auto;
        }

        .login-wrap {
            flex: 1;
            padding: 40px;
            background: #fff;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-control {
            padding: 5px 10px;
        }

        .ftco-section {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .btn.btn-primary {
            padding: 8px 12px;
            background: #5156be !important;
            border: 1px solid #5156be !important;
            color: #fff !important;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .btn.btn-primary:hover {
            background: #6c75f2 !important;
            color: #fff !important;
            border: 1px solid #6c75f2 !important;
        }

        a {
            color: #757be5;
        }

        /* Mobile responsiveness */
        @media (max-width: 767px) {
            .wrap {
                flex-direction: column;
            }

            .img {
                display: none;
            }

            .login-wrap {
                width: 100%;
                padding: 20px !important;
            }

            .login-wrap .mobile-logo {
                display: flex;
                justify-content: center;
                margin-bottom: 20px;
            }

            .login-wrap .mobile-logo img {
                max-width: 150px;
                height: auto;
            }

            .contents {
                width: 100%;
                padding: 0;
            }
        }

        /* Button styles */
        .btn.btn-success {
            background: #28a745 !important;
            border: 1px solid #28a745 !important;
            color: #fff !important;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .btn.btn-success:hover {
            background: #218838 !important;
            color: #fff !important;
            border: 1px solid #218838 !important;
        }

        /* Custom SweetAlert styles */
        .custom-swal {
            width: 300px !important;
            font-size: 14px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-color: #f9f9f9;
            color: #333;
            font-family: Arial, sans-serif;
        }
        .custom-swal .swal2-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .custom-swal .swal2-content {
            font-size: 14px;
            color: #666;
        }
        .swal2-icon.swal2-success, .swal2-icon.swal2-error, .swal2-icon.swal2-info {
            margin-top: 10px;
        }
        .custom-swal .swal2-confirm {
            background-color: #6c63ff !important;
            color: #fff !important;
            border-radius: 8px !important;
            padding: 8px 16px;
            font-weight: 600;
        }
    </style>
</head>

<body>
<?php $system_logo = $this->app_lib->get_image_url('settings/' . $global_config['system_logo']); ?>
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-10">
                    <div class="wrap d-md-flex">
                        <div class="img">
                              <img src="<?php echo html_escape($system_logo); ?>" alt="Image" class="img-fluid">
                        </div>
                        <div class="login-wrap p-4 p-md-7">
                            <div class="col-md-12 contents">
                                <div class="row justify-content-center">
                                    <div class="col-md-12">
                                        <?php
                                        if ($this->session->flashdata('reset_res')) {
                                            if ($this->session->flashdata('reset_res') == 'TRUE') {
                                                echo '<div class="alert-msg">Password reset email sent successfully. Check email</div>';
                                            } elseif ($this->session->flashdata('reset_res') == 'FALSE') {
                                                echo '<div class="alert-msg danger">You entered the wrong Username</div>';
                                            }
                                        }
                                        ?>
                                        <div class="mb-4">
                                            <h5><i class="fas fa-fingerprint"></i> <?php echo translate('password_restoration'); ?></h5>
                                            Enter your email / username and receive reset instructions via email.
                                        </div>

                                        <?php echo form_open($this->uri->uri_string()); ?>
                                        <div class="form-group <?php if (form_error('username')) echo 'has-error'; ?>">
                                            <label for="username">Email / Username</label>
                                            <div class="input-group input-group-icon">
                                                <input type="text" class="form-control" name="username" id="username" value="<?php echo set_value('username'); ?>" placeholder="<?php echo ' &#xf007;' . translate(' email / username'); ?>" style="font-family: Arial, FontAwesome;" />
                                            </div>
                                            <span class="error"><?php echo form_error('username'); ?></span>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" id="btn_submit" class="btn btn-block btn-primary">
                                                <i class="fa fa-key"></i>&nbsp;&nbsp;<?php echo translate('forgot'); ?>
                                            </button>
                                        </div>

                                        <div>
                                            <a href="<?php echo base_url('authentication'); ?>"><i class="fas fa-arrow-left"></i> <?php echo translate('back_to_login'); ?></a>
                                        </div>
                                        <?php echo form_close(); ?>

                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="<?php echo base_url('assets/login_2/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/login_2/js/popper.js'); ?>"></script>
    <script src="<?php echo base_url('assets/login_2/js/bootstrap.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/login_2/js/main.js'); ?>"></script>

    <?php
    $alertclass = "";
    if ($this->session->flashdata('alert-message-success')) {
        $alertclass = "success";
    } else if ($this->session->flashdata('alert-message-error')) {
        $alertclass = "error";
    } else if ($this->session->flashdata('alert-message-info')) {
        $alertclass = "info";
    }
    if ($alertclass != ''):
        $alert_message = $this->session->flashdata('alert-message-' . $alertclass);
    ?>
    <script type="text/javascript">
        swal({
            toast: true,
            position: 'top-end',
            icon: '<?php echo $alertclass; ?>',
            title: '<?php echo $alert_message; ?>',
            customClass: 'custom-swal',
            buttonsStyling: false,
            timer: 8000
        });
    </script>
    <?php endif; ?>
</body>

</html>
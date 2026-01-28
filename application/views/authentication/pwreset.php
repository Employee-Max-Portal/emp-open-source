<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo translate('reset_password'); ?></title>
    <link rel="shortcut icon" href="<?php echo base_url('uploads/app_image/emp.png'); ?>">
    
    <!-- Essential CSS Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #5156be;
            --secondary-color: #6c75f2;
            --text-color: #333;
            --background-color: #f4f5f7;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Lato', sans-serif;
            background-color: var(--background-color);
        }

        .reset-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .reset-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .reset-image {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            padding: 30px;
        }

        .reset-image img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
        }

        .reset-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .reset-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .reset-logo img {
            max-width: 200px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }

        .btn-reset {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-reset:hover {
            background-color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .reset-wrapper {
                flex-direction: column;
            }

            .reset-image {
                display: none;
            }
        }
    </style>
</head>
<body>
<?php $system_logo = $this->app_lib->get_image_url('settings/' . $global_config['system_logo']); ?>
    <div class="reset-container">
        <div class="reset-wrapper">
            <div class="reset-image">
                <img src="<?php echo html_escape($system_logo); ?>" alt="Reset Password Illustration">
            </div>
            
            <div class="reset-form">
                <div class="reset-logo">
                     <h1><?php echo $global_config['institute_name'];?></h1>
					 <br>
                    <h2>Reset Password</h2>
                </div>

                <?php echo form_open($this->uri->uri_string()); ?>
                    <input type="hidden" name="key" value="<?php echo $key; ?>">

                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required>
							 <span class="toggle-password" onclick="togglePasswordVisibility('password')">
								<i class="fas fa-eye-slash" id="passwordToggle"></i>
							</span>
                        </div>
                        <?php if(form_error('password')): ?>
                            <small class="text-danger"><?php echo form_error('password'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="c_password" id="confirm_password" class="form-control" placeholder="Confirm New Password" required>
							<span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
								<i class="fas fa-eye-slash" id="confirm_passwordToggle"></i>
							</span>
                        </div>
                        <?php if(form_error('c_password')): ?>
                            <small class="text-danger"><?php echo form_error('c_password'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-reset">
                            Reset Password <i class="fas fa-lock"></i>
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

   <script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const toggleIcon = document.getElementById(inputId + 'Toggle');
        
        if (input.type === 'password') {
            input.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }
</script>
</body>
</html>
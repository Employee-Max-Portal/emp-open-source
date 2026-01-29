<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo translate('reset_password'); ?></title>
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/favicon.png');?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8eaf6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            display: flex;
            max-width: 750px;
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #50a7e3 0%, #3b8bc9 100%);
            padding: 40px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .left-panel h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
        }
        .left-panel p {
            color: rgba(255,255,255,0.9);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 30px;
        }
        .features {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .feature-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }
        .feature-text {
            color: rgba(255,255,255,0.95);
            font-weight: 400;
            font-size: 0.875rem;
        }

        .right-panel {
            flex: 1;
            padding: 40px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .right-panel h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .right-panel .subtitle {
            color: #64748b;
            margin-bottom: 30px;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .form-control:focus {
            outline: none;
            border-color: #50a7e3;
            box-shadow: 0 0 0 3px rgba(80, 167, 227, 0.1);
        }
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 24px;
            height: 24px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #50a7e3 0%, #3b8bc9 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(80, 167, 227, 0.4);
        }
        .error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }
        @media (max-width: 768px) {
            .login-container { flex-direction: column; }
            .left-panel { display: none; }
            .right-panel { padding: 40px 30px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-panel">
            <h1>Create New Password</h1>
            <p>Set up a strong and secure password to protect your account from unauthorized access.</p>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-fingerprint"></i></div>
                    <div class="feature-text">Strong Encryption</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shield-virus"></i></div>
                    <div class="feature-text">Advanced Security</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                    <div class="feature-text">Instant Update</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-user-lock"></i></div>
                    <div class="feature-text">Privacy Protected</div>
                </div>
            </div>

        </div>
        <div class="right-panel">
            <h2>Reset Password</h2>
            <p class="subtitle">Enter your new password below to reset your account</p>

            <?php echo form_open($this->uri->uri_string()); ?>
                <input type="hidden" name="key" value="<?php echo $key; ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" id="password" 
                               placeholder="Enter new password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'eyeIcon1')">
                            <i class="fas fa-eye" id="eyeIcon1"></i>
                        </button>
                    </div>
                    <?php if(form_error('password')): ?>
                        <span class="error"><?php echo form_error('password'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="c_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="c_password" id="c_password" 
                               placeholder="Confirm new password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('c_password', 'eyeIcon2')">
                            <i class="fas fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>
                    <?php if(form_error('c_password')): ?>
                        <span class="error"><?php echo form_error('c_password'); ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-check"></i>
                    Reset Password
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

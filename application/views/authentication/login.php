<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo translate('Login'); ?></title>
    <link rel="shortcut icon" href="<?php echo base_url('assets/images/favicon.png');?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css'); ?>">
    <script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>
    <script type="text/javascript">var base_url = '<?php echo base_url() ?>';</script>
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
            color: white;
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
        .forgot-link {
            text-align: right;
            margin-top: -14px;
            margin-bottom: 20px;
        }
        .forgot-link a {
            color: #50a7e3;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .forgot-link a:hover {
            color: #3b8bc9;
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
        .signup-section {
            margin-top: 24px;
            text-align: center;
        }
        .signup-text {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 12px;
            font-weight: 500;
        }
        .demo-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .demo-text {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .button-group {
            display: flex;
            gap: 8px;
        }
        .modern-btn {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .modern-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s;
        }
        .modern-btn:hover::before {
            left: 100%;
        }
        .btn-demo {
            background: linear-gradient(135deg, #50a7e3 0%, #3b8bc9 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(80, 167, 227, 0.3);
        }
        .btn-demo:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(80, 167, 227, 0.4);
        }
        .btn-admin {
            background: white;
            color: #50a7e3;
            border: 2px solid #50a7e3;
            box-shadow: 0 2px 8px rgba(80, 167, 227, 0.15);
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(80, 167, 227, 0.25);
            background: #f8fbfd;
        }
        .btn-signup {
            background: white;
            color: #10b981;
            border: 2px solid #10b981;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
        }
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            background: #f0fdf4;
        }
        .btn-icon {
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .login-container { flex-direction: column; }
            .left-panel { display: none; }
            .right-panel { padding: 40px 30px; }
            .button-group { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-panel">
            <h1>Employee Max Portal</h1>
            <p>Self-hosted workforce management system.</p>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="feature-text">Secure authentication</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
                    <div class="feature-text">Role-based access control</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="feature-text">Built for reliability and transparency</div>
                </div>
            </div>

        </div>
        <div class="right-panel">
            <h2>Sign In</h2>
            <p class="subtitle">Enter your credentials to access your account</p>
            <?php echo form_open($this->uri->uri_string()); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" name="username" id="username" 
                               value="<?php echo set_value('username'); ?>" placeholder="Enter your username">
                    </div>
                    <?php if(form_error('username')): ?>
                        <span class="error"><?php echo form_error('username'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <?php if(form_error('password')): ?>
                        <span class="error"><?php echo form_error('password'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="forgot-link">
                    <a href="<?php echo base_url('authentication/forgot'); ?>">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        function fillEmployee() {
            document.getElementById('username').value = 'user@demo-emp.com.bd';
            document.getElementById('password').value = '123456';
            
            // Toggle button styles
            const employeeBtn = document.getElementById('employeeBtn');
            const adminBtn = document.getElementById('adminBtn');
            
            employeeBtn.className = 'modern-btn btn-demo';
            adminBtn.className = 'modern-btn btn-admin';
        }

        function fillAdmin() {
            document.getElementById('username').value = 'admin@demo-emp.com.bd';
            document.getElementById('password').value = '123456';
            
            // Toggle button styles
            const employeeBtn = document.getElementById('employeeBtn');
            const adminBtn = document.getElementById('adminBtn');
            
            employeeBtn.className = 'modern-btn btn-admin';
            adminBtn.className = 'modern-btn btn-demo';
        }
    </script>
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
    <div id="customToast" style="display:none;"></div>
    <script type="text/javascript">
        function showToast(message, type) {
            const toast = document.getElementById('customToast');
            const icon = type === 'error' ? 'fa-times-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
            const color = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#50a7e3';
            
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fas ${icon}" style="color: ${color};"></i>
                    <span>${message}</span>
                </div>
                <button class="toast-btn" onclick="closeToast()" style="background: ${color};">OK</button>
            `;
            toast.style.display = 'flex';
            toast.style.borderLeftColor = color;
            toast.className = 'toast-show';
        }
        
        function closeToast() {
            const toast = document.getElementById('customToast');
            toast.className = 'toast-hide';
            setTimeout(() => { toast.style.display = 'none'; }, 400);
        }
        
        showToast('<?php echo addslashes($alert_message); ?>', '<?php echo $alertclass; ?>');
    </script>
    <style>
        #customToast {
            position: fixed;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 18px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            gap: 14px;
            z-index: 9999;
            border-left: 5px solid;
            min-width: 350px;
            font-family: 'Inter', sans-serif;
        }
        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        #customToast i {
            font-size: 24px;
        }
        #customToast span {
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            flex: 1;
        }
        .toast-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .toast-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .toast-show {
            animation: slideDown 0.25s ease-out forwards;
        }
        .toast-hide {
            animation: slideUp 0.25s ease-in forwards;
        }
        @keyframes slideDown {
            from { top: -200px; opacity: 0; }
            to { top: 20px; opacity: 1; }
        }
        @keyframes slideUp {
            from { top: 20px; opacity: 1; }
            to { top: -200px; opacity: 0; }
        }
    </style>
    <?php endif; ?>
</body>
</html>
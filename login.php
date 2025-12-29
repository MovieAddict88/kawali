<?php
// Start session
session_start();

// Check if the user is already logged in, if yes then redirect to the welcome page
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('location: index.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';
load_language($pdo);

$site_name = get_setting($pdo, 'site_name');
$site_icon = get_setting($pdo, 'site_icon');

// Define variables and initialize with empty values
$username = $password = '';
$username_err = $password_err = '';

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if username is empty
    if (empty(trim($_POST['username']))) {
        $username_err = translate('please_enter_username');
    } else {
        $username = trim($_POST['username']);
    }

    // Check if password is empty
    if (empty(trim($_POST['password']))) {
        $password_err = translate('please_enter_your_password');
    } else {
        $password = trim($_POST['password']);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement - Only allow admin users to login
        $sql = 'SELECT id, username, password, role, is_reseller FROM users WHERE username = :username';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists and is admin
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row['id'];
                        $hashed_password = $row['password'];
                        $role = $row['role'];
                        
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id'] = $id;
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = $row['role'];
                            $_SESSION['is_reseller'] = $row['is_reseller'];

                            // Redirect user to welcome page
                            header('location: index.php');
                        } else {
                            // Display an error message if password is not valid
                            $password_err = translate('invalid_password');
                        }
                    }
                } else {
                    // Display an error message if username doesn't exist or is not admin
                    $username_err = translate('no_admin_account_found');
                }
            } else {
                echo translate('oops_something_went_wrong');
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title><?php echo translate('login_title'); ?> - <?php echo htmlspecialchars($site_name); ?></title>
    <?php if ($site_icon): ?>
        <link rel='icon' href='<?php echo htmlspecialchars($site_icon); ?>?v=<?php echo time(); ?>' type='image/x-icon'>
    <?php endif; ?>
    <!-- Material Components Web CSS -->
    <link rel="stylesheet" href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Material Components Web JS -->
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
    <style>
        body.login-page {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Roboto', sans-serif;
        }

        .login-container {
            display: flex;
            max-width: 900px;
            width: 90%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            min-height: 550px;
        }
        
        @keyframes panImage {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

        /* IMAGE BACKGROUND VERSION */
        .login-image {
            flex: 1;
            background-image: url('assets/background/background.jpg');
            animation: panImage 20s linear infinite;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            position: relative;
            /* Optional: Add overlay for better text visibility */
            background-blend-mode: overlay;
        }

        /* Add a dark overlay over the image for better text readability */
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay */
            z-index: 1;
        }

        /* OPTION 2: Light overlay (if you want brighter look) */
        /*
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.8) 0%, rgba(58, 12, 163, 0.8) 100%);
            z-index: 1;
        }
        */

        /* OPTION 3: Gradient overlay combined with image */
        /*
        .login-image {
            flex: 1;
            background: 
                linear-gradient(135deg, rgba(67, 97, 238, 0.85) 0%, rgba(58, 12, 163, 0.85) 100%),
                url('https://images.unsplash.com/photo-1555099962-4199c345e5dd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            position: relative;
        }
        */

        .login-image .content {
            position: relative;
            z-index: 2; /* Above the overlay */
            text-align: center;
            max-width: 80%;
        }

        .login-image h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .login-image p {
            font-size: 1.1rem;
            opacity: 0.95;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .site-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .login-form h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .login-form > p {
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        form {
            width: 100%;
            max-width: 350px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-group {
            width: 100%;
            margin-bottom: 20px;
            text-align: center;
        }

        .mdc-text-field {
            width: 100%;
        }

        .text-danger {
            color: #f44336;
            font-size: 0.85rem;
            margin-top: 5px;
            display: inline-block;
            text-align: center;
        }

        .mdc-button {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .mdc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 95%;
                margin: 20px;
            }
            
            .login-image {
                padding: 30px;
                min-height: 200px;
                background-attachment: scroll; /* Better for mobile */
            }
            
            .login-form {
                padding: 30px;
            }
        }

        /* If you want to use a local image instead */
        /*
        .login-image {
            background-image: url('/path/to/your/local/image.jpg');
            background-size: cover;
            background-position: center;
        }
        */
    </style>
</head>
<body class="login-page">
    <div class='login-container'>
        <div class='login-image'>
            <div class="content">
                <h1><?php echo htmlspecialchars($site_name); ?></h1>
                <p><?php echo translate('welcome_back'); ?></p>
            </div>
        </div>
        <div class='login-form'>
            <div style="margin-bottom: 30px;">
                <?php if ($site_icon): ?>
                    <img src="<?php echo htmlspecialchars($site_icon); ?>?v=<?php echo time(); ?>" alt="Site Icon" class="site-icon">
                <?php else: ?>
                    <span class="material-icons" style="font-size: 3rem; color: #4361ee; margin-bottom: 10px;">vpn_lock</span>
                <?php endif; ?>
                <h2><?php echo translate('login_title'); ?></h2>
                <p><?php echo translate('please_fill_credentials'); ?></p>
            </div>
            <form action='login.php' method='post'>
                <div class="form-group">
                    <label class="mdc-text-field mdc-text-field--outlined">
                        <span class="mdc-notched-outline">
                            <span class="mdc-notched-outline__leading"></span>
                            <span class="mdc-notched-outline__notch">
                                <span class="mdc-floating-label" id="username-label"><?php echo translate('username'); ?></span>
                            </span>
                            <span class="mdc-notched-outline__trailing"></span>
                        </span>
                        <input type="text" name="username" class="mdc-text-field__input" aria-labelledby="username-label" value="<?php echo htmlspecialchars($username); ?>">
                    </label>
                    <span class="text-danger"><?php echo $username_err; ?></span>
                </div>

                <div class="form-group">
                    <label class="mdc-text-field mdc-text-field--outlined">
                        <span class="mdc-notched-outline">
                            <span class="mdc-notched-outline__leading"></span>
                            <span class="mdc-notched-outline__notch">
                                <span class="mdc-floating-label" id="password-label"><?php echo translate('password'); ?></span>
                            </span>
                            <span class="mdc-notched-outline__trailing"></span>
                        </span>
                        <input type="password" name="password" class="mdc-text-field__input" aria-labelledby="password-label">
                    </label>
                    <span class="text-danger"><?php echo $password_err; ?></span>
                </div>

                <div class='form-group'>
                    <button class="mdc-button mdc-button--raised" type="submit">
                        <span class="mdc-button__label"><?php echo translate('login'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Initialize Material Design components
        document.addEventListener('DOMContentLoaded', function() {
            const textFields = [].slice.call(document.querySelectorAll('.mdc-text-field'));
            textFields.forEach(textField => {
                if (textField.classList.contains('mdc-text-field')) {
                    new mdc.textField.MDCTextField(textField);
                }
            });
            
            const buttons = [].slice.call(document.querySelectorAll('.mdc-button'));
            buttons.forEach(button => {
                if (button.classList.contains('mdc-button')) {
                    mdc.ripple.MDCRipple.attachTo(button);
                }
            });
        });
    </script>
</body>
</html>
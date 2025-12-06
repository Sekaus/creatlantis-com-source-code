<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Start output buffering to capture any accidental HTML output
        ob_start();

        // Load only the PHP code you need (these may still echo — buffer will catch it)
        require_once "./data_handler.php";
        require_once "./setup.php";

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Unserialize login/user from session
        $login = $_SESSION['user_login'] ?? null;
        $user  = $_SESSION['user_data'] ?? null;
        if ($login) $login = @unserialize($login);
        if ($user)  $user  = @unserialize($user);

        // Quick login check
        if (!($login instanceof Login) || !($user instanceof User)) {
            // Clean any buffered output and return JSON
            ob_end_clean();
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not logged in.']);
            exit;
        }

        // Build DataHandle
        try {
            $dh = new DataHandle($dbConfig, $s3Config, S3BotType::writeOnly);
        } catch (Throwable $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server config error: ' . $e->getMessage()]);
            exit;
        }

        if(!(isset($_GET["username"]) && $_GET["username"] == $user->username())) {
            ob_end_clean();
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'The account is not yours.']);
            exit;
        }

        // Now call upload and capture result. Wrap with try/catch.
        try {
            if(isset($_POST['profile_design']))
                $result = $dh->updateProfileDesign($user, $login, $_POST['profile_design']);
            else {
                echo json_encode(['success' => false, 'error' => 'Profile data has no body.']);
                exit;
            }

            // Remove any accidental buffered output (HTML/JS) before sending JSON
            ob_end_clean();

            header('Content-Type: application/json');

            if (!is_array($result)) {
                // ensure we return a consistent structure
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Invalid server response.']);
                exit;
            }

            // Set HTTP code for failure so client error handler can also be used if desired
            if (isset($result['success']) && $result['success'] === false) {
                http_response_code(400);
            } else {
                http_response_code(200);
            }

            echo json_encode($result);
            exit;
        } catch (Throwable $e) {
            // Clear buffer and return error JSON
            ob_end_clean();
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
            exit;
        }
    }
?>
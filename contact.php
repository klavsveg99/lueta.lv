<?php
header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// SMTP configuration
$cfg = require __DIR__ . '/../private/config.php';
$smtpHost = $cfg['smtpHost'];
$smtpUser = $cfg['smtpUser'];
$smtpPass = $cfg['smtpPass'];
$smtpPort = $cfg['smtpPort'];

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
$company_description = isset($_POST['company_description']) ? trim($_POST['company_description']) : '';

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$isEnglish = (strpos($referer, '/en.html') !== false) || (strpos($referer, '/en?') !== false);

$messages = [
    'lv' => [
        'required' => 'Lūdzu, aizpildiet visus obligātos laukus.',
        'invalid_email' => 'Lūdzu, ievadiet derīgu e-pasta adresi.',
        'invalid_phone' => 'Tālruņa numurs var saturēt tikai ciparus un + zīmi.',
        'success' => 'Paldies! Jūsu ziņa ir nosūtīta. Es sazināšos ar jums drīzumā.',
        'error' => 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz vai sazinieties ar mani pa e-pastu.'
    ],
    'en' => [
        'required' => 'Please fill in all required fields.',
        'invalid_email' => 'Please enter a valid email address.',
        'invalid_phone' => 'Phone number can only contain numbers and + sign.',
        'success' => 'Thank you! Your message has been sent. I will contact you soon.',
        'error' => 'Error sending message. Please try again or contact me by email.'
    ]
];

$lang = $isEnglish ? 'en' : 'lv';

if (empty($name) || empty($email) || empty($brand_name) || empty($company_description)) {
    echo json_encode(['success' => false, 'message' => $messages[$lang]['required']]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => $messages[$lang]['invalid_email']]);
    exit;
}

if (!empty($phone) && !preg_match('/^[0-9+]+$/', $phone)) {
    echo json_encode(['success' => false, 'message' => $messages[$lang]['invalid_phone']]);
    exit;
}

$to = 'lueta@lueta.lv';
$subject = $isEnglish ? 'New message from lueta.lv - ' . $name : 'Jauna ziņa no lueta.lv - ' . $name;

$email_content = $isEnglish ? "New message from contact form:\n\n" : "Jauna ziņa no kontaktformas:\n\n";
$email_content .= ($isEnglish ? "Name: " : "Vārds: ") . $name . "\n";
$email_content .= ($isEnglish ? "Brand Name: " : "Zīmola nosaukums: ") . $brand_name . "\n";
$email_content .= ($isEnglish ? "Email: " : "E-pasts: ") . $email . "\n";
$email_content .= ($isEnglish ? "Phone: " : "Tālrunis: ") . ($phone ? $phone : ($isEnglish ? 'Not specified' : 'Nav norādīts')) . "\n";
$email_content .= ($isEnglish ? "Company Description: " : "Uzņēmuma darbības apraksts: ") . $company_description . "\n";
$email_content .= "\n---\n";
$email_content .= ($isEnglish ? "Sent from: " : "Nosūtīts no: ") . ($_SERVER['HTTP_REFERER'] ?? 'Unknown') . "\n";
$email_content .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtpPort;
    $mail->Timeout = 30;

    $mail->setFrom($smtpUser, 'Lueta.lv');
    $mail->addAddress($to);
    $mail->addReplyTo($email, $name);

    $mail->Subject = $subject;
    $mail->Body = $email_content;
    $mail->CharSet = 'UTF-8';

    $result = $mail->send();
    echo json_encode(['success' => true, 'message' => $messages[$lang]['success']]);
} catch (Exception $e) {
    $error = $mail->ErrorInfo ?? $e->getMessage();
    echo json_encode(['success' => false, 'message' => $messages[$lang]['error']]);
}

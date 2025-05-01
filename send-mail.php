<?php
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $to = "info@dipug.com";
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = trim($_POST["message"]);

    // Handle service selection and "Others"
    $service = isset($_POST["service_type"]) ? strip_tags(trim($_POST["service_type"])) : 'Not specified';
    if ($service === 'professional' && isset($_POST['service'])) {
        $service = strip_tags(trim($_POST['service']));
        if ($service === 'Others' && !empty($_POST['other_service'])) {
            $other = strip_tags(trim($_POST['other_service']));
            if ($other !== '') { $service = $other; }
        }
    } elseif ($service === 'general') {
        $service = 'General Inquiry';
    }

    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Service Requested: $service\n";
    $email_content .= "Subject: $subject\n\n";
    $email_content .= "Message:\n$message\n";

    $boundary = md5(time());
    $headers = "From: $name <$email>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $email_content . "\r\n";

    // Attachments
    if (!empty($_FILES['attachments'])) {
        foreach ($_FILES['attachments']['error'] as $idx => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['attachments']['tmp_name'][$idx];
                $fileSize = $_FILES['attachments']['size'][$idx];
                $fileName = basename($_FILES['attachments']['name'][$idx]);
                if ($fileSize > 5 * 1024 * 1024) {
                    echo "Error: File $fileName exceeds the 5MB limit.";
                    exit;
                }
                $fileType = mime_content_type($tmpName);
                $fileData = chunk_split(base64_encode(file_get_contents($tmpName)));
                $body .= "--$boundary\r\n";
                $body .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $fileData . "\r\n";
            }
        }
    }

    $body .= "--$boundary--";

    $success = mail($to, $subject, $body, $headers);
    echo $success ? 'Message sent successfully!' : 'There was a problem sending your message.';
} else {
    http_response_code(403);
    echo 'There was a problem with your submission.';
}

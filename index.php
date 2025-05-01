<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the form fields
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $service_type = htmlspecialchars(trim($_POST['service_type']));
    $service = isset($_POST['service']) ? htmlspecialchars(trim($_POST['service'])) : '';
    $other_service = isset($_POST['other_service']) ? htmlspecialchars(trim($_POST['other_service'])) : '';
    $message = htmlspecialchars(trim($_POST['message']));

    // Build the email content
    $email_subject = "New Contact Form Submission: " . $subject;
    $email_body = "You have received a new message from Dipug Website:\n\n";
    $email_body .= "Full Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Subject: $subject\n";
    $email_body .= "Service Type: $service_type\n";

    if ($service_type == "professional") {
        $email_body .= "Professional Service: " . ($service == "Others" ? $other_service : $service) . "\n";
    }

    $email_body .= "Message:\n$message\n";

    // Where to send the email
    $to = "info@dipug.com";  // Change this to your real email address

    // Create a boundary for attachments
    $boundary = md5(time());

    // Email headers
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"\r\n";

    // Plain text part
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $email_body . "\r\n";

    // Attachments
    if (!empty($_FILES['attachments']['name'][0])) {
        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['attachments']['name'][$key];
                $file_size = $_FILES['attachments']['size'][$key];
                $file_tmp = $_FILES['attachments']['tmp_name'][$key];
                $file_type = $_FILES['attachments']['type'][$key];

                // Limit size to 5MB
                if ($file_size <= 5 * 1024 * 1024) {
                    $handle = fopen($file_tmp, "rb");
                    $content = fread($handle, filesize($file_tmp));
                    fclose($handle);
                    $encoded_content = chunk_split(base64_encode($content));

                    $body .= "--$boundary\r\n";
                    $body .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
                    $body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
                    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                    $body .= $encoded_content . "\r\n";
                }
            }
        }
    }

    $body .= "--$boundary--";

    // Send the email
    if (mail($to, $email_subject, $body, $headers)) {
        echo "Message sent successfully!";
    } else {
        echo "Failed to send the message. Please try again.";
    }

} else {
    echo "Invalid request.";
}
?>

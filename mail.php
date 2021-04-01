<?php
require('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Seus dados de login no gmail
 * (Não esqueça de liberar a permissão para apps frágeis,
 * recomendo que não utilize seu e-mail pessoal. Crie um para isso).
 */
$email = '';
$password = '';

$from = 'From@email.example';
$fromName = 'From Example';
$subject = 'This is a subject';
$content = 'Apenas um exemplo<br><br>Valeu!';


if (($handle = fopen("emails.csv", "r")) !== FALSE) {
    $i = 1;

    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        if ($i % 40 === 1 && $i !== 1) {
            $wait = rand(1800, 7200);
            echo "$i - VAMOS ESPERAR $wait segundos";
            sleep($wait);
        }

        if ($i !== 1) {
            sleep(rand(4, 15));
        }

        $nome = $data[0];
        $email = $data[1];
        echo "\n\n\n$i- $nome: $email\n\n";
        $i++;

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Mailer = "smtp";

        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = TRUE;
        $mail->SMTPSecure = "tls";
        $mail->Port       = 587;
        $mail->Host       = "smtp.gmail.com";
        $mail->Username   = $email;
        $mail->Password   = $password;

        $mail->IsHTML(true);
        $mail->AddAddress("$email", "$nome");

        $mail->SetFrom($from, $fromName);

        $mail->Subject = $subject;
        $content = "Oi, $nome!<br><br>$content";


        $mail->MsgHTML($content);
        if (!$mail->Send()) {
            echo "Error while sending Email.";
            var_dump($mail);
        } else {
            echo "Email sent successfully";
        }
    }
    fclose($handle);
}

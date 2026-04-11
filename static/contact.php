<?php
/**
 * Kontaktformular-Handler für eurowoche.org
 * Empfängt POST-Daten, validiert und sendet E-Mail an pr@eurowoche.org
 */

// Nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /kontakt/');
    exit;
}

// Eingaben bereinigen
function clean($str) {
    return htmlspecialchars(trim(strip_tags($str)), ENT_QUOTES, 'UTF-8');
}

$vorname    = clean($_POST['first-name']  ?? '');
$nachname   = clean($_POST['last-name']   ?? '');
$email      = trim($_POST['email']        ?? '');
$betreff    = clean($_POST['betreff']     ?? '');
$nachricht  = clean($_POST['message']     ?? '');
$datenschutz = isset($_POST['datenschutz']);
$lang       = clean($_POST['lang']        ?? 'de');

// Validierung
$fehler = [];

if (empty($vorname)) {
    $fehler[] = 'Bitte gib deinen Vornamen an.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fehler[] = 'Bitte gib eine gültige E-Mail-Adresse an.';
}
if (empty($betreff)) {
    $fehler[] = 'Bitte gib einen Betreff an.';
}
if (empty($nachricht)) {
    $fehler[] = 'Bitte schreib eine Nachricht.';
}
if (!$datenschutz) {
    $fehler[] = 'Bitte stimme der Datenschutzerklärung zu.';
}

// Honeypot-Schutz gegen Spam (leeres verstecktes Feld)
if (!empty($_POST['website'])) {
    // Stille Weiterleitung (Spam-Bot täuschen)
    header('Location: /danke/');
    exit;
}

if (!empty($fehler)) {
    // Zurück mit Fehlermeldung
    $query = http_build_query(['fehler' => implode(' | ', $fehler)]);
    header('Location: /kontakt/?' . $query);
    exit;
}

// E-Mail an pr@eurowoche.org
$name_voll   = $vorname . ($nachname ? ' ' . $nachname : '');
$to          = 'pr@eurowoche.org';
$mail_subject = '=?UTF-8?B?' . base64_encode('Kontaktanfrage: ' . $betreff) . '?=';

$mail_body  = "Neue Kontaktanfrage über eurowoche.org\n";
$mail_body .= str_repeat('-', 40) . "\n\n";
$mail_body .= "Name:       $name_voll\n";
$mail_body .= "E-Mail:     $email\n";
$mail_body .= "Betreff:    $betreff\n\n";
$mail_body .= "Nachricht:\n$nachricht\n\n";
$mail_body .= str_repeat('-', 40) . "\n";
$mail_body .= "Gesendet am: " . date('d.m.Y H:i') . "\n";

$headers  = "From: noreply@eurowoche.org\r\n";
$headers .= "Reply-To: $name_voll <$email>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";

$sent = mail($to, $mail_subject, $mail_body, $headers);

// Bestätigungs-E-Mail an Absender
$confirm_subject = '=?UTF-8?B?' . base64_encode('Deine Nachricht an die Eurowoche') . '?=';
$confirm_body  = "Hallo $vorname,\n\n";
$confirm_body .= "vielen Dank für deine Nachricht! Wir haben sie erhalten und melden uns so bald wie möglich bei dir.\n\n";
$confirm_body .= "Deine Nachricht:\n";
$confirm_body .= str_repeat('-', 40) . "\n";
$confirm_body .= "$nachricht\n";
$confirm_body .= str_repeat('-', 40) . "\n\n";
$confirm_body .= "Herzliche Grüße\n";
$confirm_body .= "Das Team der Europäischen Jugendwoche\n";
$confirm_body .= "eurowoche.org | pr@eurowoche.org\n";

$confirm_headers  = "From: Europäische Jugendwoche <noreply@eurowoche.org>\r\n";
$confirm_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$confirm_headers .= "MIME-Version: 1.0\r\n";

mail($email, $confirm_subject, $confirm_body, $confirm_headers);

if ($sent) {
    header('Location: /danke/');
} else {
    header('Location: /kontakt/?fehler=Email+konnte+nicht+gesendet+werden.+Bitte+versuche+es+später+nochmal.');
}
exit;

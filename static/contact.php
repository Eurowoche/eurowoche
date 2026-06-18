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
    header('Location: /danke/');
    exit;
}

// Rate Limiting: max. 3 Anfragen pro IP pro Stunde
$ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file = sys_get_temp_dir() . '/contact_rate_' . md5($ip) . '.json';
$now       = time();
$window    = 3600; // 1 Stunde
$max       = 3;

$rate_data = [];
if (file_exists($rate_file)) {
    $rate_data = json_decode(file_get_contents($rate_file), true) ?: [];
}
$rate_data = array_filter($rate_data, fn($t) => ($now - $t) < $window);
if (count($rate_data) >= $max) {
    header('Location: /kontakt/?fehler=Zu+viele+Anfragen.+Bitte+warte+eine+Stunde+und+versuche+es+erneut.');
    exit;
}
$rate_data[] = $now;
file_put_contents($rate_file, json_encode(array_values($rate_data)));

// reCAPTCHA v3 verifizieren
$recaptcha_token  = trim($_POST['recaptcha-token'] ?? '');
$recaptcha_secret = '6LfmRyYtAAAAAL-xHyn4fGPEDlOqw10m8LgncnQ-';
$recaptcha_ok     = false;

if (!empty($recaptcha_token)) {
    $verify = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret='
        . urlencode($recaptcha_secret)
        . '&response=' . urlencode($recaptcha_token)
        . '&remoteip=' . urlencode($ip)
    );
    $result = json_decode($verify, true);
    // Score >= 0.5 gilt als Mensch (0.0 = Bot, 1.0 = Mensch)
    if (!empty($result['success']) && isset($result['score']) && $result['score'] >= 0.5) {
        $recaptcha_ok = true;
    }
}

if (!$recaptcha_ok) {
    header('Location: /kontakt/?fehler=Sicherheitspr%C3%BCfung+fehlgeschlagen.+Bitte+versuche+es+erneut.');
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

$score       = $result['score'] ?? 0;
$score_label = $score >= 0.8 ? '✅ Wahrscheinlich Mensch'
             : ($score >= 0.5 ? '⚠️  Unsicher – bitte prüfen'
             : '🚨 Verdächtig (Bot?)');

$mail_body  = "Neue Kontaktanfrage über eurowoche.org\n";
$mail_body .= str_repeat('-', 40) . "\n\n";
$mail_body .= "Name:       $name_voll\n";
$mail_body .= "E-Mail:     $email\n";
$mail_body .= "Betreff:    $betreff\n\n";
$mail_body .= "Nachricht:\n$nachricht\n\n";
$mail_body .= str_repeat('-', 40) . "\n";
$mail_body .= "Gesendet am:      " . date('d.m.Y H:i') . "\n";
$mail_body .= "reCAPTCHA Score:  " . number_format($score, 2) . " / 1.00  →  $score_label\n";

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

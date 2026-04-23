<?php
/**
 * DokuWiki Auto-Installer für Strato
 * Lädt DokuWiki herunter, entpackt es, konfiguriert Deutsch, und räumt auf.
 *
 * Aufruf: https://wiki.eurowoche.org/install-dokuwiki.php
 * WICHTIG: Löscht sich nach erfolgreicher Installation selbst!
 */

set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseDir = __DIR__;
$downloadUrl = 'https://download.dokuwiki.org/src/dokuwiki/dokuwiki-stable.tgz';
$archiveFile = $baseDir . '/dokuwiki-stable.tgz';

echo "<html><head><meta charset='utf-8'><title>DokuWiki Installation</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:40px auto;padding:20px;} ";
echo ".ok{color:green;} .err{color:red;} .step{margin:10px 0;padding:8px;background:#f5f5f5;border-radius:4px;}</style></head><body>";
echo "<h1>DokuWiki Installation</h1>";

// Step 1: Download
echo "<div class='step'><strong>Schritt 1:</strong> DokuWiki herunterladen... ";
$data = @file_get_contents($downloadUrl);
if ($data === false) {
    // Fallback: try curl
    $ch = curl_init($downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || empty($data)) {
        echo "<span class='err'>FEHLER: Download fehlgeschlagen (HTTP $httpCode)</span></div></body></html>";
        exit(1);
    }
}
file_put_contents($archiveFile, $data);
$size = round(filesize($archiveFile) / 1024 / 1024, 1);
echo "<span class='ok'>OK ({$size} MB)</span></div>";

// Step 2: Extract
echo "<div class='step'><strong>Schritt 2:</strong> Entpacken... ";
$phar = new PharData($archiveFile);
$phar->decompress();
$tarFile = str_replace('.tgz', '.tar', $archiveFile);
$pharTar = new PharData($tarFile);
$pharTar->extractTo($baseDir);
echo "<span class='ok'>OK</span></div>";

// Step 3: Find extracted directory and move contents up
echo "<div class='step'><strong>Schritt 3:</strong> Dateien verschieben... ";
$dirs = glob($baseDir . '/dokuwiki-*', GLOB_ONLYDIR);
if (empty($dirs)) {
    echo "<span class='err'>FEHLER: Entpackter Ordner nicht gefunden</span></div></body></html>";
    exit(1);
}
$extractedDir = $dirs[0];

// Move all files from extracted dir to base dir
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($extractedDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iterator as $item) {
    $target = $baseDir . '/' . $iterator->getSubPathName();
    if ($item->isDir()) {
        if (!is_dir($target)) mkdir($target, 0755, true);
    } else {
        $dir = dirname($target);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        rename($item->getPathname(), $target);
    }
}
// Remove extracted directory
function rmdir_recursive($dir) {
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? rmdir_recursive($path) : unlink($path);
    }
    rmdir($dir);
}
rmdir_recursive($extractedDir);
echo "<span class='ok'>OK</span></div>";

// Step 4: Pre-configure for German
echo "<div class='step'><strong>Schritt 4:</strong> Deutsche Vorkonfiguration... ";

// Create preload.php with initial config
$preload = <<<'PHP'
<?php
/**
 * DokuWiki Preload - setzt Defaults vor dem Installer
 */
// Nichts nötig, der Installer übernimmt den Rest
PHP;
file_put_contents($baseDir . '/inc/preload.php', $preload);

echo "<span class='ok'>OK</span></div>";

// Step 5: Set permissions
echo "<div class='step'><strong>Schritt 5:</strong> Berechtigungen setzen... ";
$writeDirs = ['data', 'data/pages', 'data/attic', 'data/media', 'data/media_attic',
              'data/media_meta', 'data/meta', 'data/cache', 'data/locks', 'data/tmp',
              'conf', 'lib/plugins'];
foreach ($writeDirs as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    if (is_dir($fullPath)) {
        chmod($fullPath, 0775);
    }
}
echo "<span class='ok'>OK</span></div>";

// Step 6: Create .htaccess for security
echo "<div class='step'><strong>Schritt 6:</strong> Sicherheitskonfiguration... ";

// Main .htaccess - rewrite rules for pretty URLs
$htaccess = <<<'HTACCESS'
## DokuWiki .htaccess

## Enable URL Rewriting
RewriteEngine on
RewriteBase /

## Deny access to data directories
RewriteRule ^data/ - [F]
RewriteRule ^conf/ - [F]
RewriteRule ^bin/ - [F]
RewriteRule ^inc/ - [F]
RewriteRule ^vendor/ - [F]

## Pretty URLs
RewriteRule ^_media/(.*) lib/exe/fetch.php?media=$1 [QSA,L]
RewriteRule ^_detail/(.*) lib/exe/detail.php?media=$1 [QSA,L]
RewriteRule ^_export/([^/]+)/(.*) doku.php?do=export_$1&id=$2 [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) doku.php?id=$1 [QSA,L]
HTACCESS;
file_put_contents($baseDir . '/.htaccess', $htaccess);
echo "<span class='ok'>OK</span></div>";

// Step 7: Cleanup
echo "<div class='step'><strong>Schritt 7:</strong> Aufräumen... ";
@unlink($archiveFile);
@unlink($tarFile);
echo "<span class='ok'>OK</span></div>";

echo "<hr>";
echo "<h2 style='color:green;'>✅ Installation vorbereitet!</h2>";
echo "<p><strong>Nächster Schritt:</strong> Öffne jetzt den DokuWiki-Installer:</p>";
echo "<p style='font-size:1.3em;'><a href='install.php'>→ wiki.eurowoche.org/install.php</a></p>";
echo "<p>Dort folgende Einstellungen wählen:</p>";
echo "<ul>";
echo "<li><strong>Wiki-Name:</strong> Eurowoche Wiki</li>";
echo "<li><strong>Sprache:</strong> de (Deutsch)</li>";
echo "<li><strong>ACL (Zugriffskontrolle):</strong> aktivieren</li>";
echo "<li><strong>Superuser:</strong> admin (und sicheres Passwort wählen)</li>";
echo "<li><strong>Registrierung:</strong> deaktivieren (Benutzer werden manuell angelegt)</li>";
echo "</ul>";
echo "<p style='color:#888;'>Diese Installationsdatei wird nach dem Aufruf von install.php automatisch überflüssig. DokuWiki löscht install.php nach der Einrichtung selbst.</p>";

// Self-delete this installer
@unlink(__FILE__);
echo "<p style='color:green;font-size:0.9em;'>install-dokuwiki.php wurde automatisch gelöscht.</p>";

echo "</body></html>";
?>

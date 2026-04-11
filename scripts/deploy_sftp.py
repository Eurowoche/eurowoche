#!/usr/bin/env python3
"""SFTP-Deploy-Script: lädt den Hugo-Output (public/) auf Strato hoch."""
import paramiko, os, sys, time
from pathlib import Path

host     = os.environ['SFTP_HOST']
user     = os.environ['SFTP_USER']
password = os.environ['SFTP_PASS']
remote   = os.environ.get('SFTP_DIR', '/').strip() or '/'
local    = Path('public')

print(f"Connecting to {host} as {user} ...")

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(
    hostname=host,
    port=22,
    username=user,
    password=password,
    look_for_keys=False,
    allow_agent=False,
)
print("SSH-Verbindung OK")

# Großzügiges Timeout für große Dateien (Bilder etc.)
transport = client.get_transport()
transport.set_keepalive(30)
sftp = client.open_sftp()
sftp.get_channel().settimeout(120)   # 2 Minuten pro Datei

def mkdir_p(sftp, path):
    parts = [p for p in path.replace('\\', '/').split('/') if p]
    current = ''
    for part in parts:
        current = (current + '/' + part) if current else part
        try:
            sftp.mkdir(current)
        except OSError:
            pass

def put_with_retry(sftp, local_path, remote_path, retries=3):
    for attempt in range(1, retries + 1):
        try:
            sftp.put(str(local_path), remote_path)
            return True
        except Exception as e:
            if attempt < retries:
                print(f"  Versuch {attempt} fehlgeschlagen, retry ... ({e})")
                time.sleep(2)
            else:
                print(f"  WARNUNG: {remote_path} nach {retries} Versuchen nicht hochgeladen: {e}")
                return False

uploaded = 0
skipped  = 0
for item in sorted(local.rglob('*')):
    if item.is_file():
        rel         = item.relative_to(local)
        base        = remote.rstrip('/') if remote != '/' else ''
        remote_path = base + '/' + str(rel).replace('\\', '/')
        remote_dir  = '/'.join(remote_path.split('/')[:-1]) or '/'
        mkdir_p(sftp, remote_dir)
        if put_with_retry(sftp, item, remote_path):
            uploaded += 1
            if uploaded % 30 == 0:
                print(f"  {uploaded} Dateien hochgeladen ...")
        else:
            skipped += 1

sftp.close()
client.close()
print(f"Deploy abgeschlossen: {uploaded} Dateien OK, {skipped} übersprungen.")
# Nur fehlschlagen wenn gar nichts hochgeladen wurde
if uploaded == 0:
    sys.exit(1)

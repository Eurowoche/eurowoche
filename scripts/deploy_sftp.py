#!/usr/bin/env python3
"""SFTP-Deploy-Script: lädt den Hugo-Output (public/) auf Strato hoch."""
import paramiko, os, sys
from pathlib import Path

host     = os.environ['SFTP_HOST']
user     = os.environ['SFTP_USER']
password = os.environ['SFTP_PASS']
remote   = os.environ['SFTP_DIR']
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

sftp = client.open_sftp()

def mkdir_p(sftp, path):
    parts = [p for p in path.replace('\\', '/').split('/') if p]
    current = ''
    for part in parts:
        current = (current + '/' + part) if current else part
        try:
            sftp.mkdir(current)
        except OSError:
            pass

uploaded = 0
errors   = 0
for item in sorted(local.rglob('*')):
    if item.is_file():
        rel         = item.relative_to(local)
        remote_path = remote.rstrip('/') + '/' + str(rel).replace('\\', '/')
        remote_dir  = '/'.join(remote_path.split('/')[:-1])
        mkdir_p(sftp, remote_dir)
        try:
            sftp.put(str(item), remote_path)
            uploaded += 1
            if uploaded % 30 == 0:
                print(f"  {uploaded} Dateien hochgeladen ...")
        except Exception as e:
            print(f"  WARNUNG: {remote_path}: {e}")
            errors += 1

sftp.close()
client.close()
print(f"Deploy abgeschlossen: {uploaded} Dateien OK, {errors} Fehler.")
if errors > 0:
    sys.exit(1)

#!/usr/bin/env python3
"""Upload install-dokuwiki.php to /wiki/ on Strato via SFTP."""
import paramiko
import os
import sys
from pathlib import Path

host     = os.environ['SFTP_HOST']
user     = os.environ['SFTP_USER']
password = os.environ['SFTP_PASS']
local    = Path('wiki-installer/install-dokuwiki.php')

if not local.exists():
    print(f"FEHLER: {local} nicht gefunden")
    sys.exit(1)

print(f"Verbinde mit {host}...")
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(hostname=host, port=22, username=user, password=password,
               look_for_keys=False, allow_agent=False)

sftp = client.open_sftp()

# Ensure /wiki exists
try:
    sftp.stat('/wiki')
    print("/wiki existiert")
except FileNotFoundError:
    sftp.mkdir('/wiki')
    print("/wiki angelegt")

# Upload
remote_path = '/wiki/install-dokuwiki.php'
print(f"Lade {local} hoch nach {remote_path}...")
sftp.put(str(local), remote_path)

# Verify
stat = sftp.stat(remote_path)
print(f"Verifiziert: {remote_path} ({stat.st_size} bytes)")

# List contents
print("\nInhalt von /wiki/:")
for entry in sftp.listdir('/wiki'):
    print(f"  {entry}")

sftp.close()
client.close()
print("\nFertig! Jetzt wiki.eurowoche.org/install-dokuwiki.php im Browser oeffnen.")

import os, sys
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')

env_path = Path('C:/repos/gb-site/.env.secrets')
env = {}
for line in env_path.read_text().splitlines():
    line = line.strip()
    if not line or line.startswith('#') or '=' not in line:
        continue
    k, v = line.split('=', 1)
    v = v.strip().strip('"').strip("'")
    env[k] = v

required = ['SSH_HOST', 'SSH_LOGIN', 'SSH_PASSWORD', 'PROJECT_PATH', 'PHP_CLI']
for k in required:
    if not env.get(k):
        print(f'missing {k}')
        sys.exit(1)

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(env['SSH_HOST'], username=env['SSH_LOGIN'], password=env['SSH_PASSWORD'], timeout=20)

project = env['PROJECT_PATH']
php = env['PHP_CLI']
composer = f'{project}/composer2'

commands = [
    f'cd {project} && git fetch origin && git reset --hard origin/main',
    f'cd {project} && PROJECT_DIR={project} PHP_BIN={php} COMPOSER_BIN={composer} bash deploy/beget/deploy.sh',
    f'cd {project}/api && {php} artisan db:seed --class=PcDemoPartsSeeder --force',
]

for cmd in commands:
    stdin, stdout, stderr = client.exec_command(cmd, timeout=300)
    out = stdout.read().decode(errors='replace')
    err = stderr.read().decode(errors='replace')
    print(f'--- {cmd} ---')
    print(out)
    if err.strip():
        print('ERR:', err)

client.close()
print('done')

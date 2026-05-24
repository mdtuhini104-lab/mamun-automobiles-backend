#!/bin/bash
set -e

echo "Starting Mamun Automobiles ERP VPS Bootstrap..."

# 1. Update and Upgrade
apt-get update && apt-get upgrade -y

# 2. Install Required Packages
apt-get install -y curl wget git unzip zip ufw fail2ban nginx certbot python3-certbot-nginx

# 3. Configure Firewall (UFW)
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow http
ufw allow https
ufw --force enable

# 4. Configure Fail2Ban
cat <<EOT > /etc/fail2ban/jail.local
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
EOT
systemctl restart fail2ban

# 5. Install Docker & Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
curl -L "https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# 6. Setup Directory & Clone
mkdir -p /var/www/mamun-erp
cd /var/www/mamun-erp

echo "Server is fully hardened and ready for Git clone and Docker execution."
echo "Next steps:"
echo "1. git clone <your-repo> /var/www/mamun-erp"
echo "2. run ./deploy.sh"
echo "3. Configure certbot: certbot --nginx -d mamunerp.com -d *.mamunerp.com"

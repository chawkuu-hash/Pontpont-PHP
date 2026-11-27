---
name: bluehost-ssh
description: >-
  Connect to and manage the Bluehost web hosting server via SSH, SCP, and SFTP.
  Use this skill whenever deploying files, syncing updates, checking error logs,
  or interacting with the Bluehost production server for fgkijemy (50.6.52.211).
---

# Bluehost SSH & SFTP Management Skill

This skill provides verified connection configurations and procedures for managing and deploying the Pont Pont Handbag Shop to the Bluehost production hosting server.

## Server Credentials & Connection Info

- **Host IP:** `50.6.52.211`
- **Domain:** `fgk.ije.mybluehost.me` (Primary web domain)
- **Username:** `fgkijemy`
- **Password:** `}b3N{Al&p}gp;lv1`
- **SSH / SFTP Ports:** `22` or `2222` (Both open and responsive)
- **Web Root:** `/home1/fgkijemy/public_html`
- **MySQL Database:** `fgkijemy_pontpont` (User: `fgkijemy_WPufm`, Host: `localhost`, Port: `3306`)

---

## Bluehost Shell & SFTP Behavior Notes

> [!IMPORTANT]
> - **Interactive Shell (SSH Terminal):** On Bluehost shared hosting accounts, interactive shell access (`bash`) is disabled by default returning `"Shell access is not enabled on your account!"`.
> - **SFTP & SCP:** Completely **active and unrestricted** on both port 22 and 2222.
> - Direct file uploads, directory syncs, remote backups, and log inspection work reliably using `sshpass` with `scp` and `sftp`.

---

## Standard Procedures & Commands

### 1. Fast Deploy / Sync Files to Bluehost

To deploy updated code from `Pontpont-PHP` directly into `public_html/`:

```bash
export SSHPASS='}b3N{Al&p}gp;lv1'

cd /home/ubuntu/pontpontbagshop/Pontpont-PHP
sshpass -e scp -r -o StrictHostKeyChecking=no \
  index.php admin.php profile.php install.php .htaccess README.md plan.md \
  config api database assets \
  fgkijemy@50.6.52.211:public_html/
```

### 2. Inspect Remote Directory via SFTP

```bash
export SSHPASS='}b3N{Al&p}gp;lv1'
echo "ls -la public_html" | sshpass -e sftp -o StrictHostKeyChecking=no fgkijemy@50.6.52.211
```

### 3. Fetch and Inspect Server Error Logs

```bash
export SSHPASS='}b3N{Al&p}gp;lv1'
sshpass -e sftp -o StrictHostKeyChecking=no fgkijemy@50.6.52.211 << 'EOF'
get public_html/error_log /tmp/bluehost_error_log
EOF
tail -n 30 /tmp/bluehost_error_log
```

### 4. Verify Live Web Site

```bash
curl -sI https://fgk.ije.mybluehost.me
curl -sL https://fgk.ije.mybluehost.me/api/shop.php | head -c 100
```

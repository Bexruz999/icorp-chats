## Bash skriptni yaratish va ishga tushirish

```bash
nano /home/bexruz/domains/icorp-chats/supervisor_config_watcher.sh
# (yoki skriptni yuqoridagi koddan nusxa ko'chiring)
chmod +x /home/bexruz/domains/icorp-chats/supervisor_config_watcher.sh
```

## Systemd servis faylini yaratish

```bash
sudo nano /etc/systemd/system/supervisor_config_watcher.service
# (yoki servis faylini yuqoridagi koddan nusxa ko'chiring)
sudo systemctl daemon-reload
sudo systemctl enable supervisor_config_watcher
sudo systemctl start supervisor_config_watcher
sudo systemctl status supervisor_config_watcher
```

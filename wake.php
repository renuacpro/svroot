<?php
// Do not use this outside of a trusted environment!
if (file_exists("/tmp/wake")) {
	return;
}
exec("mkdir /tmp/htdocs");
exec("sudo curlftpfs 10.0.2.2:22400 /mnt/games -o allow_other,user=games:adobesucks");
exec("sudo curlftpfs 10.0.2.2:22400 /mnt/htdocs -o allow_other,user=htdocs:adobesucks,umask=002");
exec("sudo fuzzyfs /mnt/htdocs /tmp/htdocs -o allow_other");
if (!isset($zip)) {
	exec("sudo umount -l /var/www/localhost/htdocs");
	exec("sudo unionfs /root/base:/tmp/htdocs /var/www/localhost/htdocs -o allow_other");
}
touch("/tmp/wake");

<?php
// Do not use this outside of a trusted environment!
$zip = $_GET["file"];
require("wake.php");
$union = file_get_contents("/tmp/union");
$in_ramdisk = @filesize("/mnt/ramdisk/${zip}") === 0;
if (!$in_ramdisk && strpos($union, $zip) !== false) {
	die("ALREADY_MOUNTED");
}
if (!file_exists("/mnt/games/${zip}")) {
	http_response_code(400);
	die("NO_SUCH_FILE");
}
exec("mkdir /tmp/${zip} /tmp/_${zip}");
$size = filesize("/mnt/games/${zip}");
// optimization: copy ZIPs over 1 GB into memory
// negative size = greater than 2 GB (yay for overflows)
if (($size > (1 << 30) || $size < 0) && file_exists("/tmp/enable_ramdisk")) {
	$file = "/mnt/ramdisk/${zip}";
	exec("truncate -s 0 /mnt/ramdisk/*"); // hack to avoid unmounting
	// using curl directly is faster than copying with curlftpfs
	exec("curl -u games:adobesucks ftp://10.0.2.2:22400/${zip} -o ${file}");
	if ($in_ramdisk) {
		// if we had already mounted this file, we're done
		die("OK");
	}
} else {
	$file = "/mnt/games/${zip}";
}
exec("sudo fuse-zip -r ${file} /tmp/${zip} -o allow_other", $output, $result);
if ($result > 0) {
	http_response_code(400);
	die("BAD_ZIP");
}
exec("sudo fuzzyfs /tmp/${zip} /tmp/_${zip} -o allow_other");
$content = exec("find /tmp/_${zip} -name content");
if (empty($content)) {
	http_response_code(400);
	die("NO_CONTENT_FOLDER");
}
$union = "${content}:${union}";
exec("sudo umount -l /var/www/localhost/htdocs");
exec("sudo unionfs '/root/base:/tmp/htdocs:${union}' /var/www/localhost/htdocs -o allow_other");
file_put_contents("/tmp/union", $union);
echo "OK";

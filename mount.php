<?php
// Do not use this outside of a trusted environment!
function find_device_by_serial($serial) {
	$dir = opendir("/sys/block");
	while (false !== ($entry = readdir($dir))) {
		if ($entry == "." or $entry == "..") {
			continue;
		}
		$found = @file_get_contents("/sys/block/${entry}/serial");
		if ($serial === $found) {
			closedir($dir);
			return $entry;
		}
	}
	closedir($dir);
}
$serial = $_GET["file"];
$attempts = 100;
$device = find_device_by_serial($serial);
while (!$device and $attempts--) {
	usleep(100000);
	$device = find_device_by_serial($serial);
}
if (!$device) {
	http_response_code(400);
	die("NO_SUCH_FILE");
}
$union = file_get_contents("/tmp/union");
if (strpos($union, $device) !== false) {
	die("ALREADY_MOUNTED");
}
error_log("Mounting /dev/${device}");
exec("mkdir /tmp/${device} /tmp/_${device}");
exec("sudo fuse-zip -r /mnt/dev/${device} /tmp/${device} -o allow_other", $output, $result);
if ($result > 0) {
	http_response_code(400);
	die("BAD_ZIP");
}
exec("sudo fuzzyfs /tmp/${device} /tmp/_${device} -o allow_other");
$content = exec("find /tmp/_${device} -name content");
if (empty($content)) {
	http_response_code(400);
	die("NO_CONTENT_FOLDER");
}
$union = "${content}:${union}";
exec("sudo umount -l /var/www/localhost/htdocs");
exec("sudo unionfs '/root/base:${union}' /var/www/localhost/htdocs -o allow_other");
file_put_contents("/tmp/union", $union);
echo "OK";

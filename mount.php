<?php
// Do not use this outside of a trusted environment!
$zip = $_GET["file"];
require("wake.php");
$union = file_get_contents("/tmp/union");
if (strpos($union, $zip) !== false) {
	die("ALREADY_MOUNTED");
}
if (!file_exists("/mnt/games/${zip}")) {
	http_response_code(400);
	die("NO_SUCH_FILE");
}
exec("mkdir /tmp/${zip} /tmp/_${zip}");
exec("sudo fuse-zip -r /mnt/games/${zip} /tmp/${zip} -o allow_other", $output, $result);
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

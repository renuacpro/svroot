<?php
$uuid = $_GET["file"];
while (!file_exists("/tmp/${uuid}")) {
    sleep(1);
}
sleep(1);
echo "OK";

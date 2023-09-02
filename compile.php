<?php
@unlink(__DIR__ . '/compiled.php');
$contents = "";
exec("find * | grep '.php'", $files);
foreach ($files as $file) {
    if ($file == "compile.php") continue;
    $contents .= "File: $file:\n" . file_get_contents($file) . "\n";
}
file_put_contents(__DIR__ . '/compiled.php', $contents);

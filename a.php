<?php

$a = base64_encode(file_get_contents('icon.png'));
file_put_contents("base64encode", $a);

?>

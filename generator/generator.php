<?php
require "commandLine.php";

$arguments = getopt("mvc");

print_r($arguments);
printLine("Generator version 1.0.");
<?php

#!/usr/bin/env php

$virtual_hosts_dir = "/etc/apache2/sites-available/";
if (!is_dir($virtual_hosts_dir) || !is_writable($virtual_hosts_dir)) {
    echo "You must run this script as root!\n";
    exit;
}

$default_doc_root = "/home/vladislav/web";
$server_alias = "";
$add_to_hosts = null;
$document_root = "";

if ($argc > 1) {
    for ($i = 1; $i < $argc; $i++) {
        $option = explode("=", $argv[$i]);
        switch ($option[0]) {
            case "-h":
            case "--add-to-hosts":
                $add_to_hosts = true;
                break;

            case "-n":
            case "--no-add-to-hosts":
                $add_to_hosts = false;
                break;

            case "-a":
            case "--server-alias":
                if (isset($option[1])) {
                    $server_alias = $option[1];
                } else {
                    echo "Wrong option: {$argv[$i]}\n";
                }
                break;

            case "-d":
            case "--document-root":
                if (isset($option[1])) {
                    if ($option[1] == "default") {
                        $document_root = $default_doc_root;
                    } else if (is_dir(dirname($option[1]))) {
                        $document_root = $option[1];
                    }
                } else {
                    echo "Wrong option: {$argv[$i]}\n";
                }
                break;

            default:
                if (substr($argv[$i], 1, 1) == '-') {
                    echo "Unknown option: {$argv[$i]}\n";
                }
                break;
        }
    }
}

while (!$server_alias) {
    echo "Enter your hostname: ";
    $server_alias = trim(fgets(STDIN));
}

if ($add_to_hosts === null) {
    echo "Add $server_alias to your /etc/hosts ? (Y/N) [Y]: ";
    $line = trim(fgets(STDIN));
    if ($line == 'n' || $line == 'N') {
        $add_to_hosts = false;
    } else {
        $add_to_hosts = true;
    }
}

if (!$document_root) {
    $default_doc_root = $default_doc_root . '/' . $server_alias;
    echo "Enter your document root [$default_doc_root]: ";
    $line = trim(fgets(STDIN));
    if ($line && is_dir(dirname($line))) {
        $document_root = $line;
    } else {
        $document_root = $default_doc_root;
    }
}

if (!is_dir($document_root)) {
    mkdir($document_root);
}

if ($add_to_hosts) {
    $hosts = file_get_contents("/etc/hosts");
    $hosts .= "127.0.0.1\t$server_alias\n";
    file_put_contents("/etc/hosts", $hosts);
}

$host_template = <<<HOST
<VirtualHost *:80>
ServerAdmin i@bogus.in
ServerAlias $server_alias

DocumentRoot $document_root
<Directory $document_root>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
</Directory>

ErrorLog \${APACHE_LOG_DIR}/$server_alias-error.log;
LogLevel warn
CustomLog \${APACHE_LOG_DIR}/$server_alias-access.log combined
</VirtualHost>
HOST;

file_put_contents("/etc/apache2/sites-available/$server_alias", $host_template);
echo "Apache config for this hostname created successfully! Don't forget to run a2ensite $server_alias\n";
?>

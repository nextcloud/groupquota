<?php

use OCA\GroupQuota\AppInfo\Application;

$application = \OC::$server->query(Application::class);
$application->register();

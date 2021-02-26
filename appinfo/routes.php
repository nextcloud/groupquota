<?php

return [
	'routes' => [
		['name' => 'Quota#setQuota', 'url' => '/quota/{groupId}', 'verb' => 'POST'],
		['name' => 'Quota#getQuota', 'url' => '/quota/{groupId}', 'verb' => 'GET'],
	],
];

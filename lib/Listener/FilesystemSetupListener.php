<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\GroupQuota\Listener;

use OC\Files\Filesystem;
use OCA\GroupQuota\Quota\QuotaManager;
use OCA\GroupQuota\Quota\UsedSpaceCalculator;
use OCA\GroupQuota\Wrapper\GroupQuotaWrapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\BeforeFileSystemSetupEvent;
use OCP\Files\FileInfo;
use OCP\Files\IHomeStorage;
use OCP\Files\Storage\IStorage;
use OCP\IGroupManager;
use OCP\ILogger;
use Psr\Log\LoggerInterface;

/** @template-implements IEventListener<BeforeFileSystemSetupEvent> */
class FilesystemSetupListener implements IEventListener {
	public function __construct(
		private readonly QuotaManager $quotaManager,
		private readonly UsedSpaceCalculator $usedSpaceCalculator,
		private readonly IGroupManager $groupManager,
		private readonly LoggerInterface $logger,
	) {

	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeFileSystemSetupEvent) {
			return;
		}
		Filesystem::addStorageWrapper('groupquota', function (string $mountPoint, IStorage $storage) {
			if ($storage->instanceOfStorage(IHomeStorage::class)) {
				/** @var IHomeStorage $storage */
				$user = $storage->getUser();
				[$groupId, $quota] = $this->quotaManager->getUserQuota($user);
				if ($quota !== FileInfo::SPACE_UNLIMITED && $groupId !== '') {
					$group = $this->groupManager->get($groupId);
					if (!$group) {
						$this->logger->log(ILogger::DEBUG, "Group not found: $groupId", ['app' => 'groupquota']);
						return $storage;
					}
					return new GroupQuotaWrapper([
						'storage' => $storage,
						'root_size' => $this->usedSpaceCalculator->getUsedSpaceByGroup($group),
						'quota' => $quota,
						'root' => 'files'
					]);
				}
			}
			return $storage;
		});
	}


}

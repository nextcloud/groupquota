<?php
/**
 * @copyright Copyright (c) 2018 Robin Appelman <robin@icewind.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\GroupQuota\AppInfo;

use OCA\GroupQuota\Quota\UsedSpaceCalculator;
use OCA\GroupQuota\Quota\QuotaManager;
use OCA\GroupQuota\Wrapper\GroupUsedSpaceWrapper;
use OCP\AppFramework\App;
use OCP\Util;

class Application extends App {
	public function __construct(array $urlParams = []) {
		parent::__construct('groupquota', $urlParams);
	}

	/**
	 * @return UsedSpaceCalculator
	 */
	private function getUsedSpaceCalculator() {
		return $this->getContainer()->query(UsedSpaceCalculator::class);
	}

	/**
	 * @return QuotaManager
	 */
	private function getQuotaManager() {
		return $this->getContainer()->query(QuotaManager::class);
	}

	public function register() {
		Util::connectHook('OC_Filesystem', 'preSetup', $this, 'addStorageWrapper');
	}

	public function addStorageWrapper() {
		\OC\Files\Filesystem::addStorageWrapper('groupquota', function ($mountPoint, $storage) {
			/**
			 * @var \OC\Files\Storage\Storage $storage
			 */
			if ($storage->instanceOfStorage('\OC\Files\Storage\Home')
				|| $storage->instanceOfStorage('\OC\Files\ObjectStore\HomeObjectStoreStorage')
			) {
				/** @var \OC\Files\Storage\Home $storage */
				if (is_object($storage->getUser())) {
					$user = $storage->getUser();
					list($groupId, $quota) = $this->getQuotaManager()->getUserQuota($user);
					if ($quota !== \OCP\Files\FileInfo::SPACE_UNLIMITED) {
						$group = $this->getContainer()->getServer()->getGroupManager()->get($groupId);
						$userSpaceWrapper = new GroupUsedSpaceWrapper([
							'storage' => $storage,
							'root_size' => $this->getUsedSpaceCalculator()->getUsedSpaceByGroup($group),
							'root' => 'files'
						]);
						return new \OC\Files\Storage\Wrapper\Quota([
							'storage' => $userSpaceWrapper,
							'quota' => $quota,
							'root' => 'files'
						]);
					}
				}
			}
			return $storage;
		});
	}
}

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

namespace OCA\GroupQuota\Quota;

use OCP\Files\FileInfo;
use OCP\IConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUser;

class QuotaManager {
	/** @var IConfig */
	private $config;

	/** @var IGroupManager */
	private $groupManager;

	public function __construct(IConfig $config, IGroupManager $groupManager) {
		$this->config = $config;
		$this->groupManager = $groupManager;
	}

	/**
	 * @param string $groupId
	 * @return int
	 */
	public function getGroupQuota($groupId) {
		return $this->config->getAppValue('groupquota', 'quota_' . $groupId, FileInfo::SPACE_UNLIMITED) + 0;
	}

	public function getUserQuota(IUser $user) {
		$groups = $this->groupManager->getUserGroups($user);
		$groupQuotas = array_map(function (IGroup $group) {
			return $this->getGroupQuota($group->getGID());
		}, $groups);
		foreach ($groupQuotas as $group => $quota) {
			if ($quota >= 0) {
				return [$group, $quota];
			}
		}
		return ['', FileInfo::SPACE_UNLIMITED];
	}

	/**
	 * @param string $groupId
	 * @param int $quota
	 */
	public function setGroupQuota($groupId, $quota) {
		$this->config->setAppValue('groupquota', 'quota_' . $groupId, $quota);
	}
}

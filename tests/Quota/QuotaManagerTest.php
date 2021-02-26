<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019 Robin Appelman <robin@icewind.nl>
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

namespace OCA\groupquota\tests\Quota;

use OCA\GroupQuota\Quota\QuotaManager;
use OCP\Files\FileInfo;
use OCP\IConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUser;
use Test\TestCase;

class QuotaManagerTest extends TestCase {
	private function getConfig($quotas): IConfig {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')
			->willReturnCallback(function ($app, $key) use ($quotas) {
				$group = substr($key, strlen('quota_'));
				return isset($quotas[$group]) ? $quotas[$group] : FileInfo::SPACE_UNLIMITED;
			});
		return $config;
	}

	private function getGroupManager($groups): IGroupManager {
		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('getUserGroups')
			->willReturnCallback(function (IUser $user) use ($groups) {
				$groupIds = isset($groups[$user->getUID()]) ? $groups[$user->getUID()] : [];
				$groups = array_map(function (string $groupId) {
					return $this->getGroup($groupId);
				}, $groupIds);
				return array_combine($groupIds, $groups);
			});
		return $groupManager;
	}

	private function getGroup(string $groupId): IGroup {
		$group = $this->createMock(IGroup::class);
		$group->method('getGID')
			->willReturn($groupId);
		return $group;
	}

	private function getUser(string $userId): IUser {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')
			->willReturn($userId);
		return $user;
	}

	public function testGetUserQuota() {
		$config = $this->getConfig([
			'foo' => 1024,
		]);
		$groupManager = $this->getGroupManager([
			'bar' => ['asd', 'foo'],
		]);

		$quotaManager = new QuotaManager($config, $groupManager);

		$this->assertEquals(1024, $quotaManager->getGroupQuota('foo'));
		$this->assertEquals(FileInfo::SPACE_UNLIMITED, $quotaManager->getGroupQuota('asd'));

		$this->assertEquals(['foo', 1024], $quotaManager->getUserQuota($this->getUser('bar')));
	}

	public function testGetUserQuotaNone() {
		$config = $this->getConfig([
			'foo' => 1024,
		]);
		$groupManager = $this->getGroupManager([
			'bar' => ['asd'],
		]);

		$quotaManager = new QuotaManager($config, $groupManager);

		$this->assertEquals(['', FileInfo::SPACE_UNLIMITED], $quotaManager->getUserQuota($this->getUser('bar')));
	}
}

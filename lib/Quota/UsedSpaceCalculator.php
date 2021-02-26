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

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IGroup;
use OCP\IUser;

class UsedSpaceCalculator {
	/** @var IDBConnection */
	private $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param IGroup $group
	 * @return integer
	 */
	public function getUsedSpaceByGroup(IGroup $group) {
		$users = $group->getUsers();
		if (count($users) === 0) {
			return 0;
		}

		$mountPoints = array_map(function (IUser $user) {
			return '/' . $user->getUID() . '/';
		}, $users);

		$query = $this->connection->getQueryBuilder();

		$mountPointArguments = array_map(function ($mountPoint) use ($query) {
			return $query->createNamedParameter($mountPoint);
		}, $mountPoints);

		$query->select($query->func()->sum('size'))
			->from('filecache', 'f')
			->innerJoin('f', 'mounts', 'm', $query->expr()->andX(
				$query->expr()->eq('storage_id', 'storage'),
				$query->expr()->eq('path_hash', $query->createNamedParameter(md5('files')))
			))
			->where($query->expr()->in('mount_point', $mountPointArguments))
			->andWhere($query->expr()->gte('size', $query->expr()->literal(0, IQueryBuilder::PARAM_INT)));

		return $query->execute()->fetchColumn() + 0;
	}
}

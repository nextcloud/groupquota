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

namespace OCA\GroupQuota\Wrapper;

use OC\Files\Cache\Wrapper\CacheWrapper;
use OCP\Files\Cache\ICache;

class GroupUsedSpaceCacheWrapper extends CacheWrapper {
	/** @var string */
	private $root;

	/** @var int */
	private $rootSize;

	/**
	 * GroupUsedSpaceCacheWrapper constructor.
	 *
	 * @param ICache $cache
	 * @param string $root
	 * @param int $rootSize
	 */
	public function __construct(ICache $cache, string $root, int $rootSize) {
		parent::__construct($cache);
		$this->root = $root;
		$this->rootSize = $rootSize;
	}

	public function get($file) {
		$data = parent::get($file);
		if ($file === $this->root) {
			$data['size'] = $this->rootSize;
		}
		return $data;
	}
}

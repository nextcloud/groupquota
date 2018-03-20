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

namespace OCA\GroupQuota\Controller;

use OCA\GroupQuota\Quota\QuotaManager;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class QuotaController extends OCSController {
	private $quotaManager;

	public function __construct(
		$AppName,
		IRequest $request,
		QuotaManager $quotaManager
	) {
		parent::__construct($AppName, $request);
		$this->quotaManager = $quotaManager;
	}

	/**
	 * @param string $groupId
	 * @param string $quota
	 * @return DataResponse
	 */
	public function setQuota($groupId, $quota) {
		$quotaBytes = \OC_Helper::computerFileSize($quota);
		if (!$quotaBytes) {
			throw new \InvalidArgumentException('Invalid quota');
		}
		$this->quotaManager->setGroupQuota($groupId, $quotaBytes);
		return new DataResponse([
			'quota_bytes' => $quotaBytes,
			'quota_human' => \OC_Helper::humanFileSize($quotaBytes)
		]);
	}

	/**
	 * @param string $groupId
	 * @return DataResponse
	 */
	public function getQuota($groupId) {
		$quotaBytes = $this->quotaManager->getGroupQuota($groupId);
		return new DataResponse([
			'quota_bytes' => $quotaBytes,
			'quota_human' => \OC_Helper::humanFileSize($quotaBytes)
		]);
	}
}

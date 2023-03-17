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
use OCA\GroupQuota\Quota\UsedSpaceCalculator;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSBadRequestException;
use OCP\AppFramework\OCSController;
use OCP\IGroupManager;
use OCP\IRequest;

class QuotaController extends OCSController {
	private QuotaManager $quotaManager;
	private UsedSpaceCalculator $usedSpaceCalculator;
	private IGroupManager $groupManager;

	public function __construct(
		$AppName,
		IRequest $request,
		QuotaManager $quotaManager,
		UsedSpaceCalculator $usedSpaceCalculator,
		IGroupManager $groupManager
	) {
		parent::__construct($AppName, $request);
		$this->quotaManager = $quotaManager;
		$this->usedSpaceCalculator = $usedSpaceCalculator;
		$this->groupManager = $groupManager;
	}

	public function setQuota(string $groupId, $quota): DataResponse {
		$group = $this->groupManager->get($groupId);
		if (!$group) {
			throw new OCSBadRequestException('Group not found: ' . $groupId);
		}
		$quotaBytes = \OC_Helper::computerFileSize($quota);
		if (!$quotaBytes) {
			throw new OCSBadRequestException('Invalid quota');
		}
		$this->quotaManager->setGroupQuota($groupId, $quotaBytes);
		$used = $this->usedSpaceCalculator->getUsedSpaceByGroup($group);
		return $this->buildQuotaResponse($quotaBytes, $used);
	}

	public function getQuota(string $groupId): DataResponse {
		$group = $this->groupManager->get($groupId);
		if (!$group) {
			throw new OCSBadRequestException('Group not found: ' . $groupId);
		}
		$quotaBytes = $this->quotaManager->getGroupQuota($groupId);
		$used = $this->usedSpaceCalculator->getUsedSpaceByGroup($group);
		return $this->buildQuotaResponse($quotaBytes, $used);
	}

	private function buildQuotaResponse($quotaBytes, $used): DataResponse {
		return new DataResponse([
			'quota_bytes' => $quotaBytes,
			'quota_human' => \OC_Helper::humanFileSize($quotaBytes),
			'used_bytes' => $used,
			'used_human' => \OC_Helper::humanFileSize($used),
			'used_relative' => round($used / $quotaBytes * 100, 2)
		]);
	}
}

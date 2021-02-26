<?php
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

namespace OCA\GroupQuota\Command;

use OC\Core\Command\Base;
use OCA\GroupQuota\Quota\QuotaManager;
use OCA\GroupQuota\Quota\UsedSpaceCalculator;
use OCP\Files\FileInfo;
use OCP\IGroupManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetQuota extends Base {
	private $quotaManager;
	private $usedSpaceCalculator;
	private $groupManager;

	public function __construct(
		IGroupManager $groupManager,
		QuotaManager $quotaManager,
		UsedSpaceCalculator $usedSpaceCalculator
	) {
		parent::__construct();
		$this->groupManager = $groupManager;
		$this->quotaManager = $quotaManager;
		$this->usedSpaceCalculator = $usedSpaceCalculator;
	}

	protected function configure() {
		$this
			->setName('groupquota:set')
			->setDescription('Set the configured quota for a group')
			->addArgument('name', InputArgument::REQUIRED, 'Name of the group')
			->addArgument('quota', InputArgument::REQUIRED, 'The quota to set for the group')
			->addOption('format', 'f', InputOption::VALUE_NONE, 'Format the quota to be "human readable"');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$groupId = $input->getArgument('name');
		$group = $this->groupManager->get($groupId);
		if (!$group) {
			$output->writeln("<error>Group not found: $groupId</error>");
			return -1;
		}
		$quota = \OC_Helper::computerFileSize($input->getArgument('quota'));
		$this->quotaManager->setGroupQuota($groupId, $quota);
		if ($input->getOption('format')) {
			$quota = $quota === FileInfo::SPACE_UNLIMITED ? 'Unlimited' : \OC_Helper::humanFileSize($quota);
		}
		$output->writeln((string)$quota);

		return 0;
	}
}

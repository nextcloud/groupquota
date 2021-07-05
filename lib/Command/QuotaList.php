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
use OCP\IGroupManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QuotaList extends Base {
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
			->setName('groupquota:list')
			->setDescription('Lists all configured quotas')
			->addOption('format', 'f', InputOption::VALUE_NONE, 'Format the quota to be "human readable"');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$quotas = $this->quotaManager->getQuotaList();
		$output->writeln("Group: Quota");
		foreach ($quotas as $groupId => $quota) {
			$quotaTxt = $input->getOption('format') ? \OC_Helper::humanFileSize($quota) : $quota;
			$output->writeln($groupId . ": " . $quotaTxt);
		}
		return 0;
	}
}

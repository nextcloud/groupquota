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
use Symfony\Component\Console\Terminal;

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
		# $output->writeln("Group: Quota");

		$terminal = new Terminal();
		$terminalWidth = $terminal->getWidth();

		# four headers: group, used, free, total quota
		$columnWidth = intdiv($terminalWidth, 4);
		$widths = [$terminalWidth - 3 * $columnWidth - 3, $columnWidth, $columnWidth, $columnWidth];

		# header
		$text = $this->formatTableRow(['group', 'free', 'used', 'total'], $widths);
		$output->writeln($text);
		$text = $this->formatTableRow(['', '', '', ''], $widths, '-');
		$output->writeln($text);

		# content
		foreach ($quotas as $groupId => $quota) {
			$group = $this->groupManager->get($groupId);
			$used = $this->usedSpaceCalculator->getUsedSpaceByGroup($group);
			$free = $quota - $used;
			$quotaTxt = $input->getOption('format') ? \OC_Helper::humanFileSize($quota) : $quota;
			$usedTxt = $input->getOption('format') ? \OC_Helper::humanFileSize($used) : $used;
			$freeTxt = $input->getOption('format') ? \OC_Helper::humanFileSize($free) : $free;

			$texts = [$groupId, $freeTxt, $usedTxt, $quotaTxt];
			$text = $this->formatTableRow($texts, $widths);
			$output->writeln($text);
		}
		return 0;
	}

	private function ellipseAndPadText(string $text, int $width, string $sep = ' '): string {
		$text = str_replace(["\r", "\n"], ' ', $text);
		$text = str_pad($text, $width, $sep, STR_PAD_RIGHT);
		$text = strlen($text) > $width ? substr($text, 0, $width - 2) . ' …' : $text;
		return $text;
	}

	private function formatTableRow(array $texts, array $widths, string $sep = ' '): string {
		$callback = function ($a, $b) use ($sep) {
			return $this->ellipseAndPadText($a, $b, $sep);
		};
		$formattedTexts = array_map(
			$callback,
			$texts,
			$widths
		);
		return implode('|', $formattedTexts);
	}
}

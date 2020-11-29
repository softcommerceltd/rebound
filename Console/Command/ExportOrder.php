<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Console\Command;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use SoftCommerce\Rebound\Service\OrderExportInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportOrder
 * @package SoftCommerce\Rebound\Console\Command
 */
class ExportOrder extends AbstractCommand
{
    const COMMAND_NAME = 'softcommerce_rebound:export_order';
    const ID_FILTER = 'id';
    const DATE_FILTER = 'date';
    const STATUS_FILTER = 'status';
    const ENTITY_FILTER = 'entity';

    /**
     * @var OrderExportInterface
     */
    private OrderExportInterface $orderExportService;

    /**
     * ExportOrder constructor.
     * @param OrderExportInterface $orderExportService
     * @param State $appState
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @param null $name
     */
    public function __construct(
        OrderExportInterface $orderExportService,
        State $appState,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        $name = null
    ) {
        $this->orderExportService = $orderExportService;
        parent::__construct($appState, $searchCriteriaBuilder, $filterGroupBuilder, $filterBuilder, $name);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Exports Order To Rebound.')
            ->setDefinition([
                new InputOption(
                    self::ID_FILTER,
                    '-i',
                    InputOption::VALUE_REQUIRED,
                    'Order Entity ID Filter [e.g. 100,101]'
                ),
                new InputOption(
                    self::DATE_FILTER,
                    '-d',
                    InputOption::VALUE_REQUIRED,
                    'Date Filter [e.g. 2020-12-31 12:00:00]'
                ),
                new InputOption(
                    self::STATUS_FILTER,
                    '-s',
                    InputOption::VALUE_REQUIRED,
                    'Status Filter [e.g processing,complete]'
                ),
                new InputOption(
                    self::ENTITY_FILTER,
                    '-e',
                    InputOption::VALUE_REQUIRED,
                    'Entity Filter [returns and/or recycling]'
                ),
            ]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->executeBefore();

        $searchCriteria = null;
        $filterGroup = [];
        if ($statusFilter = $input->getOption(self::STATUS_FILTER)) {
            $output->writeln(sprintf('<info>Exporting orders by status %s.</info>', $statusFilter));
            $statusFilter = explode(',', str_replace(' ', '', $statusFilter));
            $filter = $this->filterBuilder
                ->setField(OrderInterface::STATUS)
                ->setValue($statusFilter)
                ->setConditionType('in')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();
        }

        if ($idFilter = $input->getOption(self::ID_FILTER)) {
            $output->writeln(sprintf('<info>Exporting orders by ID(s) %s.</info>', $idFilter));
            $idFilter = explode(',', str_replace(' ', '', $idFilter));
            $filter = $this->filterBuilder
                ->setField(OrderInterface::ENTITY_ID)
                ->setValue($idFilter)
                ->setConditionType('in')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();
        }

        if ($dateFilter = $input->getOption(self::DATE_FILTER)) {
            $output->writeln(sprintf('<info>Exporting orders by date %1.</info>', $dateFilter));
            $filter = $this->filterBuilder
                ->setField(OrderInterface::CREATED_AT)
                ->setValue($dateFilter)
                ->setConditionType('gteq')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();
        }

        if ($entityFilter = $input->getOption(self::ENTITY_FILTER)) {
            $output->writeln(sprintf('<info>Exporting orders by entity type %1.</info>', $dateFilter));
            $entityFilter = explode(',', str_replace(' ', '', $entityFilter));
            $this->orderExportService->setEntityFilter($entityFilter);
        }

        if (!empty($filterGroup)) {
            $this->orderExportService->setSearchCriteria(
                $this->searchCriteriaBuilder
                    ->setFilterGroups($filterGroup)
                    ->create()
            );
        }

        try {
            $this->orderExportService->execute();
            if (count($this->orderExportService->getResponse()) < 100) {
                $this->executeAfter($output, $this->orderExportService->getResponse());
            } else {
                $output->writeln('<info>Export complete. Referrer to log for more details %1.</info>');
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}

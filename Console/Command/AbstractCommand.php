<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Console\Command;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use SoftCommerce\Rebound\Model\Source\Status;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @package SoftCommerce\Rebound\Console\Command
 */
class AbstractCommand extends Command
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var State
     */
    protected State $appState;

    /**
     * AbstractCommand constructor.
     * @param State $appState
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @param null $name
     */
    public function __construct(
        State $appState,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        $name = null
    ) {
        $this->appState = $appState;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($name);
    }

    /**
     * @throws LocalizedException
     */
    public function executeBefore()
    {
        $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        return $this;
    }

    /**
     * @param OutputInterface $output
     * @param array $response
     * @return $this
     */
    protected function executeAfter(OutputInterface $output, array $response)
    {
        if (!is_array($response)) {
            $output->writeln(sprintf("<error>{$response}</error>"));
            return $this;
        }

        foreach ($response as $status => $message) {
            if (is_array($message)) {
                $this->executeAfter($output, $message);
                continue;
            }

            if ($message instanceof Phrase) {
                $message = $message->render();
            }

            if ($status === Status::ERROR) {
                $output->writeln(sprintf("<error>{$message}</error>"));
            } elseif ($status === Status::WARNING) {
                $output->writeln(sprintf("<warning>{$message}</warning>"));
            } elseif ($status === Status::NOTICE) {
                $output->writeln(sprintf("<notice>{$message}</notice>"));
            } else {
                $output->writeln(sprintf("<info>{$message}</info>"));
            }
        }

        return $this;
    }
}

<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Component\Grid\Definition;

use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ArrayToDefinitionConverter implements ArrayToDefinitionConverterInterface
{
    public const EVENT_NAME = 'sylius.grid.%s';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(string $code, array $configuration): Grid
    {
        $grid = Grid::fromCodeAndDriverConfiguration(
            $code,
            $configuration['driver']['name'],
            $configuration['driver']['options']
        );

        $grid->setSorting($configuration['sorting'] ?? []);
        $grid->setLimits($configuration['limits'] ?? []);

        foreach ($configuration['fields'] as $name => $fieldConfiguration) {
            $grid->addField($this->convertField($name, $fieldConfiguration));
        }

        foreach ($configuration['filters'] as $name => $filterConfiguration) {
            $grid->addFilter($this->convertFilter($name, $filterConfiguration));
        }

        foreach ($configuration['actions'] as $name => $actionGroupConfiguration) {
            $grid->addActionGroup($this->convertActionGroup($name, $actionGroupConfiguration));
        }

        $this->eventDispatcher->dispatch($this->getEventName($code), new GridDefinitionConverterEvent($grid));

        return $grid;
    }

    /**
     * @param string $name
     * @param array $configuration
     *
     * @return Field
     */
    private function convertField(string $name, array $configuration): Field
    {
        $field = Field::fromNameAndType($name, $configuration['type']);

        if (isset($configuration['path'])) {
            $field->setPath($configuration['path']);
        }
        if (isset($configuration['label'])) {
            $field->setLabel($configuration['label']);
        }
        if (isset($configuration['enabled'])) {
            $field->setEnabled($configuration['enabled']);
        }
        if (array_key_exists('sortable', $configuration)) {
            $sortable = false === $configuration['sortable'] ? null : $name;

            $field->setSortable($sortable);
        }
        if (isset($configuration['position'])) {
            $field->setPosition($configuration['position']);
        }
        if (isset($configuration['options'])) {
            $field->setOptions($configuration['options']);
        }

        return $field;
    }

    /**
     * @param string $name
     * @param array $configuration
     *
     * @return Filter
     */
    private function convertFilter(string $name, array $configuration): Filter
    {
        $filter = Filter::fromNameAndType($name, $configuration['type']);

        if (isset($configuration['label'])) {
            $filter->setLabel($configuration['label']);
        }
        if (isset($configuration['template'])) {
            $filter->setTemplate($configuration['template']);
        }
        if (isset($configuration['enabled'])) {
            $filter->setEnabled($configuration['enabled']);
        }
        if (isset($configuration['position'])) {
            $filter->setPosition($configuration['position']);
        }
        if (isset($configuration['options'])) {
            $filter->setOptions($configuration['options']);
        }
        if (isset($configuration['form_options'])) {
            $filter->setFormOptions($configuration['form_options']);
        }
        if (isset($configuration['default_value'])) {
            $filter->setCriteria($configuration['default_value']);
        }

        return $filter;
    }

    /**
     * @param string $name
     * @param array $configuration
     *
     * @return ActionGroup
     */
    private function convertActionGroup(string $name, array $configuration): ActionGroup
    {
        $actionGroup = ActionGroup::named($name);

        foreach ($configuration as $actionName => $actionConfiguration) {
            $actionGroup->addAction($this->convertAction($actionName, $actionConfiguration));
        }

        return $actionGroup;
    }

    /**
     * @param string $name
     * @param array $configuration
     *
     * @return Action
     */
    private function convertAction(string $name, array $configuration): Action
    {
        $action = Action::fromNameAndType($name, $configuration['type']);

        if (isset($configuration['label'])) {
            $action->setLabel($configuration['label']);
        }
        if (isset($configuration['icon'])) {
            $action->setIcon($configuration['icon']);
        }
        if (isset($configuration['enabled'])) {
            $action->setEnabled($configuration['enabled']);
        }
        if (isset($configuration['position'])) {
            $action->setPosition($configuration['position']);
        }
        if (isset($configuration['options'])) {
            $action->setOptions($configuration['options']);
        }

        return $action;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getEventName(string $code): string
    {
        return sprintf(self::EVENT_NAME, str_replace('sylius_', '', $code));
    }
}

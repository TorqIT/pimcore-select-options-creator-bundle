<?php

namespace TorqIT\SelectOptionsCreatorBundle\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\SelectOptions\Config as SelectOptionsConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Pimcore\Model\DataObject\SelectOptions\Data\SelectOption;
use Symfony\Component\Console\Input\InputOption;

class SelectOptionsGeneratorCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('torq:generate-select-options')
            ->setDescription('Generate select options from YAML file.')
            ->addOption('force-recreate-options', 'fro', InputOption::VALUE_NONE, 'Force recreate select options.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $forceRecreateOptions = $input->getOption('force-recreate-options');

        $selectOptionsYamlFileLocation = PIMCORE_PROJECT_ROOT . '/config/select_options.yaml';
        $selectOptionsArray = Yaml::parseFile($selectOptionsYamlFileLocation);

        if ($selectOptionsArray["select_options"]) {
            $selectOptions = $selectOptionsArray["select_options"];
            foreach ($selectOptions as $selectOptionId => $selectOptionProperties) {
                $output->writeln('Processing select option: ' . $selectOptionId);
                $this->createOrUpdateSelectOption($selectOptionId, $selectOptionProperties, $forceRecreateOptions);
            }
        }

        return Command::SUCCESS;
    }

    private function createOrUpdateSelectOption(string $selectOptionId, ?array $selectOptionProperties, ?bool $forceRecreateOptions = false): void
    {
        $selectOption = SelectOptionsConfig::getById($selectOptionId);
        if (!$selectOption) {
            $selectOption = new SelectOptionsConfig();
            $selectOption->setId($selectOptionId);
        }

        if (is_array($selectOptionProperties)) {
            if (array_key_exists('group', $selectOptionProperties)) {
                $group = $selectOptionProperties['group'];
                $selectOption->setGroup($group);
            }

            if (array_key_exists('traits', $selectOptionProperties)) {
                $traits = $selectOptionProperties['traits'];
                $selectOption->setUseTraits($traits ?? "");
            }

            if (array_key_exists('interfaces', $selectOptionProperties)) {
                $interfaces = $selectOptionProperties['interfaces'];
                $selectOption->setImplementsInterfaces($interfaces ?? "");
            }

            if (array_key_exists('options', $selectOptionProperties)) {
                $options = $selectOptionProperties['options'];
                if (is_array($options)) {
                    if ($forceRecreateOptions) {
                        foreach ($options as $option) {
                            $selectOption->setSelectOptions(...array_map(function ($option) {
                                return $this->createNewSelectOptionFromData($option);
                            }, $options));
                        }
                    } else {
                        foreach ($options as $option) {
                            $selectOptionIndex = (array_key_exists('name', $option) && !empty($option['name'])) ? $option['name'] : $option['value'];
                            if ($this->isSelectOptionAlreadyCreated($selectOption, $selectOptionIndex)) {
                                continue;
                            }

                            $selectOption->setSelectOptions(
                                ...[
                                    ...$selectOption->getSelectOptions(),
                                    $this->createNewSelectOptionFromData($option)
                                ]
                            );
                        }
                    }
                }
            }
        }

        $selectOption->save();
    }

    private function isSelectOptionAlreadyCreated(SelectOptionsConfig $selectOption, string $label)
    {
        $currentOptions = array_reduce($selectOption->getSelectOptions(), function ($result, SelectOption $selectOption) {
            $result[$selectOption->hasName() ? $selectOption->getName() : $selectOption->getValue()] = $selectOption;
            return $result;
        }, []);

        return array_key_exists($label, $currentOptions);
    }

    private function createNewSelectOptionFromData(array $option)
    {
        $value = array_key_exists('value', $option) ? $option['value'] : '';
        $label = array_key_exists('label', $option) ? $option['label'] : '';
        $name = array_key_exists('name', $option) ? $option['name'] : '';
        return new SelectOption($value, $label, $name);
    }
}

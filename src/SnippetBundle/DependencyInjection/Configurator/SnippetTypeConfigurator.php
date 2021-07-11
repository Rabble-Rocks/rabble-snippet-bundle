<?php

namespace Rabble\SnippetBundle\DependencyInjection\Configurator;

use Rabble\ContentBundle\DependencyInjection\Configurator\AbstractContentConfigurator;
use Rabble\FieldTypeBundle\FieldType\StringType;
use Rabble\SnippetBundle\SnippetType\SnippetType;
use Symfony\Component\Validator\Constraints\NotBlank;

class SnippetTypeConfigurator extends AbstractContentConfigurator
{
    public function configure(SnippetType $snippetType)
    {
        $fieldConfigs = $snippetType->getFields();
        $fields = [
            new StringType([
                'name' => 'title',
                'label' => 'snippet.title',
                'translatable' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a title.',
                    ]),
                ],
                'translation_domain' => 'RabbleSnippetBundle',
            ]),
        ];
        /** @var array $fieldConfig */
        foreach ($fieldConfigs as $fieldConfig) {
            $fields[] = $this->processField($fieldConfig);
        }
        $snippetType->setFields($fields);
    }
}

<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\FileReader;

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use Symfony\Component\Finder\SplFileInfo;

class ExtensionAttributesReader
{
    /**
     * @return list<array{class: class-string, type: string, code: string}>
     */
    public function getDefinitionsFromFile(SplFileInfo $file): array
    {
        $definitions = [];

        try {
            $extensionAttributes = $this->readExtensionAttributeNodesFromFile($file);

            foreach ($extensionAttributes ?? [] as $extensionAttribute) {
                if (!$extensionAttribute instanceof DOMElement) {
                    continue;
                }

                /** @var class-string $targetClass */
                $targetClass = $extensionAttribute->getAttribute('for');

                $attributesNodes = $extensionAttribute->getElementsByTagName('attribute');
                foreach ($attributesNodes as $attribute) {
                    /** @var DOMElement $attribute */
                    $definitions[] = [
                        'class' => $targetClass,
                        'code' => $attribute->getAttribute('code'),
                        'type' => $attribute->getAttribute('type'),
                    ];
                }
            }
        } catch (Exception) {
            return [];
        }

        return $definitions;
    }

    /**
     * @return iterable<DOMNode>
     */
    private function readExtensionAttributeNodesFromFile(SplFileInfo $file): ?iterable
    {
        $document = new DOMDocument();
        $document->load($file->getRealPath());

        $configNodes = $document->getElementsByTagName('config');
        if ($configNodes->length === 0) {
            return null;
        }

        /** @var DOMElement $config */
        $config = $configNodes->item(0);

        return $config->getElementsByTagName('extension_attributes');
    }
}

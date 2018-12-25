<?php

namespace CseEightselectBasic\Setup\Helpers;

use Shopware\Components\Emotion\ComponentInstaller;

class EmotionComponents
{

    /**
     * @var ComponentInstaller
     */
    private $componentInstaller;

    /**
     * @param ComponentInstaller $componentInstaller
     */
    public function __construct(ComponentInstaller $componentInstaller)
    {
        $this->componentInstaller = $componentInstaller;
    }

    public function create()
    {
        // component SYS-PSV
        $syspsvElement = $this->componentInstaller->createOrUpdate(
            $this->getName(),
            '8select SYS-PSV component',
            [
                'name' => 'SYS-PSV Component',
                'template' => 'sys_psv',
                'cls' => '8select--element--sys-psv',
                'xtype' => 'emotion-8select-syspsv-element',
            ]
        );
        $syspsvElement->createHiddenField(
            [
                'name' => 'sys_psv_ordernumber',
                'fieldLabel' => 'Product Ordernumber',
                'allowBlank' => false,
            ]
        );
        $syspsvElement->createNumberField(
            [
                'name' => 'sys_psv_lazyload_factor',
                'fieldLabel' => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText' => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
                                sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). Beispiel: 0 = Laden,
                                sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet; 1 = Laden,
                                sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereiches
                                befindet.',
                'allowBlank' => true,
            ]
        );

        // component PSP-TLV
        $psptlvElement = $this->componentInstaller->createOrUpdate(
            $this->getName(),
            '8select PSP-TLV component',
            [
                'name' => 'PSP-TLV Component',
                'template' => 'psp_tlv',
                'cls' => '8select--element--psp-tlv',
                'xtype' => 'emotion-8select-psptlv-element',
            ]
        );
        $psptlvElement->createTextField(
            [
                'name' => 'psp_tlv_stylefactor',
                'fieldLabel' => 'Stylefactor',
                'allowBlank' => false,
            ]
        );
        $psptlvElement->createNumberField(
            [
                'name' => 'psp_tlv_lazyload_factor',
                'fieldLabel' => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText' => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
                                sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). Beispiel: 0 = Laden,
                                sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet; 1 = Laden,
                                sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereiches
                                befindet.',
                'allowBlank' => true,
            ]
        );

        // component PSP-PSV
        $psppsvElement = $this->componentInstaller->createOrUpdate(
            $this->getName(),
            '8select PSP-PSV component',
            [
                'name' => 'PSP-PSV Component',
                'template' => 'psp_psv',
                'cls' => '8select--element--psp-psv',
                'xtype' => 'emotion-8select-psppsv-element',
            ]
        );
        $psppsvElement->createTextField(
            [
                'name' => 'psp_psv_set_id',
                'fieldLabel' => 'Set-ID',
                'allowBlank' => false,
            ]
        );
        $psppsvElement->createNumberField(
            [
                'name' => 'psp_psv_lazyload_factor',
                'fieldLabel' => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText' => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
                                sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). Beispiel: 0 = Laden,
                                sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet; 1 = Laden,
                                sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereiches
                                befindet.',
                'allowBlank' => true,
            ]
        );
    }
}

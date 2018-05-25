<?php
namespace CseEightselectBasic\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="8s_attribute_mapping")
 */
class EightselectAttribute extends ModelEntity
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $eightselectAttribute
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $eightselectAttribute;

    /**
     * @var string $eightselectAttributeLabel
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $eightselectAttributeLabel;

    /**
     * @var string $eightselectAttributeLabelDescr
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $eightselectAttributeLabelDescr;

    /**
     * @var string $eightselectAttributeGroupName
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $eightselectAttributeGroupName;

    /**
     * @var string $shopwareAttribute
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $shopwareAttribute;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEightselectAttribute()
    {
        return $this->eightselectAttribute;
    }

    /**
     * @return string
     */
    public function getEightselectAttributeLabel()
    {
        return $this->eightselectAttributeLabel;
    }

    /**
     * @return string
     */
    public function getEightselectAttributeLabelDescr()
    {
        return $this->eightselectAttributeLabelDescr;
    }

    /**
     * @return string
     */
    public function getEightselectAttributeGroupName()
    {
        return $this->eightselectAttributeGroupName;
    }

    /**
     * @param string $name
     */
    public function setShopwareAttribute($name)
    {
        $this->shopwareAttribute = $name;
    }

    /**
     * @return string
     */
    public function getShopwareAttribute()
    {
        return $this->shopwareAttribute;
    }
}

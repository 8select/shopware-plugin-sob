<?php
namespace EightselectCSE\Models;

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

<?php

namespace TddWizard\Fixtures\Customer;

use Faker\Factory as FakerFactory;
use InvalidArgumentException;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Directory\Model\Region;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Builder to be used by fixtures
 */
class AddressBuilder
{
    /**
     * @var AddressInterface
     */
    private $address;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    public function __construct(AddressRepositoryInterface $addressRepository, AddressInterface $address)
    {
        $this->address = $address;
        $this->addressRepository = $addressRepository;
    }

    public function __clone()
    {
        $this->address = clone $this->address;
    }

    public static function anAddress(
        ObjectManagerInterface $objectManager = null,
        string $locale = 'de_DE'
    ): AddressBuilder {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }

        $address = self::prepareFakeAddress($objectManager, $locale);
        return new self($objectManager->create(AddressRepositoryInterface::class), $address);
    }

    public static function aCompanyAddress(
        ObjectManagerInterface $objectManager = null,
        string $locale = 'de_DE',
        string $vatId = '1234567890'
    ): AddressBuilder {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }

        $address = self::prepareFakeAddress($objectManager, $locale);
        $address->setVatId($vatId);
        return new self($objectManager->create(AddressRepositoryInterface::class), $address);
    }

    public function asDefaultShipping(): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setIsDefaultShipping(true);
        return $builder;
    }

    public function asDefaultBilling(): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setIsDefaultBilling(true);
        return $builder;
    }

    public function withPrefix($prefix): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setPrefix($prefix);
        return $builder;
    }

    public function withFirstname($firstname): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setFirstname($firstname);
        return $builder;
    }

    public function withLastname($lastname): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setLastname($lastname);
        return $builder;
    }

    public function withStreet($street): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setStreet((array)$street);
        return $builder;
    }

    public function withCompany($company): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCompany($company);
        return $builder;
    }

    public function withTelephone($telephone): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setTelephone($telephone);
        return $builder;
    }

    public function withPostcode($postcode): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setPostcode($postcode);
        return $builder;
    }

    public function withCity($city): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCity($city);
        return $builder;
    }

    public function withCountryId($countryId): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCountryId($countryId);
        return $builder;
    }

    public function withRegionId($regionId): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setRegionId($regionId);
        return $builder;
    }

    public function withCustomAttributes(array $values): AddressBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            $builder->address->setCustomAttribute($code, $value);
        }
        return $builder;
    }

    /**
     * @return AddressInterface
     * @throws LocalizedException
     */
    public function build(): AddressInterface
    {
        return $this->addressRepository->save($this->address);
    }

    public function buildWithoutSave(): AddressInterface
    {
        return clone $this->address;
    }

    private static function prepareFakeAddress(
        ObjectManagerInterface $objectManager,
        string $locale = 'de_DE'
    ): AddressInterface {
        $faker = FakerFactory::create($locale);
        $countryCode = substr($locale, -2);

        try {
            $region = $faker->province;
        } catch (InvalidArgumentException $exception) {
            $region = $faker->state;
        }

        $regionId = $objectManager->create(Region::class)->loadByName($region, $countryCode)->getId();

        /** @var AddressInterface $address */
        $address = $objectManager->create(AddressInterface::class);
        $address
            ->setTelephone($faker->phoneNumber)
            ->setPostcode($faker->postcode)
            ->setCountryId($countryCode)
            ->setCity($faker->city)
            ->setCompany($faker->company)
            ->setStreet([$faker->streetAddress])
            ->setLastname($faker->lastName)
            ->setFirstname($faker->firstName)
            ->setRegionId($regionId);

        return $address;
    }
}

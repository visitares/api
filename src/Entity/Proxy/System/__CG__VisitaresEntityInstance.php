<?php

namespace Visitares\Entity\Proxy\System\__CG__\Visitares\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Instance extends \Visitares\Entity\Instance implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', 'id', 'creationDate', 'modificationDate', 'master', 'timeline', 'isActive', 'isTemplate', 'customerNumber', 'statsDayRange', 'statsMinUserCount', 'usersCountByContract', 'messageAdministration', 'logoffTimer', 'token', 'registrationToken', 'domain', 'name', 'shortDescription', 'description', 'country', 'postalCode', 'city', 'street', 'sector', 'logo', 'background', 'backgroundId', 'imageGroups', 'settings', 'cmsConfig', 'messageModule');
        }

        return array('__isInitialized__', 'id', 'creationDate', 'modificationDate', 'master', 'timeline', 'isActive', 'isTemplate', 'customerNumber', 'statsDayRange', 'statsMinUserCount', 'usersCountByContract', 'messageAdministration', 'logoffTimer', 'token', 'registrationToken', 'domain', 'name', 'shortDescription', 'description', 'country', 'postalCode', 'city', 'street', 'sector', 'logo', 'background', 'backgroundId', 'imageGroups', 'settings', 'cmsConfig', 'messageModule');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Instance $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());

        parent::__clone();
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function setSettings($settings)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSettings', array($settings));

        return parent::setSettings($settings);
    }

    /**
     * {@inheritDoc}
     */
    public function getSettings()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSettings', array());

        return parent::getSettings();
    }

    /**
     * {@inheritDoc}
     */
    public function setImageGroups($imageGroups)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setImageGroups', array($imageGroups));

        return parent::setImageGroups($imageGroups);
    }

    /**
     * {@inheritDoc}
     */
    public function getImageGroups()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getImageGroups', array());

        return parent::getImageGroups();
    }

    /**
     * {@inheritDoc}
     */
    public function setCmsConfig($cmsConfig)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCmsConfig', array($cmsConfig));

        return parent::setCmsConfig($cmsConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function getCmsConfig()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCmsConfig', array());

        return parent::getCmsConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function __call($method, $arguments)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__call', array($method, $arguments));

        return parent::__call($method, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', array());

        return parent::toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'jsonSerialize', array());

        return parent::jsonSerialize();
    }

}

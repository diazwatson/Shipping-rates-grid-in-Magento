<?php

class MageHack_ShippingRatesAdmin_Block_Adminhtml_Tablerate_Edit_Form extends Mage_Adminhtml_Block_Widget_Form{
    /** 
     * Init class
     */
    public function __construct()
    {   
        parent::__construct();
     
        $this->setId('shippingrate_form');
        $this->setTitle($this->__('Edit Shipping Rate'));
    }
    
    /** 
     * Setup form fields for inserts/updates
     * 
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {   
        $shippingrate = Mage::registry('shippingrate'); /* @var $shippingrate Varien_Object */
        $sessionData = $this->_getHelper()->getShippingRateSessionData();
        if (is_array($sessionData)) {
            $shippingrate->setData($sessionData);
            $this->_getHelper()->clearShippingRateSessionData();
        }     
           
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post',
            
        )); 
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('checkout')->__('Rate Information'),
            'class'     => 'fieldset-wide',
        )); 
        
        
       
     
        if ($shippingrate->getData('pk')) {
            $fieldset->addField('pk', 'hidden', array(
                'name' => 'pk',
            )); 
        }
        
        $websiteSelect = $fieldset->addField('website_id', 'select', array(
                'name'      => 'website_id',
                'label'     => Mage::helper('shippingratesadmin')->__('Website'),
                'title'     => Mage::helper('shippingratesadmin')->__('Website'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(false, false),
                'onchange'  => 'changeRatesWebsite()'
            ));
        
        $websiteSelect->setAfterElementHtml(
        "<script type=\"text/javascript\">
            //<![CDATA[
                function changeRatesWebsite() {           
                    var url = \"{$this->getUrl('shippingratesadmin/adminhtml_tablerate/changewebsite')}\";
                    var website = $('website_id').getValue();
                    new Ajax.Request(url, {
                        method: 'post',
                        parameters: {\"website\": website}, 
                        onSuccess: function(transport) { 
                            var jsonResponse = transport.responseJSON;
                            if (!jsonResponse) {
                                return;
                            }                     
                            var label = $$('label[for=\"condition_value\"]')[0];
                            label.innerHTML = jsonResponse.conditionLabel;
                        }
                    });                    
                }
                
                document.observe(\"dom:loaded\", function(){
                    changeRatesWebsite();
                });
            //]]>
            </script>");   
        
        
        $country = $fieldset->addField('dest_country_id', 'select', array(
            'name'  => 'dest_country_id',
            'label'     => Mage::helper('shippingratesadmin')->__('Country'),
            'values'    => Mage::getModel('adminhtml/system_config_source_country') ->toOptionArray(),
            'onchange' => 'getstate(this)',
        ));
        
        
        
        if($shippingrate->getData('dest_country_id'))
            $regions = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($shippingrate->getData('dest_country_id'))->toOptionArray();
        else
            $regions = array( 0 => 'Select Country');
                
        $fieldset->addField('dest_region_id', 'select', array(
            'name'  => 'dest_region_id',
            'label'     => Mage::helper('shippingratesadmin')->__('State/Region'),
            'values' => $regions,
            
        ));
        
        $country->setAfterElementHtml(
        "<script type=\"text/javascript\">
            function getstate(selectElement){
                var reloadurl = '". $this->getUrl('shippingratesadmin/adminhtml_tablerate/region') . "country/' + selectElement.value;
                new Ajax.Request(reloadurl, {
                    method: 'get',
                    onLoading: function (stateform) {
                        $('state').update('Searching...');
                    },
                    onComplete: function(stateform) { 
                        $('dest_region_id').update(stateform.responseText);
                    }
                });
            }
        </script>");
        
        
        $fieldset->addField('dest_zip', 'text', array(
            'name'      => 'dest_zip',
            'label'     => Mage::helper('shippingratesadmin')->__('Zip/Postal Code'),
            'title'     => Mage::helper('shippingratesadmin')->__('Zip/Postal Code'),
            'required'  => true,
            'note'  => Mage::helper('shippingratesadmin')->__('Use * for all'),
        ));
        
        
        $fieldset->addField('condition_value', 'text', array(
            'name'      => 'condition_value',
            'label'     => Mage::helper('shippingratesadmin')->__('Order Subtotal (and above)'),
            'title'     => Mage::helper('shippingratesadmin')->__('Order Subtotal (and above)'),
            'required'  => true,
        ));
     
        $fieldset->addField('price', 'text', array(
            'name'      => 'price',
            'label'     => Mage::helper('shippingratesadmin')->__('Price'),
            'title'     => Mage::helper('shippingratesadmin')->__('Price'),
            'required'  => true,
        )); 
          
       
     
        $form->setValues($shippingrate->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    } 
    
    
    /**
     * 
     * @return MageHack_ShippingRatesAdmin_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('shippingratesadmin');
    }
}
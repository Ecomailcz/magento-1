<?php

    class Ecomail_Ecomail_Helper_Data extends Mage_Core_Helper_Abstract {

        public function getAPI() {

            require_once __DIR__ . '/../lib/api.php';

            $obj = new EcomailAPI();
            $obj->setAPIKey( Mage::getStoreConfig( 'ecomail_options/properties/api_key' ) );

            return $obj;
        }

        public function getCookieNameTrackStructEvent() {
            return 'Ecomail';
        }

    }
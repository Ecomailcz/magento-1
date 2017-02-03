<?php

    class Ecomail_Ecomail_Model_OptionsListId {

        public function toOptionArray() {

            $options = array();

            if( Mage::getStoreConfig( 'ecomail_options/properties/api_key' ) ) {
                $listsCollection = Mage::helper( 'ecomail' )
                        ->getAPI()
                        ->getListsCollection();


                foreach( $listsCollection as $list ) {
                    $options[] = array(
                            'value' => $list->id,
                            'label' => $list->name
                    );
                }
            }

            return $options;
        }
    }
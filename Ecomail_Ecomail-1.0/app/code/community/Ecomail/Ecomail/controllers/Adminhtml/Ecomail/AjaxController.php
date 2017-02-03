<?php

    class Ecomail_Ecomail_Adminhtml_Ecomail_AjaxController extends Mage_Adminhtml_Controller_Action {

        public function indexAction() {

            $isAjax = Mage::app()
                          ->getRequest()
                          ->isAjax();
            if( $isAjax ) {

                $result = array();

                $cmd = $this->getRequest()
                            ->getParam( 'cmd' );
                if( $cmd == 'getLists' ) {

                    $APIKey = $this->getRequest()
                                   ->getParam( 'APIKey' );
                    if( $APIKey ) {
                        $listsCollection = Mage::helper( 'ecomail' )
                                               ->getAPI()
                                               ->setAPIKey( $APIKey )
                                               ->getListsCollection();
                        foreach( $listsCollection as $list ) {
                            $result[] = array(
                                    'id'   => $list->id,
                                    'name' => $list->name
                            );
                        }
                    }

                }


                $this->getResponse()
                     ->setBody(
                             Mage::helper( 'core' )
                                 ->jsonEncode( $result )
                     );
            }
        }
    }

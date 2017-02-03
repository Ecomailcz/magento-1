<?php

    class Ecomail_Ecomail_Model_Observer {

        public function subscribedToNewsletter( Varien_Event_Observer $observer ) {
            $event      = $observer->getEvent();
            $subscriber = $event->getDataObject();
            $data       = $subscriber->getData();

            $statusChange = $subscriber->getIsStatusChanged();

            // Trigger if user is now subscribed and there has been a status change:
            if( $data['subscriber_status'] == "1" && $statusChange == true ) {

                if( Mage::getStoreConfig( 'ecomail_options/properties/api_key' ) ) {

                    $email = $data['subscriber_email'];
                    $name  = '';

                    $id = $subscriber['customer_id'];
                    if( $id ) {
                        $customer = Mage::getModel( 'customer/customer' )
                                        ->load( $id );
                        $name     = $customer->getName();
                    }
                    
                    Mage::helper( 'ecomail' )
                        ->getApi()
                        ->subscribeToList(
                                Mage::getStoreConfig( 'ecomail_options/properties/list_id' ),
                                array(
                                        'email' => $email,
                                        'name'  => $name
                                )
                        );
                }

            }

            return $observer;
        }

        public function sales_order_afterPlace( Varien_Event_Observer $observer ) {

            $event = $observer->getEvent();
            $order = $observer->getOrder();

            $addressDelivery = $order->getShippingAddress();

            /**
             * @var Mage_Sales_Model_Order_Item $orderProduct
             * @var Mage_Catalog_Model_Product  $product
             */

            $arr = array();
            foreach( $order->getAllItems() as $orderProduct ) {
                $product     = $orderProduct->getProduct();
                $categoryIds = $product->getCategoryIds();

                $category = null;
                if( count( $categoryIds ) ) {
                    $firstCategoryId = $categoryIds[0];
                    $category        = Mage::getModel( 'catalog/category' )
                                           ->load( $firstCategoryId );

                }

                if( !isset( $orderProduct['price_incl_tax'] ) ) {
                    continue;
                }

                $arr[] = array(
                        'code'      => $orderProduct['sku'],
                        'title'     => $orderProduct['name'],
                        'category'  => $category ? $category->getName() : null,
                        'price'     => $orderProduct['price_incl_tax'],
                        'amount'    => $orderProduct['qty_ordered'],
                        'timestamp' => strtotime( $orderProduct['created_at'] )
                );
            }

            $data = array(
                    'transaction'       => array(
                            'order_id'  => $order->getId(),
                            'email'     => $order['customer_email'],
                            'shop'      => $order->getStore()
                                                 ->getBaseUrl( Mage_Core_Model_Store::URL_TYPE_LINK ),
                            'amount'    => $order['grand_total'],
                            'tax'       => $order['tax_amount'],
                            'shipping'  => $order['shipping_incl_tax'],
                            'city'      => $addressDelivery ? $addressDelivery['city'] : null,
                            'county'    => '',
                            'country'   => $addressDelivery ? $addressDelivery->getCountry() : null,
                            'timestamp' => strtotime( $order['created_at'] )
                    ),
                    'transaction_items' => $arr
            );

            $r = Mage::helper( 'ecomail' )
                     ->getApi()
                     ->createTransaction( $data );

            return $observer;
        }

        public function processAddToCart( Varien_Event_Observer $observer ) {

            $event = $observer->getEvent();
            /**
             * @var Mage_Catalog_Model_Product $product
             */
            $product = $observer->getProduct();

            $params     = Mage::app()
                              ->getRequest()
                              ->getParams();
            $quantity   = $params['qty'];
            $id_product = $product->getId();

            setcookie(
                    Mage::helper( 'ecomail' )
                        ->getCookieNameTrackStructEvent(),
                    json_encode(
                            array(
                                    'category' => 'Product',
                                    'action'   => 'AddToCart',
                                    'tag'      => implode(
                                            '|',
                                            array(
                                                    $id_product
                                            )
                                    ),
                                    'property' => 'quantity',
                                    'value'    => $quantity
                            )
                    ),
                    null,
                    Mage::app()
                        ->getRequest()
                        ->getBasePath()
            );

            return $observer;

        }

        public function createAccount( Varien_Event_Observer $observer ) {
            return $observer;
        }

        public function createAccountCheckout( Varien_Event_Observer $observer ) {

            if( $observer->getQuote()
                         ->getData( 'checkout_method' ) != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER
            ) {
                return $observer;
            }

            return $this->createAccount( $observer );

        }
    }
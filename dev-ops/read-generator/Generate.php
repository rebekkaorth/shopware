<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
(new \Symfony\Component\Dotenv\Dotenv())->load(__DIR__.'/../../.env');
require_once __DIR__ . '/../../src/Framework/Doctrine/DatabaseConnector.php';
require_once __DIR__ . '/DomainGenerator.php';
require_once __DIR__ . '/Context.php';

class Generate
{
    const ManyToOne = 'N:1';
    const ManyToMany = 'N:N';
    const OneToMany = '1:N';
    const OneToOne = '1:1';

    public function execute()
    {
        $connection = \Shopware\Framework\Doctrine\DatabaseConnector::createPdoConnection();

        $dbalConnection = new \Doctrine\DBAL\Connection(
            ['pdo' => $connection],
            new Doctrine\DBAL\Driver\PDOMySql\Driver(),
            null,
            null
        );

        $dir = __DIR__ . '/../../src';

        $generator = new DomainGenerator($dbalConnection, $dir);

        $tables = [
            'order' => [
                'associations' => [
                    self::createAssociation('customer', self::ManyToOne, true, false, 'customer', 'customer_uuid', '', '', false, true),
                    self::createAssociation('order_state', self::ManyToOne, true, false, 'state', 'order_state_uuid', '', '', false),
                    self::createAssociation('payment_method', self::ManyToOne, true, false, 'paymentMethod', 'payment_method_uuid', '', '', false, true),
                    self::createAssociation('currency', self::ManyToOne, true, false, 'currency', 'currency_uuid', '', '', false, true),
                    self::createAssociation('shop', self::ManyToOne, true, false, 'shop', 'shop_uuid', '', '', false, true),
                    self::createAssociation('order_address', self::ManyToOne, true, false, 'billingAddress', 'billing_address_uuid', '', '', false, false),
                    self::createAssociation('order_line_item', self::OneToMany, false, true, 'lineItem', 'order_uuid', '', '', false),
                    self::createAssociation('order_delivery', self::OneToMany, false, true, 'delivery', 'order_uuid', '', '', false, true),
                ]
            ],
            'order_state' => [],
            'order_line_item'  => [
                'associations' => []
            ],
            'order_address'  => [
                'associations' => [
                    self::createAssociation('area_country', self::ManyToOne, true, false, 'country', 'area_country_uuid', '', '', false),
                    self::createAssociation('area_country_state', self::ManyToOne, true, false, 'state', 'area_country_state_uuid', '', '', true),
                ]
            ],
            'order_delivery'  => [
                'associations' => [
                    self::createAssociation('order_state', self::ManyToOne, true, false, 'state', 'order_state_uuid', '', '', false, false),
                    self::createAssociation('order_address', self::ManyToOne, true, false, 'shippingAddress', 'shipping_address_uuid', '', '', false, false),
                    self::createAssociation('shipping_method', self::ManyToOne, true, false, 'shippingMethod', 'shipping_method_uuid', '', '', false, true),
                    self::createAssociation('order_delivery_position', self::OneToMany, false, true, 'position', 'order_delivery_uuid', '', '', false, false),
                ]
            ],
            'order_delivery_position'  => [
                'associations' => [
                    self::createAssociation('order_line_item', self::ManyToOne, true, false, 'lineItem', 'order_line_item_uuid', '', '', false)
                ]
            ],
            'product_media' => [
                'associations' => [
                    self::createAssociation('media', self::OneToOne, true, false, 'media', 'media_uuid'),
                ]
            ],
            'product' => [
                'seo_url_name' => 'detail_page',
                'associations' => [
                    self::createAssociation('unit', self::ManyToOne, true, false, 'unit', 'unit_uuid'),
                    self::createAssociation('product_price', self::OneToMany, true, true, 'price', 'product_uuid'),
                    self::createAssociation('product_manufacturer', self::ManyToOne, true, false, 'manufacturer', 'product_manufacturer_uuid', '', '', false),
                    self::createAssociation('tax', self::ManyToOne, true, false, 'tax', 'tax_uuid', '', '', false),
                    self::createAssociation('seo_url', self::ManyToOne, true, false, 'canonicalUrl', ''),
                    self::createAssociation('product_media', self::OneToMany, false, true, 'media', 'product_uuid'),
                    self::createAssociation('price_group', self::ManyToOne, true, false, 'priceGroup', 'price_group_uuid'),
                    self::createAssociation('customer_group', self::ManyToMany, true, true, 'blockedCustomerGroups', '', 'product_avoid_customer_group'),
                    self::createAssociation('category', self::ManyToMany, false, true, 'category', 'product_uuid', 'product_category', '', true, false, '', file_get_contents(__DIR__ . '/special_case/product/category_association_assign.txt')),
                    self::createAssociation('category', self::ManyToMany, false, true, 'categoryTree', 'product_uuid', 'product_category_ro'),
                    self::createAssociation('product_vote', self::OneToMany, false, true, 'vote', 'product_uuid'),
                    self::createAssociation('product_listing_price_ro', self::OneToMany, true, true, 'listingPrice', 'product_uuid'),
                    self::createAssociation('product_vote_average_ro', self::OneToMany, false, true, 'voteAverage', 'product_uuid')
                ]
            ],
            'product_price' => [
                'associatons' => [
                    self::createAssociation('customer_group', self::ManyToOne, true, false, 'customerGroup', 'customer_group_uuid', '', '', false)
                ]
            ],
            'product_vote' => [
                self::createAssociation('shop', self::ManyToOne, true, false, 'shop', 'shop_uuid'),
            ],
//            'product_detail' => [
//                'associations' => [
//
//                ],
//            ],
            'product_manufacturer' => [],
            'product_vote_average_ro' => [],
            'product_listing_price_ro' => [],
            'seo_url' => [
                'collection_functions' => [
                    file_get_contents(__DIR__ . '/special_case/seo_url/collection_functions.txt')
                ],
                'columns' => [
                    'seo_hash' => [
                        'functions' => file_get_contents(__DIR__ . '/special_case/seo_url/seo_hash.txt')
                    ],
                ]
            ],
            'tax' => [],
            'shop' => [
                'columns' => [
                    'base_url' => [
                        'functions' => file_get_contents(__DIR__ . '/special_case/shop/base_url_functions.txt')
                    ],
                    'base_path' => [
                        'functions' => file_get_contents(__DIR__ . '/special_case/shop/base_path_functions.txt')
                    ]
                ],
                'associations' => [
                    self::createAssociation('currency', self::ManyToOne, true, false, 'currency', 'currency_uuid', '', '', false),
                    self::createAssociation('locale', self::ManyToOne, true, false, 'locale', 'locale_uuid', '', '', false),

                    self::createAssociation('locale', self::ManyToOne, false, false, 'fallbackLocale', 'fallback_locale_uuid'),
                    self::createAssociation('category', self::ManyToOne, false, false, 'category', 'category_uuid', '', '', false),

                    self::createAssociation('customer_group', self::ManyToOne, false, false, 'customerGroup', 'customer_group_uuid', '', '', false),
                    self::createAssociation('payment_method', self::ManyToOne, false, false, 'paymentMethod', 'payment_method_uuid', '', '', false),
                    self::createAssociation('shipping_method', self::ManyToOne, false, false, 'shippingMethod', 'shipping_method_uuid', '', '', false),
                    self::createAssociation('area_country', self::ManyToOne, false, false, 'country', 'area_country_uuid', '', '', false),

                    self::createAssociation('shop_template', self::ManyToOne, false, false, 'template', 'shop_template_uuid', '', '', false),
                    self::createAssociation('currency', self::ManyToMany, false, true, 'availableCurrency', 'currency_uuid', 'shop_currency', ''),
                ],
                'collection_functions' => [
                    file_get_contents(__DIR__ . '/special_case/shop/collection_functions.txt')
                ]
            ],
            'payment_method' => [
                'associations' => [
                    self::createAssociation('shop', self::ManyToMany, false, true, 'shop', '', 'payment_method_shop'),
                    self::createAssociation('area_country', self::ManyToMany, false, true, 'country', '', 'payment_method_country')
                ]
            ],
            'shipping_method' => [
                'associations' => [
                    self::createAssociation('category', self::ManyToMany, false, true, 'category', '', 'shipping_method_category'),
                    self::createAssociation('area_country', self::ManyToMany, false, true, 'country', '', 'shipping_method_country'),
                    self::createAssociation('holiday', self::ManyToMany, false, true, 'holiday', '', 'shipping_method_holiday'),
                    self::createAssociation('payment_method', self::ManyToMany, false, true, 'paymentMethod', '', 'shipping_method_payment_method'),
                    self::createAssociation('shipping_method_price', self::OneToMany, false, true, 'price', 'shipping_method_uuid'),
                ]
            ],
            'shipping_method_price' => [],
            'currency' => [
                'associations' => [
                    self::createAssociation('shop', self::ManyToMany, false, true, 'shop', '', 'shop_currency'),
                ],
                'collection_functions' => [
                    file_get_contents(__DIR__ . '/special_case/currency/collection_functions.txt')
                ]
            ],
            'media' => [
                'associations' => [
                    self::createAssociation('album', self::ManyToOne, true, false, 'album', 'album_uuid'),
                ],
                'struct_functions' => [
                    file_get_contents(__DIR__ . '/special_case/media/mediabasicstruct.txt')
                ],
            ],
            'category' => [
                'seo_url_name' => 'listing_page',
                'columns' => [
                    'path' => ['type' => 'array'],
                    'path_names' => ['type' => 'array'],
                    'facet_ids' => ['type' => 'array'],
                    'sorting_ids' => ['type' => 'array'],
                ],
                'associations' => [
                    self::createAssociation('product_stream', self::ManyToOne, false, false, 'productStream', 'product_stream_uuid'),
                    self::createAssociation('media', self::ManyToOne, false, false, 'media', 'media_uuid'),
                    self::createAssociation('seo_url', self::ManyToOne, true, false, 'canonicalUrl', ''),
                    self::createAssociation('product', self::ManyToMany, false, true, 'product', '', 'product_category_ro'),
                    self::createAssociation('customer_group', self::ManyToMany, false, true, 'blockedCustomerGroups', '', 'category_avoid_customer_group'),
                ],
                'struct_functions' => [
                    file_get_contents(__DIR__ . '/special_case/category/struct_children.txt')
                ],
                'collection_functions' => [
                    file_get_contents(__DIR__ . '/special_case/category/collection_build_tree.txt')
                ]
            ],
            'customer' => [
                'associations' => [
                    self::createAssociation('customer_group', self::ManyToOne, true, false, 'customerGroup', 'customer_group_uuid', '', '', false),
                    self::createAssociation('customer_address', self::ManyToOne, true, false, 'defaultShippingAddress', 'default_shipping_address_uuid', '', '', false),
                    self::createAssociation('customer_address', self::ManyToOne, true, false, 'defaultBillingAddress', 'default_billing_address_uuid', '', '', false),
                    self::createAssociation('payment_method', self::ManyToOne, true, false, 'lastPaymentMethod', 'last_payment_method_uuid'),
                    self::createAssociation('payment_method', self::ManyToOne, true, false, 'defaultPaymentMethod', 'default_payment_method_uuid', '', '', false),
                    self::createAssociation('customer_address', self::OneToMany, false, true, 'address', 'customer_uuid'),
                    self::createAssociation('shop', self::ManyToOne, false, false, 'shop', 'shop_uuid', '', '', false),
                ],
                'struct_functions' => [
                    file_get_contents(__DIR__ . '/special_case/customer/active_addresses.txt')
                ]
            ],
            'customer_address' => [
                'associations' => [
                    self::createAssociation('area_country', self::ManyToOne, true, false, 'country', 'area_country_uuid', '', '', false),
                    self::createAssociation('area_country_state', self::ManyToOne, true, false, 'state', 'area_country_state_uuid', '', '', true),
                ]
            ],
            'product_stream' => [
                'associations' => [
                    self::createAssociation('listing_sorting', self::ManyToOne, true, false, 'sorting', 'listing_sorting_uuid', '', '', false)
                ]
            ],
            'album' => [
                'associations' => [
                    self::createAssociation('media', self::OneToMany, false, true, 'media', 'album_uuid')
                ]
            ],
            'area' => [
                'associations' => [
                    self::createAssociation('area_country', self::OneToMany, false, true, 'country', 'area_uuid', '', '', true, true)
                ]
            ],
            'area_country' => [
                'associations' => [
                    self::createAssociation('area_country_state', self::OneToMany, false, true, 'state', 'area_country_uuid')
                ]
            ],
            'area_country_state' => [
            ],
            'customer_group' => [
                'associations' => [
                    self::createAssociation('customer_group_discount', self::OneToMany, false, true, 'discount', 'customer_group_uuid')
                ]
            ],
            'customer_group_discount' => [
            ],
            'holiday' => [],
            'locale' => [],
            'price_group' => [
                'associations' => [
                    self::createAssociation('price_group_discount', self::OneToMany, false, true, 'discount', 'price_group_uuid')
                ]
            ],
            'price_group_discount' => [
            ],
            'shop_template' => [],
            'tax_area_rule' => [],
            'unit' => [],
            'listing_sorting' => []
        ];

        $context = new \ReadGenerator\Context();
//        $context->createFactory = false;
//        $context->createStruct = false;
//        $context->createCollection = false;
//        $context->createExtension = false;
//        $context->createController = true;
//        $context->createWriter = false;
        $context->createBundle = false;
//        $context->createEvent = false;
//        $context->createReader = false;
//        $context->createSearcher = false;
//        $context->createRepository = false;
//        $context->createServiceXml = false;

        foreach ($tables as $table => $assocs) {
            $generator->generate($table, $assocs, $context);
        }
    }


    /**
     * @param string $table defines the associated table (like product => "product_detail")
     * @param string $type defines the association type 1:1, 1:N, ...
     * @param bool $inBasic should be loaded with basic struct
     * @param bool $loadByReader defines if the entity can loaded in same query or lazy by reader
     * @param string $property defines the property name
     * @param string $foreignKeyColumn defines the foreign key column
     * @param string $mappingTable only used for N:N (product_category)
     * @param string $condition useless
     * @param bool $nullable defines if the property can be null (only used for ToOne associations)
     * @param bool $hasDetailReader defines if the related table has an own detail reader
     * @param null $fetchTemplate hack to override "association fetch"
     * @param null $assignTemplate hack to override "association assignment"
     * @return array
     */
    private static function createAssociation(
        string $table,
        string $type,
        bool $inBasic,
        bool $loadByReader,
        string $property,
        string $foreignKeyColumn,
        string $mappingTable = '',
        string $condition = '',
        $nullable = true,
        $hasDetailReader = false,
        $fetchTemplate = null,
        $assignTemplate = null
    ) {
        return [
            'in_basic' => $inBasic,                        //defines if it should be added to basic struct
            'load_by_association_reader' => $loadByReader,      //true to fetch directly in query, false to fetch over associated basic reader
            'type' => $type,
            'table' => $table,
            'mapping' => $mappingTable,
            'condition' => $condition,
            'property' => $property,
            'foreignKeyColumn' => $foreignKeyColumn,
            'nullable' => $nullable,
            'has_detail_reader' => $hasDetailReader,
            'fetchTemplate' => $fetchTemplate,
            'assignTemplate' => $assignTemplate
        ];
    }
}

$command = new Generate();
$command->execute();
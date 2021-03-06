<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class EbayEnterprise_Eb2cInventory_Model_Config extends EbayEnterprise_Eb2cCore_Model_Config_Abstract
{
    protected $_configPaths = array(
        'allocation_expired'                    => 'eb2cinventory/allocation_expired',
        'api_opt_inventory_allocation'          => 'eb2cinventory/api_opt_inventory_allocation',
        'api_opt_inventory_details'             => 'eb2cinventory/api_opt_inventory_details',
        'api_opt_inventory_qty'                 => 'eb2cinventory/api_opt_inventory_qty',
        'api_opt_inventory_rollback_allocation' => 'eb2cinventory/api_opt_inventory_rollback_allocation',
        'api_return_format'                     => 'eb2cinventory/api_return_format',
        'api_service'                           => 'eb2cinventory/api_service',
        'api_xml_ns'                            => 'eb2ccore/api/xml_namespace',
        'feed_event_type'                       => 'eb2ccore/feed/filetransfer_imports/inventory/event_type',
        'feed_file_pattern'                     => 'eb2ccore/feed/filetransfer_imports/inventory/file_pattern',
        'feed_header_version'                   => 'eb2ccore/feed/filetransfer_imports/inventory/header_version',
        'feed_directory_config'                 => 'eb2ccore/feed/filetransfer_imports/inventory',
        'xsd_file_allocation'                   => 'eb2cinventory/xsd/allocation_file',
        'xsd_file_details'                      => 'eb2cinventory/xsd/details_file',
        'xsd_file_quantity'                     => 'eb2cinventory/xsd/quantity_file',
        'xsd_file_rollback'                     => 'eb2cinventory/xsd/rollback_file',
        'estimated_delivery_template'           => 'eb2cinventory/estimated_delivery/template',
    );
}

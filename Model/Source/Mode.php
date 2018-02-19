<?php
/**
 * Cybage Layermultifilter Layered Navigation Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_ecom@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Layermultifilter Layered Navigation Plugin
 * @package    Cybage_Layermultifilter
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */
namespace Cybage\Layermultifilter\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Mode extends AbstractSource 
{
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getAllOptions() {
        return [
            ['value' => '0', 'label' => __('--Select An Option--')],
            ['value' => 'range', 'label' => __('Range')],
            ['value' => 'slider', 'label' => __('Slider')]
        ];
    }
}
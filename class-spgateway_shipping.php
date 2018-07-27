<?php

/**
 * @class       WC_Spgateway_Shipping
 * @extends     WC_Shipping_Method
 * @version
 * @author  Pya2go Libby
 * @author  Pya2go Chael
 * @author  Spgateway Geoff
 * @author  Spgateway_Pay2go Q //20170217 1.0.1
 * @author  Spgateway_Pay2go jack //20170622 1.0.2
 * @author  Spgateway_Pay2go Stally //20180420 1.0.3
 */
class WC_Spgateway_Shipping extends WC_Shipping_Method
{

    public function __construct($id = '', $title = '')
    {
        $this->id = $id;
        $this->method_title = __($title, 'woocommerce');
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        // Define user set variables
        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->fee = $this->get_option('fee');
        $this->coupon_free = $this->get_option('coupon_free');
        $this->free_amount = $this->get_option('free_amount');

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __("啟用 $this->method_title(僅供智付通金流付款使用)", 'woocommerce'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('標題', 'woocommerce'),
                'type' => 'text',
                'description' => __('控制使用者在結帳時所看到的標題.', 'woocommerce'),
                'default' => __($this->method_title, 'woocommerce'),
            ),
            'fee' => array(
                'title' => __('運費', 'woocommerce'),
                'type' => 'price',
                'description' => __('請自行設定運費', 'woocommerce'),
                'default' => __(200, 'woocommerce'),
            ),
            'coupon_free' => array(
                'title' => __('免運費優惠券', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __("$this->method_title 是否可以使用免運費優惠券", 'woocommerce'),
                'default' => __('no', 'woocommerce'),
            ),
            'free_amount' => array(
                'title' => __('滿額免運', 'woocommerce'),
                'type' => 'price',
                'description' => __('金額達到多少免運費，0為關閉此功能', 'woocommerce'),
                'default' => __(0, 'woocommerce'),
                'placeholder' => wc_format_localized_price(0)
            )
        );
    }

    public function is_available($package)
    {
        if ($this->enabled === 'no') {
            return false;
        }
        return true;
    }

    public function calculate_shipping($package = array())
    {
        global $woocommerce;
        //免運優惠券
        if($this->coupon_free == 'yes') {
            foreach ($woocommerce->cart->coupons as $val) {
                if ($val->get_free_shipping() == true) {
                    $this->fee = 0;
                }
            }
        }
        //滿額免運
        if(round($woocommerce->cart->cart_contents_total) >= $this->free_amount && $this->free_amount > 0) {
            $this->fee = 0;
        }

        $this->add_rate(array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $this->fee
        ));
    }
}
?>

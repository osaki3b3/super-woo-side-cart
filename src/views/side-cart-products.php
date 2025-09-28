<?php
// Add a custom section to the cart page
add_action('woocommerce_after_cart_contents', 'display_cart_related_products');

?>
<div id="cart-form-side" class="cart-form-side">
    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="woocommerce-cart-form">
            <?php do_action('woocommerce_before_cart_table'); ?>
            <?php do_action('woocommerce_before_cart_contents'); ?>
            <div class="cart-block">

                <div class="cart-body">
                    <div class="cart-products">
                        <?php
                        //hide extra labels on "no custom" products
                        $no_custom = get_field('no_customize_products', 'options');
                        // Debug: Log the value
                        error_log(var_export($no_custom, true));
                        $no_custom = $no_custom ? $no_custom : [];

                        // e. get box categories ids to identify.
                        $box_categories = array(514, 557);

                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {

                            // echo "Product Name:".$cart_item['data']->name."<br>";
                            // echo "Product ID:".$cart_item['product_id']."<br>";
                            // echo "Product Parent Item Key:".$cart_item['parent_item_key']."<br>";
                            // echo "Cart Item Key:".$cart_item_key."<br>";
                            // echo "Addons::".print_r($cart_item['addons'], true)."<br>";
                            //echo "<br><br>Addon Cart content:".print_r(WC()->cart->cart_contents[$cart_item_key]['addons'], true)."<br><br>";

                            //echo "Cart item Key is:".$cart_item_key;
                            //print_r($cart_item);

                            //echo "Parent ID:".$cart_item['parent_product_id']."<br>";
                            //echo "Parent key:".$cart_item['parent_item_key']."<br>";

                            // e. <======================================================
                            // we need to get the Jewelry Boxes Category products, there are 2 categories.
                            // 514 = Wooden Jewelry Box, Porcelain Jewelry Box
                            // 557 = Complimentary Paper Box
                            // we need to get these products and allow to associate them with a products in particular based on a checkbox, make an ajax call and update it

                            $terms = wp_get_post_terms($cart_item['product_id'], 'product_cat');
                            $is_in_box_cat = false;
                            $box_products = null;

                            foreach ($terms as $key => $category_term) {
                                if (in_array($category_term->term_id, $box_categories)) {
                                    $is_in_box_cat = true;

                                    // <==============================================================
                                    // get all products from BOX categories
                                    $box_args = array(
                                        'limit'    => -1, // Retrieve all products, or specify a number for a limited set
                                        'status'   => 'publish', // Only get published products
                                        /*  'category' => array( 'electronics', 'books' ), */ // Array of category slugs
                                        'product_category_id' => $box_categories
                                    );

                                    $box_products = wc_get_products($box_args);
                                    //print_r($box_products);

                                }
                                //echo "<br>Is in Category ID: " . $category_term->term_id . "<br>";
                            }

                            /* echo "<br>Post Terms Are: <br>";
                                    print_r($terms);
                                    echo "<br><br>"; */
                            //foreach ( $terms as $term ) $categories[] = $term->slug;



                            // if ( ! empty( $products ) ) {
                            //     echo '<h2>Products in Jewelry Boxes:</h2>';
                            //     echo '<ul>';
                            //     foreach ( $products as $product ) {
                            //         echo '<li>' . $product->get_name() . ' (ID: ' . $product->get_id() . ')</li>';
                            //     }
                            //     echo '</ul>';
                            // } else {
                            //     echo '<p>No products found in the specified categories.</p>';
                            // }


                            // // check and print product form the categories above.
                            // if( has_term( array(514), 'product_cat' ) ) {
                            //     // do something if current product in the loop is in product category with ID 4
                            //     $term = get_term( 514 , 'product_cat' );
                            //     echo "<br>This product is in category".print_r($term, true)."<br>";
                            // }elseif( has_term( array(557), 'product_cat' ) ){
                            //     $term = get_term( 557 , 'product_cat' );
                            //     echo "<br>This product is in category".print_r($term, true)."<br>";

                            // }

                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                            $meta = wc_get_cart_item_data($cart_item);
                            $pa_size = __('None', 'ip_master');
                            $pa_metal = __('None', 'ip_master');
                            $pa_finish = __('None', 'ip_master');
                            $pa_engraving = __('None', 'ip_master');
                            $pa_ring_box = __('None', 'ip_master');

                            // <===================== if product is in BOX category then show all the boxes to be able to change it
                            if ($is_in_box_cat) {

                                //$box_name = $cart_item['name'];
                                //echo "Selected Box Product id is: ".$cart_item['product_id']."<br>".  $cart_item['name'];
                                //print_r($cart_item);

                                echo '<div class="wsc-title-box">Ring Box</div>';
                                echo '<div class="b-loader"></div>';
                                echo '<div class="box-products">';

                                foreach ($box_products as $box_key => $box) {

                                    // echo "<br>Box product ".$box_key."<br>";
                                    // print_r($box);
                                    // echo "<br><br>";

                        ?>
                                    <div class="box-product" data-belongs-to-product="<?php ?>">
                                        <div class="box-product-image">
                                            <?php
                                            echo get_the_post_thumbnail($box->id, 'full');
                                            ?>
                                        </div>
                                        <div class="box-product-title">
                                            <?php
                                            //print_r($cart_item);
                                            echo $box->name; #. " / box id:" . $box->id;
                                            ?>
                                        </div>
                                        <div class="box-product-price">
                                            <?php
                                            $price_html = $box->get_price_html();
                                            //echo "<br><br>Box PRICE:".$box->price;

                                            if (str_contains($price_html, '0.00')) {
                                                echo "Free";
                                            } else {
                                                echo $price_html;
                                            }


                                            ?>
                                        </div>
                                        <div class="box-product-checkbox" data-cart-item-key="<?php echo trim($cart_item_key); ?>" data-key-to-remove="<?php echo trim($cart_item_key);  #$cart_item['product_id']; 
                                                                                                                                                        ?>" data-key-to-add="<?php echo trim($box->id); ?>" data-parent-id="<?php echo trim($cart_item['parent_product_id']); ?>" data-parent-key="<?php echo trim($cart_item['parent_item_key']); ?>" data-box-name="<?php echo trim($box->name); ?>" data-box-price="<?php echo trim($box->price); ?>">

                                            <input type="radio" class="checkbox-box <?php if ($cart_item['product_id'] == $box->id) {
                                                                                        echo ' radio-checked';
                                                                                    } ?>" name=" box-product-checkbox-<?php echo $cart_item['product_id']; ?>" data-box-parent-id="<?php echo $cart_item['parent_product_id']; ?>" data-box-parent-item-key="<?php echo $cart_item['parent_item_key']; ?>" value="<?php echo $box->id; ?>" data-box-id="<?php echo $box->id; ?>" <?php if ($cart_item['product_id'] == $box->id) {
                                                                                                                                                                                                                                                                                                                                                                                        echo ' checked="checked"';
                                                                                                                                                                                                                                                                                                                                                                                    } ?> />




                                        </div>
                                    </div>

                                <?php

                                }
                                echo '</div>';
                            } else {


                                echo '<div class="cart-product cart_item">';
                                echo '<div class="cart-product-data">';

                                $show_extra = true;
                                if (is_array($no_custom) && in_array($product_id, $no_custom)) {
                                    $show_extra = false;
                                }

                                foreach ($meta as $item) {
                                    switch ($item['taxonomy']) {
                                        case 'pa_metal-type':
                                            $pa_metal = $item['value'];
                                            break;
                                    }
                                }


                                if (isset($cart_item['addons']) && is_array($cart_item['addons'])) {
                                    //echo "ENTERS ADDONS...<br>";
                                    foreach ($cart_item['addons'] as $item) {

                                        //echo "Addon item is <br>";
                                        //print_r($item);

                                        if (isset($item['name'])) {
                                            switch ($item['name']) {
                                                case 'Ring Size':
                                                    $pa_size = $item['value'];
                                                    break;
                                                case 'Finish':
                                                    $pa_finish = $item['value'];
                                                    break;
                                                case 'Engraving':
                                                    $pa_engraving = $item['value'];
                                                    break;
                                                case 'Hand Engraving':
                                                    if (!empty($item['value'])) { // checks if 'Hand Engraving' has a non-empty value
                                                        $pa_engraving_price = $item['price'];
                                                        $pa_engraving = $item['value'];
                                                    }

                                                    break;
                                                case 'Ring Box':
                                                    $pa_ring_box = $item['value'];
                                                    break;
                                                case 'Metal':
                                                    $pa_metal = $item['value'];
                                                    break;
                                            }
                                        }
                                    }
                                }



                                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                ?>
                                    <div class="cart-product-image">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

                                        if (! $product_permalink) {
                                            echo $thumbnail; // PHPCS: XSS ok.
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                                        }
                                        ?>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="cart-product-info">
                                    <div class="product-name"><span class="strong">Name:</span>
                                        <?php
                                        echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                                        ?>
                                    </div>
                                    <div class="product-price">
                                        <?php
                                        //print_r($_product);
                                        //print_r($_product->name . "<br>");
                                        //print_r($_product->price . "<br>");
                                        //print_r($_product->regular_price . "<br>");
                                        echo 'Price: $';
                                        echo (number_format($_product->get_price(), 2));
                                        //echo WC()->cart->get_product_price( $_product );
                                        //print_r($_product->name);
                                        ?>
                                        <!-- <span class="strong">Pricee: </span><span class="wsc-subtotal-val">$<?php

                                                                                                                    echo $cart_item->price;

                                                                                                                    //echo "Product Get price:" . $_product->get_price();

                                                                                                                    // e. some temp fix for the price being displayed TODO; refactor all this.
                                                                                                                    if ($pa_engraving_price > 1  && !strpos($_product->get_name(), 'Jewelry Box')) {
                                                                                                                        //$final_price = ($_product->price - $pa_engraving_price);
                                                                                                                        $final_price = ($_product->get_price() - $pa_engraving_price);
                                                                                                                        echo ' -- ' . number_format($final_price, 2);
                                                                                                                    } else {
                                                                                                                        echo ' --- ' . number_format($_product->get_price(), 2);
                                                                                                                    }
                                                                                                                    //echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); 

                                                                                                                    ?></span>
                                    </div> -->
                                        <?php if ($show_extra): ?>
                                            <div class="product-size"><span class="strong">Size: </span><?php echo  $pa_size; ?></div>
                                            <?php
                                            if ($_product->is_sold_individually()) {
                                                $product_quantity = sprintf('<span class="quantity-number">1</span><input type="hidden" name="cart[%s][qty]" value="1" class="input-text qty text"/>', $cart_item_key);
                                            } else {
                                                $product_quantity = sprintf('<span class="quantity-number">%s</span><input type="hidden" name="cart[%s][qty]" value="%s" class="input-text qty text"/>', $cart_item['quantity'], $cart_item_key, $cart_item['quantity']);
                                            }
                                            ?>
                                            <div class="product-quantity wsc-qty" data-key="<?php echo $cart_item_key; ?>"><span class="strong">Quantity:&nbsp;</span><span class="quantity"><?php echo $product_quantity; ?></span><span class="minus dec"></span><span class="plus inc"></span></div>
                                            <div class="product-metal"><span class="strong">Metal: </span><?php echo $pa_metal; ?></div>
                                            <!-- <div class="product-finish"><span class="strong">Finish: </span><?php echo $pa_finish; ?></div> -->
                                            <div class="product-engraving"><span class="strong">Hand Engraving: </span><?php echo $pa_engraving; ?> <?php if ($pa_engraving_price > 1) {
                                                                                                                                                        echo '($' . number_format_i18n($pa_engraving_price, 2) . ')';
                                                                                                                                                    } ?></div>

                                            <div class="product-ring_box"><span class="strong">Ring box: </span><span class="ring-box-name"><?php echo $pa_ring_box; ?></span> <?php if ($pa_ring_box_price > 1) {
                                                                                                                                                                                    echo '($' . number_format_i18n($pa_ring_box_price, 2) . ')';
                                                                                                                                                                                } ?></div>
                                            <div class="oyster-protection-logo" data-product-id="<?php echo $product_id; ?>"></div>
                                            <div class="product-actions">
                                                <span class="strong"><a href="<?php echo get_permalink($product_id); ?>" class="product-edit">Edit Product</a></span>
                                                <span class="strong"><?php
                                                                        echo apply_filters(
                                                                            'woocommerce_cart_item_remove_link',
                                                                            sprintf(
                                                                                '<a href="%s" aria-label="%s" data-product_id="%s" data-product_sku="%s">REMOVE PRODUCT</a>',
                                                                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                                                esc_html__('Remove this item', 'woocommerce'),
                                                                                esc_attr($product_id),
                                                                                esc_attr($_product->get_sku())
                                                                            ),
                                                                            $cart_item_key
                                                                        );
                                                                        ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="product-quantity"><span class="strong">Quantity:&nbsp;</span><span class="quantity"><?php echo $cart_item['quantity']; ?></span></div>
                                            <div class="product-actions">
                                                <span class="strong"><?php
                                                                        echo apply_filters(
                                                                            'woocommerce_cart_item_remove_link',
                                                                            sprintf(
                                                                                '<a href="%s" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-product-key="%s" >REMOVE PRODUCT</a>',
                                                                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                                                esc_html__('Remove this item', 'woocommerce'),
                                                                                esc_attr($product_id),
                                                                                esc_attr($_product->get_sku()),
                                                                                esc_attr($cart_item_key)
                                                                            ),
                                                                            $cart_item_key
                                                                        );
                                                                        ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>


                    </div>
            <?php
                            } // END if that checks if in BOX category.
                        } // end PRODUCT for each.
            ?>
            <!-- <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update cart', 'woocommerce'); ?></button> -->
            <?php do_action('woocommerce_cart_actions'); ?>
            <?php //wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); 
            ?>

                </div>
                <div class="cart-total-block cart_totals">
                    <div class="cart-subtotal"><span class="strong">Estimated Total: </span><?php echo WC()->cart->get_cart_subtotal(); ?></div>
                </div>

                <?php woocommerce_button_proceed_to_checkout(); ?>

            </div>
        </div>
        <? php // do_action( 'woocommerce_cart_contents' ); 
        ?>
        <?php do_action('woocommerce_after_cart_contents'); ?>
        <?php //do_action( 'woocommerce_after_cart_table' ); 
        ?>
</div>
</form>
</div>


<!-- <div class="cart-collaterals">
    <?php
    /**
     * Cart collaterals hook.
     *
     * @hooked woocommerce_cross_sell_display
     * @hooked woocommerce_cart_totals - 10
     */
    //do_action( 'woocommerce_cart_collaterals' );
    ?>
</div> -->

<script>
    jQuery(document).ready(function() {
        // Select all <li> elements within a specific <ul> (e.g., with ID "myList")
        // and hide all elements starting from the third one (index 2, as it's 0-indexed)
        jQuery('.cart-collaterals .products li:gt(1)').hide();
    });
</script>


<?php


function display_cart_related_products()
{


    // Check if there are items in the cart
    if (! WC()->cart->is_empty()) {
        // Get product IDs from the cart
        $cart_product_ids = array();
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_product_ids[] = $cart_item['product_id'];
        }

        // echo "Product IDs are: <br>";
        // print_r($cart_product_ids);

        // Query for related products based on cart items (e.g., same categories/tags)
        // This part would involve a custom query using WP_Query or wc_get_products()
        // and logic to determine related items based on your criteria.

        // Example: Display related products from the same category as the last item added
        $last_product_in_cart = $cart_product_ids[0]; #end( $cart_product_ids );
        $product_categories = wp_get_post_terms($last_product_in_cart, 'product_cat', array('fields' => 'ids'));

        if (! empty($product_categories)) {
            $args = array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => 2, // Number of related products to display
                'post__not_in'   => $cart_product_ids, // Exclude products already in cart
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'id',
                        'terms'    => $product_categories,
                        'operator' => 'IN',
                    ),
                ),
            );

            $related_products_query = new WP_Query($args);

            if ($related_products_query->have_posts()) {
                echo '<div class="side-cart-also-like">';
                echo '<h2>You may be interested in...</h2>';
                echo '<ul class="products">';
                while ($related_products_query->have_posts()) : $related_products_query->the_post();
                    //wc_get_template_part( 'content', 'product' ); // Display product loop item
                    //print_r( $related_products_query );

                    $product = wc_get_product($related_products_query->post->ID);

?>
                    <li class="product type-product">

                        <a href="<?php echo get_permalink($related_products_query->post->ID) ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">

                            <?php $url = wp_get_attachment_url(get_post_thumbnail_id($related_products_query->post->ID), 'full'); ?>
                            <img src="<?php echo $url ?>" />


                            <?php
                            /*  if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( 'full' ); // You can specify image size: 'thumbnail', 'medium', 'large', 'full' or a custom size
                                    } */
                            ?>

                            <div class="title_price_wrapper">
                                <h2 class="woocommerce-loop-product__title"><?php echo get_the_title(); ?><br>Starts At:<?php echo number_format($product->get_price(), 2); ?></h2>

                            </div>
                        </a>


                    </li>

<?php
                endwhile;
                echo '</ul>';
                echo '</div>';
                wp_reset_postdata();
            }
        }
    }
}

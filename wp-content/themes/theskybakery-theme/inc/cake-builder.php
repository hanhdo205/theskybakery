<?php
/**
 * Custom Cake Builder
 *
 * @package TheSkyBakery
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cake Builder Shortcode
 */
function tsb_cake_builder_shortcode() {
    ob_start();
    ?>
    <div id="cake-builder" class="cake-builder-container">
        <div class="row">
            <!-- Cake Preview -->
            <div class="col-lg-5">
                <div class="cake-preview">
                    <div class="preview-image">
                        <img src="<?php echo TSB_THEME_URI; ?>/assets/images/cake-placeholder.png" alt="Your Custom Cake" id="cake-preview-image">
                        <div class="preview-message" id="cake-preview-message">Your message here</div>
                    </div>
                    <div class="preview-price">
                        <span class="label">Total:</span>
                        <span class="price" id="cake-total-price">$0.00</span>
                    </div>
                </div>
            </div>

            <!-- Cake Options -->
            <div class="col-lg-7">
                <form id="cake-builder-form" class="cake-options">
                    
                    <!-- Step 1: Size -->
                    <div class="builder-step">
                        <h3 class="step-title"><span class="step-number">1</span> Choose Size</h3>
                        <div class="size-options">
                            <?php
                            $sizes = array(
                                'small' => array('label' => 'Small (6")', 'price' => 38, 'serves' => '6-8'),
                                'medium' => array('label' => 'Medium (8")', 'price' => 55, 'serves' => '10-12'),
                                'large' => array('label' => 'Large (10")', 'price' => 75, 'serves' => '14-18'),
                                'xl' => array('label' => 'Extra Large (12")', 'price' => 95, 'serves' => '20-25'),
                            );

                            foreach ($sizes as $key => $size) :
                            ?>
                                <label class="cake-size-option">
                                    <input type="radio" name="cake_size" value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($size['price']); ?>" <?php echo $key === 'small' ? 'checked' : ''; ?>>
                                    <div class="option-content">
                                        <span class="size-name"><?php echo esc_html($size['label']); ?></span>
                                        <span class="size-serves">Serves <?php echo esc_html($size['serves']); ?></span>
                                        <span class="size-price">$<?php echo esc_html($size['price']); ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Flavor -->
                    <div class="builder-step">
                        <h3 class="step-title"><span class="step-number">2</span> Choose Flavor</h3>
                        <div class="flavor-options">
                            <?php
                            $flavors = array(
                                'vanilla' => array('label' => 'Vanilla', 'price' => 0),
                                'chocolate' => array('label' => 'Chocolate', 'price' => 0),
                                'red-velvet' => array('label' => 'Red Velvet', 'price' => 5),
                                'carrot' => array('label' => 'Carrot', 'price' => 5),
                                'lemon' => array('label' => 'Lemon', 'price' => 3),
                                'marble' => array('label' => 'Marble', 'price' => 3),
                                'mud' => array('label' => 'Mud Cake', 'price' => 5),
                            );

                            foreach ($flavors as $key => $flavor) :
                            ?>
                                <label class="cake-flavor-option">
                                    <input type="radio" name="cake_flavor" value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($flavor['price']); ?>" <?php echo $key === 'vanilla' ? 'checked' : ''; ?>>
                                    <div class="option-content">
                                        <span class="flavor-name"><?php echo esc_html($flavor['label']); ?></span>
                                        <?php if ($flavor['price'] > 0) : ?>
                                            <span class="flavor-price">+$<?php echo esc_html($flavor['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 3: Frosting -->
                    <div class="builder-step">
                        <h3 class="step-title"><span class="step-number">3</span> Choose Frosting</h3>
                        <div class="frosting-options">
                            <?php
                            $frostings = array(
                                'buttercream' => array('label' => 'Buttercream', 'price' => 0),
                                'cream-cheese' => array('label' => 'Cream Cheese', 'price' => 5),
                                'chocolate-ganache' => array('label' => 'Chocolate Ganache', 'price' => 5),
                                'whipped-cream' => array('label' => 'Whipped Cream', 'price' => 0),
                                'fondant' => array('label' => 'Fondant', 'price' => 15),
                            );

                            foreach ($frostings as $key => $frosting) :
                            ?>
                                <label class="cake-flavor-option">
                                    <input type="radio" name="cake_frosting" value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($frosting['price']); ?>" <?php echo $key === 'buttercream' ? 'checked' : ''; ?>>
                                    <div class="option-content">
                                        <span class="flavor-name"><?php echo esc_html($frosting['label']); ?></span>
                                        <?php if ($frosting['price'] > 0) : ?>
                                            <span class="flavor-price">+$<?php echo esc_html($frosting['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 4: Toppings -->
                    <div class="builder-step">
                        <h3 class="step-title"><span class="step-number">4</span> Add Toppings <span class="optional">(Optional)</span></h3>
                        <div class="topping-options">
                            <?php
                            $toppings = array(
                                'fresh-fruits' => array('label' => 'Fresh Fruits', 'price' => 8),
                                'chocolate-drip' => array('label' => 'Chocolate Drip', 'price' => 5),
                                'sprinkles' => array('label' => 'Sprinkles', 'price' => 3),
                                'macarons' => array('label' => 'Macarons', 'price' => 10),
                                'gold-leaf' => array('label' => 'Gold Leaf', 'price' => 12),
                                'flowers' => array('label' => 'Edible Flowers', 'price' => 8),
                                'edible-image' => array('label' => 'Edible Image', 'price' => 15),
                            );

                            foreach ($toppings as $key => $topping) :
                            ?>
                                <label class="cake-topping-option">
                                    <input type="checkbox" name="cake_toppings[]" value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($topping['price']); ?>">
                                    <div class="option-content">
                                        <span class="topping-name"><?php echo esc_html($topping['label']); ?></span>
                                        <span class="topping-price">+$<?php echo esc_html($topping['price']); ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 5: Message -->
                    <div class="builder-step">
                        <h3 class="step-title"><span class="step-number">5</span> Add Message <span class="optional">(Optional)</span></h3>
                        <div class="message-input">
                            <input type="text" id="cake-message" name="cake_message" maxlength="50" placeholder="e.g., Happy Birthday John!">
                            <p class="hint">Maximum 50 characters</p>
                        </div>
                    </div>

                    <!-- Step 6: Pickup Details -->
                    <div class="builder-step">
                        <h3 class="step-title"><span class="step-number">6</span> Pickup Details</h3>
                        <div class="pickup-details">
                            <div class="form-row">
                                <label for="pickup-store">Store Location</label>
                                <select id="pickup-store" name="pickup_store" required>
                                    <option value="">Select store</option>
                                    <?php
                                    $stores = tsb_get_stores();
                                    if ($stores) :
                                        foreach ($stores as $store) :
                                    ?>
                                        <option value="<?php echo esc_attr($store->ID); ?>"><?php echo esc_html($store->post_title); ?></option>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>

                            <div class="form-row form-row-half">
                                <label for="pickup-date">Pickup Date</label>
                                <input type="date" id="pickup-date" name="pickup_date" required>
                            </div>

                            <div class="form-row form-row-half">
                                <label for="pickup-time">Pickup Time</label>
                                <select id="pickup-time" name="pickup_time" required>
                                    <option value="">Select date first</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="builder-submit">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <style>
    .cake-builder-container {
        padding: 40px 0;
    }

    .cake-preview {
        background: #f8f8f8;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        position: sticky;
        top: 120px;
    }

    .preview-image {
        position: relative;
        margin-bottom: 20px;
    }

    .preview-image img {
        max-width: 100%;
        border-radius: 10px;
    }

    .preview-message {
        position: absolute;
        bottom: 30%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255,255,255,0.9);
        padding: 10px 20px;
        border-radius: 5px;
        font-family: 'Dancing Script', cursive;
        font-size: 20px;
        color: #333;
    }

    .preview-price {
        font-size: 24px;
        font-weight: 600;
    }

    .preview-price .label {
        color: #666;
    }

    .preview-price .price {
        color: #c9a86c;
    }

    .builder-step {
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #eee;
    }

    .step-title {
        font-size: 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .step-number {
        background: #c9a86c;
        color: #fff;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .optional {
        font-size: 14px;
        color: #999;
        font-weight: normal;
    }

    .size-options,
    .flavor-options,
    .frosting-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .topping-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .cake-size-option,
    .cake-flavor-option,
    .cake-topping-option {
        display: block;
        cursor: pointer;
    }

    .cake-size-option input,
    .cake-flavor-option input,
    .cake-topping-option input {
        display: none;
    }

    .option-content {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .cake-size-option input:checked + .option-content,
    .cake-flavor-option input:checked + .option-content,
    .cake-topping-option input:checked + .option-content {
        border-color: #c9a86c;
        background: rgba(201, 168, 108, 0.1);
    }

    .option-content:hover {
        border-color: #c9a86c;
    }

    .size-name,
    .flavor-name,
    .topping-name {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .size-serves {
        display: block;
        font-size: 13px;
        color: #666;
    }

    .size-price,
    .flavor-price,
    .topping-price {
        display: block;
        color: #c9a86c;
        font-weight: 600;
        margin-top: 5px;
    }

    .message-input input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
    }

    .message-input input:focus {
        border-color: #c9a86c;
        outline: none;
    }

    .message-input .hint {
        font-size: 13px;
        color: #999;
        margin-top: 5px;
    }

    .pickup-details .form-row {
        margin-bottom: 15px;
    }

    .pickup-details label {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .pickup-details select,
    .pickup-details input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
    }

    .form-row-half {
        display: inline-block;
        width: calc(50% - 10px);
    }

    .form-row-half:first-of-type {
        margin-right: 15px;
    }

    .builder-submit {
        text-align: center;
        margin-top: 30px;
    }

    .builder-submit .btn {
        min-width: 250px;
    }

    @media (max-width: 991px) {
        .cake-preview {
            position: static;
            margin-bottom: 30px;
        }

        .size-options,
        .flavor-options,
        .frosting-options {
            grid-template-columns: 1fr;
        }

        .topping-options {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .topping-options {
            grid-template-columns: 1fr;
        }

        .form-row-half {
            display: block;
            width: 100%;
        }

        .form-row-half:first-of-type {
            margin-right: 0;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('cake_builder', 'tsb_cake_builder_shortcode');

/**
 * Create Cake Builder Page Template
 */
function tsb_cake_builder_template($template) {
    if (is_page('cake-builder')) {
        $custom_template = TSB_THEME_DIR . '/templates/template-cake-builder.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'tsb_cake_builder_template');

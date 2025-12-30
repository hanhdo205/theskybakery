/**
 * The Sky Bakery Theme - Main JavaScript
 * 
 * @package TheSkyBakery
 */

(function($) {
    'use strict';

    // Document Ready
    $(document).ready(function() {
        TSB.init();
    });

    // Window Load
    $(window).on('load', function() {
        TSB.onLoad();
    });

    // Main Theme Object
    var TSB = {
        
        /**
         * Initialize all functions
         */
        init: function() {
            this.heroSlider();
            this.productsCarousel();
            this.backToTop();
            this.stickyHeader();
            this.ajaxAddToCart();
            this.mobileMenu();
            this.newsletterForm();
            this.productHover();
            this.cakeBuilder();
            this.pickupScheduling();
            this.updateCartCount();
        },

        /**
         * On window load
         */
        onLoad: function() {
            this.animateOnScroll();
        },

        /**
         * Hero Slider
         */
        heroSlider: function() {
            var $slider = $('.hero-slider .slider-wrapper');

            if ($slider.length === 0) {
                return;
            }

            // Đợi slick load
            if (typeof $.fn.slick === 'undefined') {
                console.log('Slick not loaded, retrying...');
                setTimeout(function() {
                    TSB.heroSlider();
                }, 100);
                return;
            }

            // Chỉ khởi tạo nếu có nhiều hơn 1 slide
            var slideCount = $slider.find('.slide-item').length;

            if (slideCount > 1) {
                $slider.slick({
                    dots: true,
                    arrows: true,
                    infinite: true,
                    speed: 500,
                    fade: true,
                    cssEase: 'linear',
                    autoplay: true,
                    autoplaySpeed: 4000,
                    pauseOnHover: true,
                    adaptiveHeight: false,
                    prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>',
                    nextArrow: '<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>',
                    responsive: [
                        {
                            breakpoint: 768,
                            settings: {
                                arrows: false
                            }
                        }
                    ]
                });
            } else {
                // Nếu chỉ có 1 slide, không cần slider
                $slider.addClass('single-slide');
            }
        },

        /**
         * Products Carousel (Owl Carousel)
         */
        productsCarousel: function() {
            var $carousel = $('.products-carousel');

            if ($carousel.length && $.fn.owlCarousel) {
                $carousel.owlCarousel({
                    loop: true,
                    margin: 20,
                    nav: true,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: true,
                    navText: [
                        '<i class="fas fa-chevron-left"></i>',
                        '<i class="fas fa-chevron-right"></i>'
                    ],
                    responsive: {
                        0: {
                            items: 1,
                            margin: 10
                        },
                        576: {
                            items: 2,
                            margin: 15
                        },
                        992: {
                            items: 3,
                            margin: 20
                        },
                        1200: {
                            items: 4,
                            margin: 25
                        }
                    }
                });
            }
        },

        /**
         * Back to Top Button
         */
        backToTop: function() {
            var $button = $('#topcontrol');

            // Initially hide Top button only
            $button.hide();

            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $button.fadeIn();
                } else {
                    $button.fadeOut();
                }
            });

            $button.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 600);
            });
        },

        /**
         * Sticky Header
         */
        stickyHeader: function() {
            var $header = $('.site-header');
            var headerOffset = $header.offset().top;

            $(window).scroll(function() {
                if ($(this).scrollTop() > headerOffset + 100) {
                    $header.addClass('is-sticky');
                } else {
                    $header.removeClass('is-sticky');
                }
            });
        },

        /**
         * AJAX Add to Cart
         */
        ajaxAddToCart: function() {
            $(document).on('click', '.btn-add-cart, .add-to-cart-btn', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var productId = $button.data('product_id');
                
                if (!productId) {
                    return;
                }

                $button.addClass('loading');
                $button.prop('disabled', true);

                $.ajax({
                    url: tsb_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tsb_add_to_cart',
                        product_id: productId,
                        nonce: tsb_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update cart count
                            $('.cart-count').text(response.data.cart_count);
                            
                            // Show success message
                            TSB.showNotification('Product added to cart!', 'success');
                            
                            // Add visual feedback
                            $button.addClass('added');
                            setTimeout(function() {
                                $button.removeClass('added');
                            }, 2000);
                        } else {
                            TSB.showNotification(response.data.message || 'Error adding to cart', 'error');
                        }
                    },
                    error: function() {
                        TSB.showNotification('Error adding to cart', 'error');
                    },
                    complete: function() {
                        $button.removeClass('loading');
                        $button.prop('disabled', false);
                    }
                });
            });
        },

        /**
         * Mobile Menu
         */
        mobileMenu: function() {
            // Close menu when clicking outside
            $(document).on('click', function(e) {
                var $navbar = $('.navbar-collapse');
                if ($navbar.hasClass('show') && !$(e.target).closest('.navbar').length) {
                    $navbar.collapse('hide');
                }
            });

            // Handle dropdown on mobile
            $('.navbar-nav .dropdown-toggle').on('click', function(e) {
                if ($(window).width() < 992) {
                    e.preventDefault();
                    $(this).next('.dropdown-menu').slideToggle();
                }
            });
        },

        /**
         * Newsletter Form
         */
        newsletterForm: function() {
            $('#newsletter-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var email = $form.find('input[name="email"]').val();
                
                if (!email || !TSB.isValidEmail(email)) {
                    TSB.showNotification('Please enter a valid email address', 'error');
                    return;
                }

                var $button = $form.find('button[type="submit"]');
                $button.addClass('loading').prop('disabled', true);

                $.ajax({
                    url: tsb_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tsb_newsletter_subscribe',
                        email: email,
                        nonce: tsb_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            TSB.showNotification('Thank you for subscribing!', 'success');
                            $form[0].reset();
                        } else {
                            TSB.showNotification(response.data.message || 'Subscription failed', 'error');
                        }
                    },
                    error: function() {
                        TSB.showNotification('Subscription failed. Please try again.', 'error');
                    },
                    complete: function() {
                        $button.removeClass('loading').prop('disabled', false);
                    }
                });
            });
        },

        /**
         * Product Hover Effects
         */
        productHover: function() {
            $('.product-card').hover(
                function() {
                    $(this).find('.product-overlay').stop().fadeIn(200);
                },
                function() {
                    $(this).find('.product-overlay').stop().fadeOut(200);
                }
            );
        },

        /**
         * Cake Builder
         */
        cakeBuilder: function() {
            var $builder = $('#cake-builder');
            
            if (!$builder.length) {
                return;
            }

            // Size selection
            $builder.on('change', '.cake-size-option input', function() {
                var size = $(this).val();
                var price = $(this).data('price');
                TSB.updateCakePrice();
            });

            // Flavor selection
            $builder.on('change', '.cake-flavor-option input', function() {
                TSB.updateCakePrice();
            });

            // Topping selection
            $builder.on('change', '.cake-topping-option input', function() {
                TSB.updateCakePrice();
            });

            // Message input
            $builder.on('input', '#cake-message', function() {
                var message = $(this).val();
                $('#cake-preview-message').text(message || 'Your message here');
            });

            // Add to cart
            $builder.on('submit', function(e) {
                e.preventDefault();
                TSB.addCustomCakeToCart();
            });
        },

        /**
         * Update Cake Price
         */
        updateCakePrice: function() {
            var $builder = $('#cake-builder');
            var basePrice = 0;

            // Get size price
            var $selectedSize = $builder.find('.cake-size-option input:checked');
            if ($selectedSize.length) {
                basePrice = parseFloat($selectedSize.data('price')) || 0;
            }

            // Add flavor price
            var $selectedFlavor = $builder.find('.cake-flavor-option input:checked');
            if ($selectedFlavor.length) {
                basePrice += parseFloat($selectedFlavor.data('price')) || 0;
            }

            // Add toppings price
            $builder.find('.cake-topping-option input:checked').each(function() {
                basePrice += parseFloat($(this).data('price')) || 0;
            });

            // Update display
            $('#cake-total-price').text('$' + basePrice.toFixed(2));
        },

        /**
         * Add Custom Cake to Cart
         */
        addCustomCakeToCart: function() {
            var $builder = $('#cake-builder');
            var $button = $builder.find('button[type="submit"]');
            
            var cakeData = {
                size: $builder.find('.cake-size-option input:checked').val(),
                flavor: $builder.find('.cake-flavor-option input:checked').val(),
                toppings: [],
                message: $builder.find('#cake-message').val(),
                pickup_date: $builder.find('#pickup-date').val(),
                pickup_time: $builder.find('#pickup-time').val(),
                pickup_store: $builder.find('#pickup-store').val()
            };

            $builder.find('.cake-topping-option input:checked').each(function() {
                cakeData.toppings.push($(this).val());
            });

            $button.addClass('loading').prop('disabled', true);

            $.ajax({
                url: tsb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tsb_add_custom_cake',
                    cake_data: cakeData,
                    nonce: tsb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        TSB.showNotification('Custom cake added to cart!', 'success');
                        $('.cart-count').text(response.data.cart_count);
                        
                        // Redirect to cart
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    } else {
                        TSB.showNotification(response.data.message || 'Error adding cake to cart', 'error');
                    }
                },
                error: function() {
                    TSB.showNotification('Error adding cake to cart', 'error');
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Pickup Scheduling
         */
        pickupScheduling: function() {
            var $pickupDate = $('#pickup-date');
            var $pickupTime = $('#pickup-time');
            var $pickupStore = $('#pickup-store');

            if (!$pickupDate.length) {
                return;
            }

            // Set minimum date to tomorrow
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            var minDate = tomorrow.toISOString().split('T')[0];
            $pickupDate.attr('min', minDate);

            // Update available times when date changes
            $pickupDate.on('change', function() {
                var date = $(this).val();
                var store = $pickupStore.val();
                
                if (date && store) {
                    TSB.getAvailablePickupTimes(date, store);
                }
            });

            // Update available times when store changes
            $pickupStore.on('change', function() {
                var date = $pickupDate.val();
                var store = $(this).val();
                
                if (date && store) {
                    TSB.getAvailablePickupTimes(date, store);
                }
            });
        },

        /**
         * Get Available Pickup Times
         */
        getAvailablePickupTimes: function(date, store) {
            var $pickupTime = $('#pickup-time');
            
            $pickupTime.prop('disabled', true);

            $.ajax({
                url: tsb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tsb_get_pickup_times',
                    date: date,
                    store: store,
                    nonce: tsb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $pickupTime.html('<option value="">Select time</option>');
                        
                        $.each(response.data.times, function(i, time) {
                            $pickupTime.append('<option value="' + time.value + '">' + time.label + '</option>');
                        });
                    }
                },
                complete: function() {
                    $pickupTime.prop('disabled', false);
                }
            });
        },

        /**
         * Update Cart Count
         */
        updateCartCount: function() {
            // Listen for WooCommerce cart updates
            $(document.body).on('added_to_cart removed_from_cart', function() {
                $.ajax({
                    url: tsb_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tsb_get_cart_count',
                        nonce: tsb_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.cart-count').text(response.data.count);
                        }
                    }
                });
            });
        },

        /**
         * Animate on Scroll
         */
        animateOnScroll: function() {
            var $elements = $('.animate__animated');
            
            function checkElements() {
                var windowHeight = $(window).height();
                var scrollTop = $(window).scrollTop();

                $elements.each(function() {
                    var $el = $(this);
                    var elTop = $el.offset().top;

                    if (scrollTop + windowHeight > elTop + 100) {
                        $el.addClass('animate__fadeInUp');
                    }
                });
            }

            $(window).on('scroll', checkElements);
            checkElements();
        },

        /**
         * Show Notification
         */
        showNotification: function(message, type) {
            var $notification = $('<div class="tsb-notification ' + type + '">' + message + '</div>');
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);

            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        },

        /**
         * Validate Email
         */
        isValidEmail: function(email) {
            var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return pattern.test(email);
        }
    };

    // Make TSB available globally
    window.TSB = TSB;

})(jQuery);

/* Notification Styles (inline for JS) */
var notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .tsb-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 4px;
        color: #fff;
        font-size: 14px;
        font-weight: 500;
        z-index: 9999;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .tsb-notification.show {
        transform: translateX(0);
    }
    .tsb-notification.success {
        background: #28a745;
    }
    .tsb-notification.error {
        background: #dc3545;
    }
    .tsb-notification.info {
        background: #17a2b8;
    }
    .btn-add-cart.loading,
    .add-to-cart-btn.loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }
    .btn-add-cart.loading::after,
    .add-to-cart-btn.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        border: 2px solid #fff;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        right: 10px;
        top: 50%;
        margin-top: -8px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .btn-add-cart.added,
    .add-to-cart-btn.added {
        background: #28a745 !important;
    }
`;
document.head.appendChild(notificationStyles);

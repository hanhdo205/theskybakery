/**
 * Star Rating Interactions
 * Font Awesome Stars
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // Handle star rating input clicks
        $('.stars-input .star-link').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var rating = $this.data('rating');

            // Update hidden select
            $('#rating').val(rating);

            // Update visual stars
            $('.stars-input .star-link').each(function(index) {
                var $star = $(this);
                var $icon = $star.find('i');

                if (index < rating) {
                    // Fill star
                    $icon.removeClass('far').addClass('fas');
                } else {
                    // Empty star
                    $icon.removeClass('fas').addClass('far');
                }
            });

            // Add selected class
            $('.stars-input .star-link').removeClass('selected');
            $this.addClass('selected');
            $this.prevAll('.star-link').addClass('selected');
        });

        // Hover effect
        $('.stars-input .star-link').on('mouseenter', function() {
            var rating = $(this).data('rating');

            $('.stars-input .star-link').each(function(index) {
                var $icon = $(this).find('i');

                if (index < rating) {
                    $icon.addClass('hover-fill');
                } else {
                    $icon.removeClass('hover-fill');
                }
            });
        });

        $('.stars-input').on('mouseleave', function() {
            $('.stars-input .star-link i').removeClass('hover-fill');
        });

    });

})(jQuery);

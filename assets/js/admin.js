/**
 * Smart Product Export - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        const $form = $('#spe-export-form');
        const $filterType = $('#filter_type');
        const $filterValue = $('#filter_value');
        const $filterValueRow = $('#filter_value_row');
        const $filterValueDesc = $('#filter_value_desc');
        const $exportBtn = $('#spe-export-btn');
        const $resultsCard = $('#spe-results-card');
        const $resultsTextarea = $('#spe-results');
        const $message = $('#spe-message');
        const $count = $('#spe-count');
        const $copyBtn = $('#spe-copy-btn');

        // Update form based on filter type
        $filterType.on('change', function() {
            const filterType = $(this).val();
            updateFilterValueField(filterType);
        });

        // Initialize on page load
        updateFilterValueField($filterType.val());

        function updateFilterValueField(filterType) {
            let placeholder = '';
            let description = '';

            switch(filterType) {
                case 'all':
                    $filterValueRow.hide();
                    break;
                case 'sku':
                    placeholder = 'e.g., SHIRT';
                    description = 'Enter partial SKU to search for (e.g., "SHIRT" matches "SHIRT-001", "TSHIRT-RED")';
                    $filterValueRow.show();
                    break;
                case 'id':
                    placeholder = 'e.g., 123, 456, 789';
                    description = 'Enter one or more product IDs separated by commas';
                    $filterValueRow.show();
                    break;
                case 'category':
                    placeholder = 'e.g., clothing';
                    description = 'Enter the category slug';
                    $filterValueRow.show();
                    break;
                case 'tag':
                    placeholder = 'e.g., sale';
                    description = 'Enter the product tag slug';
                    $filterValueRow.show();
                    break;
                case 'attribute':
                    placeholder = 'e.g., color:blue';
                    description = 'Enter in format "attribute_name:value" (e.g., "color:blue", "size:large")';
                    $filterValueRow.show();
                    break;
                default:
                    $filterValueRow.show();
            }

            $filterValue.attr('placeholder', placeholder);
            $filterValueDesc.text(description);
        }

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();

            const filterType = $filterType.val();
            const filterValue = $filterValue.val().trim();
            const includeVariations = $('#include_variations').is(':checked') ? 'yes' : 'no';

            // Validation
            if (filterType !== 'all' && !filterValue) {
                showMessage('Please enter a filter value or select "All Products".', 'error');
                return;
            }

            // Show loading state
            $exportBtn.addClass('loading').prop('disabled', true);
            $resultsCard.hide();

            // Make AJAX request
            $.ajax({
                url: speAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'spe_export_skus',
                    nonce: speAjax.nonce,
                    filter_type: filterType,
                    filter_value: filterValue,
                    include_variations: includeVariations
                },
                success: function(response) {
                    if (response.success) {
                        displayResults(response.data.skus, response.data.count, response.data.message);
                    } else {
                        showMessage(response.data.message || 'An error occurred.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showMessage('Request failed: ' + error, 'error');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    $exportBtn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        // Display results
        function displayResults(skus, count, message) {
            $resultsTextarea.val(skus);
            $count.text(count);
            $message.text(message).removeClass('error');
            $resultsCard.fadeIn(300).addClass('show');

            // Scroll to results
            $('html, body').animate({
                scrollTop: $resultsCard.offset().top - 100
            }, 500);
        }

        // Show message
        function showMessage(message, type = 'success') {
            $message.text(message).toggleClass('error', type === 'error');
            $resultsCard.fadeIn(300).addClass('show');
            $resultsTextarea.val('');
            $count.text('0');
        }

        // Copy to clipboard
        $copyBtn.on('click', function() {
            const skus = $resultsTextarea.val();

            if (!skus) {
                return;
            }

            // Copy to clipboard
            $resultsTextarea.select();
            document.execCommand('copy');

            // Use modern clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(skus).then(function() {
                    showCopySuccess();
                }).catch(function(err) {
                    console.error('Clipboard copy failed:', err);
                    showCopySuccess(); // Still show success as fallback worked
                });
            } else {
                showCopySuccess();
            }

            // Deselect text
            window.getSelection().removeAllRanges();
        });

        function showCopySuccess() {
            const originalText = $copyBtn.html();
            $copyBtn.addClass('copied')
                    .html('<span class="dashicons dashicons-yes"></span> Copied!');

            setTimeout(function() {
                $copyBtn.removeClass('copied').html(originalText);
            }, 2000);
        }

        // Auto-resize textarea based on content
        $resultsTextarea.on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

    });

})(jQuery);

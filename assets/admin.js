/**
 * Admin JavaScript for RightLine Ads
 *
 * @package RightLine_Ads
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		let mediaUploader;

		$(document).on('click', '.rightline-ads-upload-button', function (e) {
			e.preventDefault();

			const button = $(this);
			const fieldContainer = button.closest('.rightline-ads-image-field');
			const imageInput = fieldContainer.find('.image-id-input');
			const imagePreview = fieldContainer.find('.image-preview');

			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
				},
				multiple: false
			});

			mediaUploader.on('select', function () {
				const attachment = mediaUploader.state().get('selection').first().toJSON();

				imageInput.val(attachment.id);

				const imgUrl = attachment.sizes && attachment.sizes.medium
					? attachment.sizes.medium.url
					: attachment.url;

				const img = $('<img>').attr('src', imgUrl).attr('alt', 'Preview');
				imagePreview.empty().append(img);

				button.text('Change Image');

				if (fieldContainer.find('.rightline-ads-remove-button').length === 0) {
					button.after('<button type="button" class="button rightline-ads-remove-button">Remove Image</button>');
				}
			});

			mediaUploader.open();
		});

		$(document).on('click', '.rightline-ads-remove-button', function (e) {
			e.preventDefault();

			const button = $(this);
			const fieldContainer = button.closest('.rightline-ads-image-field');
			const imageInput = fieldContainer.find('.image-id-input');
			const imagePreview = fieldContainer.find('.image-preview');
			const uploadButton = fieldContainer.find('.rightline-ads-upload-button');

			imageInput.val('');
			imagePreview.empty();
			uploadButton.text('Upload Image');
			button.remove();
		});

		function updateDimensionSummary() {
			const adType = $('#rightline_ad_type').val();
			const summaryEl = $('#rightline-ads-dimension-summary');

			if (!summaryEl.length) {
				return;
			}

			if (!adType || typeof rightlineAdsDimensions === 'undefined' || !rightlineAdsDimensions[adType]) {
				summaryEl.text('');
				return;
			}

			const dims = rightlineAdsDimensions[adType]['1440'];
			const labels = typeof rightlineAdsTypeLabels !== 'undefined' ? rightlineAdsTypeLabels : {};
			const label = labels[adType] || adType;

			if (dims) {
				summaryEl.text(label + ': Recommended Size: ' + dims.width + ' × ' + dims.height + ' px');
			} else {
				summaryEl.text('');
			}
		}

		function updateDimensionLabels() {
			const adType = $('#rightline_ad_type').val();

			if (!adType || typeof rightlineAdsDimensions === 'undefined' || !rightlineAdsDimensions[adType]) {
				$('.dimension-label').text('');
				return;
			}

			const dims = rightlineAdsDimensions[adType];

			$('.rightline-ads-image-field').each(function () {
				const sizeKey = $(this).data('size');
				const dimensionLabel = $(this).find('.dimension-label');

				const breakpointMap = {
					desktop: '1440',
					tablet: '992',
					mobile: '768'
				};

				const breakpointKey = breakpointMap[sizeKey];

				if (breakpointKey && dims[breakpointKey]) {
					const dim = dims[breakpointKey];
					dimensionLabel.text('(Recommended Size: ' + dim.width + ' × ' + dim.height + ' px)');
				}
			});
		}

		updateDimensionSummary();
		updateDimensionLabels();

		$('#rightline_ad_type').on('change', function () {
			const adType = $(this).val();

			updateDimensionSummary();
			updateDimensionLabels();
		});
	});
})(jQuery);

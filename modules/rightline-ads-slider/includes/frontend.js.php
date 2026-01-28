/**
 * Frontend JavaScript for Ads Slider Module
 *
 * @package RightLine_Ads
 * @var string $id Node ID
 */

(function() {
	'use strict';

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		const slider = document.querySelector('.fl-node-<?php echo esc_js( $id ); ?> .rightline-ads-slider');

		if (!slider) {
			return;
		}

		const slides = slider.querySelectorAll('.rightline-ads-slide');

		if (slides.length <= 1) {
			return;
		}

		const transitionSpeed = parseInt(slider.dataset.transitionSpeed, 10) || 500;
		const slideDelay = parseInt(slider.dataset.slideDelay, 10) || 3000;

		let currentSlide = 0;
		let autoplayInterval = null;
		let isPaused = false;

		function showSlide(index) {
			if (index < 0) {
				index = slides.length - 1;
			} else if (index >= slides.length) {
				index = 0;
			}

			currentSlide = index;

			slides.forEach((slide, i) => {
				if (i === currentSlide) {
					slide.classList.add('active');
				} else {
					slide.classList.remove('active');
				}
			});
		}

		function nextSlide() {
			showSlide(currentSlide + 1);
		}

		function startAutoplay() {
			if (!isPaused && !autoplayInterval) {
				autoplayInterval = setInterval(nextSlide, slideDelay);
			}
		}

		function stopAutoplay() {
			if (autoplayInterval) {
				clearInterval(autoplayInterval);
				autoplayInterval = null;
			}
		}

		function pauseAutoplay() {
			isPaused = true;
			stopAutoplay();
		}

		function resumeAutoplay() {
			isPaused = false;
			startAutoplay();
		}

		slider.addEventListener('mouseenter', pauseAutoplay);
		slider.addEventListener('mouseleave', resumeAutoplay);

		startAutoplay();

		window.addEventListener('beforeunload', stopAutoplay);
	}
})();

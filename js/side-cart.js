
(function ($) {
	const state = {
		open: false,
		busy: false
	};

	//const blur_container = '.wsc-body';
	const blur_container = '.box-products, .cart_totals';

	const NONCE_NAME = 'wsc_nonce';

	function openDrawer() {

		$(blur_container).addClass("blurred-div");
		showLoader();


		state.open = true;
		$('.wsc-overlay').addClass('active');
		$('.wsc-drawer').addClass('open');
		refreshCart();
	}

	function closeDrawer() {
		state.open = false;
		$('.wsc-overlay').removeClass('active');
		$('.wsc-drawer').removeClass('open');
		$('body').removeClass('no-scroll');
		$('html').removeClass('no-scroll');
	}

	function ajax(action, data) {
		if (state.busy) return $.Deferred().reject();
		state.busy = true;
		data = $.extend({}, data || {}, { action: action });
		data[NONCE_NAME] = WSC.nonce;
		return $.post(WSC.ajax, data).always(function () { state.busy = false; });
	}

	function refreshCart() {
		ajax('wsc_get_cart').done(function (resp) {

			if (resp && resp.success) {
				$('.wsc-body').html(resp.data.html);
				$('.wsc-total-val').html(resp.data.total_html);
				$('.wsc-subtotal-val').html(resp.data.subtotal_html);
				$('.wsc-cart-count').text(resp.data.count);
			} else if (resp && resp.data && resp.data.html) {
				$('.wsc-body').html(resp.data.html);
			}

			//$('.box-products').slick('unslick');
			//$('.box-products').slick('destroy');
			$('.box-products').slick({
				arrows: false,
				dots: false,
				infinite: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				centerMode: false,
				centerPadding: 0,
				mobileFirst: true,
				responsive: [
					{
						breakpoint: 768, // This is your breakpoint for desktop/tablet. Adjust as needed.
						settings: "unslick" // At this breakpoint and above, the slider will be unslicked
					},
					{
						breakpoint: 0, // This applies to all screens smaller than the breakpoint above
						settings: {
							arrows: false,
							dots: false,
							infinite: true,
							slidesToShow: 1,
							slidesToScroll: 1,
							centerMode: false,
							centerPadding: 0
						}
					}
				]
			});

			//$('.side-cart-also-like .products').slick('destroy');
			$('.side-cart-also-like .products').slick({
				arrows: false,
				dots: false,
				infinite: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				centerMode: false,
				centerPadding: 0,
				mobileFirst: true,
				responsive: [
					{
						breakpoint: 768, // This is your breakpoint for desktop/tablet. Adjust as needed.
						settings: "unslick" // At this breakpoint and above, the slider will be unslicked
					},
					{
						breakpoint: 0, // This applies to all screens smaller than the breakpoint above
						settings: {
							arrows: false,
							dots: false,
							infinite: true,
							slidesToShow: 1,
							slidesToScroll: 1,
							centerMode: false,
							centerPadding: 0
						}
					}
				]
			});

			$(blur_container).removeClass("blurred-div");
			hideLoader();

		});
	}

	function updateQty(key, qty) {

		ajax('wsc_update_qty',
			{ key: key, qty: qty })
			.done(function () {
				refreshCart();
				$(document.body).trigger('wc_fragment_refresh');
			});
	}

	function removeItem(key) {
		ajax('wsc_remove_item', {
			key: key
		})
			.done(function () {
				refreshCart();
				$(document.body).trigger('wc_fragment_refresh');
			});
	}

	function removeItemAddItem(key, key2, parentid, keyid, boxname, boxprice) {

		ajax('wsc_remove_item_add_item', {
			key: key,
			key2: key2,
			parentid: parentid,
			keyid: keyid,
			boxname: boxname,
			boxprice: boxprice
		})
			.done(function () {

				refreshCart();
				console.log('remove item add item completed....');
				$(document.body).trigger('wc_fragment_refresh');
				// setTimeout(function(){
				// 	$('.ring-box-name').text(boxname);
				// }, 3000 );



			});

		// jQuery(document.body).on('wc_fragment_refresh', function() {
		// 	// Your custom function or code to execute after wc_fragment_refresh
		// 	alert('calling it...');
		// 	$('.ring-box-name').text(boxname);
		// 	// console.log('wc_fragment_refresh event triggered and my custom function is running!');
		// 	// yourCustomFunction(); // Call your specific function here
		// });

	}

	function applyCoupon(code) {
		ajax('wsc_apply_coupon', { code: code }).done(function (resp) { refreshCart(); if (resp && resp.data && resp.data.message) { alert(resp.data.message); } });
	}

	// Openers
	$(document).on('click', '.bario-wsc-float-btn, .wsc-float-btn, .wsc-open', function (e) { e.preventDefault(); openDrawer(); });
	$(document).on('click', '.wsc-close, .wsc-overlay', function (e) { e.preventDefault(); closeDrawer(); });

	// Qty controls & remove
	$(document).on('click', '.wsc-qty .inc', function () {

		$(blur_container).addClass("blurred-div");
		showLoader();

		const row = $(this).closest('[data-key]');
		const key = row.data('key');
		const input = row.find('input');
		const val = parseInt(input.val() || 1, 10) + 1;
		input.val(val);
		updateQty(key, val);

	});



	$(document).on('click', '.wsc-qty .dec', function () {

		$(blur_container).addClass("blurred-div");
		showLoader();

		const row = $(this).closest('[data-key]');
		const key = row.data('key');
		const input = row.find('input');
		let val = Math.max(0, parseInt(input.val() || 1, 10) - 1);
		input.val(val);
		updateQty(key, val);
	});


	$(document).on('change', '.wsc-qty input', function () { const row = $(this).closest('[data-key]'); const key = row.data('key'); let val = parseInt($(this).val() || 1, 10); if (isNaN(val) || val < 0) val = 0; updateQty(key, val); });


	$(document).on('click', '.wsc-remove', function (e) {
		e.preventDefault();
		const row = $(this).closest('[data-key]');
		const key = row.data('key');
		removeItem(key);
	}
	);

	// Coupon
	$(document).on('submit', '.wsc-coupon-form', function (e) { e.preventDefault(); const code = $(this).find('input[name="coupon_code"]').val(); if (code) { applyCoupon(code); } });

	// Auto-open after add to cart (WooCommerce triggers this)
	$(document.body).on('added_to_cart', function () { openDrawer(); refreshCart(); });
	$(document.body).on('removed_from_cart', function () { refreshCart(); });

	// Keep count fresh when Woo updates fragments
	$(document.body).on('wc_fragments_refreshed wc_fragment_refresh', function () { refreshCart(); });

	$(document).on('click', '.wsc-close, .wsc-overlay', function (e) {
		e.preventDefault();
		closeDrawer();
	});

	// Close cart when pressing the Esc key
	$(document).on('keyup', function (e) {
		if (e.key === "Escape") {
			closeDrawer();
		}
	});


	// CART SCROLL AND OPENING
	var $sideCart = $('.side-cart');
	let $barioCart = $('.bario-wsc-float-btn');

	// Open cart
	$('.open-cart').on('click', function () {
		$sideCart.addClass('open');
		$('body').addClass('no-scroll'); // disable background scroll
	});

	$('.bario-wsc-float-btn').on('click', function () {
		$barioCart.addClass('open');
		$('body').addClass('no-scroll');
		$('html').addClass('no-scroll');
	});


	// Close cart
	$('.close-cart').on('click', function () {
		$sideCart.removeClass('open');
		$('body').removeClass('no-scroll'); // enable background scroll
		$('html').removeClass('no-scroll');
	});

	// // Optional: close cart when clicking outside
	// $(document).on('click', function(e) {
	//     if ($sideCart.hasClass('open') && !$(e.target).closest('.side-cart, .open-cart').length) {
	//         $sideCart.removeClass('open');
	//         $('body').removeClass('no-scroll');
	// 		$('html').removeClass('no-scroll');
	//     }
	// });

	// // Optional: close cart with ESC key
	// $(document).on('keydown', function(e) {
	//     if (e.key === "Escape" && $sideCart.hasClass('open')) {
	//         $sideCart.removeClass('open');
	//         $('body').removeClass('no-scroll');
	//     }
	// });


	$(document).on('click', '.checkbox-box', function (e) {

		//$(".box-products").addClass("blurred-div");
		$(blur_container).addClass("blurred-div");

		showLoader();

		e.preventDefault();

		const to_remove = $(this).closest('[data-key-to-remove]');
		const to_add = $(this).closest('[data-key-to-add]');
		const parent_id = $(this).closest('[data-parent-id]');
		const parent_key = $(this).closest('[data-parent-key]');
		const box_name = $(this).closest('[data-box-name]');
		const box_price = $(this).closest('[data-box-price]');

		//alert(to_remove.data('key-to-remove'));
		//alert(to_add.data('key-to-add'));

		const key = to_remove.data('key-to-remove');
		const key2 = to_add.data('key-to-add');
		const parentid = parent_id.data('parent-id');
		const keyid = parent_key.data('parent-key');
		const boxname = box_name.data('box-name');
		const boxprice = box_price.data('box-price');

		// remove and add new item

		//alert('key:'+key+', key2:'+key2+', parentid:'+parentid+', keyid:'+keyid);

		removeItemAddItem(key, key2, parentid, keyid, boxname, boxprice);

	});

	function showLoader() {
		$('.b-loader').show();
	}

	function hideLoader() {
		$('.b-loader').hide();
	}

	if ($('.box-products').length) {
		$('.box-products').slick({
			arrows: false,
			dots: false,
			infinite: true,
			slidesToShow: 1,
			slidesToScroll: 1,
			centerMode: false,
			centerPadding: 0,
			mobileFirst: true,
			responsive: [
				{
					breakpoint: 768, // This is your breakpoint for desktop/tablet. Adjust as needed.
					settings: "unslick" // At this breakpoint and above, the slider will be unslicked
				},
				{
					breakpoint: 0, // This applies to all screens smaller than the breakpoint above
					settings: {
						arrows: false,
						dots: false,
						infinite: true,
						slidesToShow: 1,
						slidesToScroll: 1,
						centerMode: false,
						centerPadding: 0
					}
				}
			]
		});
	}


	if ($('.side-cart-also-like .products').length) {
		$('.side-cart-also-like .products').slick({
			arrows: false,
			dots: false,
			infinite: true,
			slidesToShow: 1,
			slidesToScroll: 1,
			centerMode: false,
			centerPadding: 0,
			mobileFirst: true,
			responsive: [
				{
					breakpoint: 768, // This is your breakpoint for desktop/tablet. Adjust as needed.
					settings: "unslick" // At this breakpoint and above, the slider will be unslicked
				},
				{
					breakpoint: 0, // This applies to all screens smaller than the breakpoint above
					settings: {
						arrows: false,
						dots: false,
						infinite: true,
						slidesToShow: 1,
						slidesToScroll: 1,
						centerMode: false,
						centerPadding: 0
					}
				}
			]
		});
	}








})(jQuery);
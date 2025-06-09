var loader = '<div id="loader"><div class="cssload-container"><div class="cssload-speeding-wheel"></div></div></div>',
	overlay = $('#overlay'),
	touchscreen = 0;
	select_instances = [];

if('ontouchstart' in window || (window.DocumentTouch && document instanceof window.DocumentTouch) || navigator.maxTouchPoints > 0 || window.navigator.msMaxTouchPoints > 0) {
	touchscreen = 1;
	$('body').addClass('touch');
} else {
	$('body').addClass('notouch');
	
	$('[data-product-block]').on('mouseenter', function(){
		$(this).find('.dropdown-block').slideDown(200);
	}).on('mouseleave', function(){
		$(this).find('.dropdown-block').slideUp(200);
	});
}

if(typeof(button) != 'function') { // fix no bootstrap
	$.fn.button = function(s){return;}
}

$(document).ready(function(){
	$('body').addClass('loaded');
});
	
$(document).find('[data-nice-select]').each(function(){
	NiceSelect.bind(this);
});
	
var setFooter = function(ind=0) {
	fh = $('.footer').outerHeight();
	$('.site-wrapper').css({'padding-bottom': (fh+ind)+'px'});
	$('.footer').css({'margin-top':'-'+fh+'px'});
}

function checkJson(json) {
	if(json.redirect == 1) {
		document.location.reload();
	} else if(json.redirect) {
		window.location.href = json.redirect;
	}
	if(json.warning) nrShowMessage(json.warning);
	if(json.log) console.log(json.log);
}

function nrShowMessage(m, t = 0) { ////// html
	var c = t ? 'success' : 'error';
	var html = '<div class="alert alert-'+c+'" data-alert>';
	html += '<img class="alert-image" src="/catalog/view/theme/noir/img/'+c+'.svg" alt="">';
	html += '<div class="alert-title">'+(t ? 'Sukces' : 'Ostrzeżenie')+'</div>';
	html += '<div class="alert-message">'+m+'</div>';
	html += '</div>';
	$('#alert').html(html);
	Fancybox.show(
		[
			{
				src: '#alert',
				type: 'inline',
				
			}
		],
		{
			dragToClose: false,
			trapFocus: false,
			placeFocusBack: false,
			hideScrollbar: false,
			on: {
				destroy: () => {$('#alert').html('');}
			}
		}
	);
}

function showModal(sel) {
	Fancybox.show(
		[
			{
				src: sel,
				type: 'inline',
				
			}
		],
		{
			dragToClose: false,
			trapFocus: false,
			placeFocusBack: false,
			hideScrollbar: false,
		}
	);
}

function register() {
	var m = $('#sign-up');
	m.find('.form-error').text('').hide();
	m.find('.input-error').removeClass('input-error');
	$.ajax({
		url: 'index.php?route=account/register/nr_register',
		type: 'post',
		dataType: 'json',
		data: m.find('form').serialize(),
		success: function(json) {
			checkJson(json);
			if(json.error) {
				$.each(json.error, function(i, v){
					if(v == 1) {
						m.find('[data-error="'+i+'"]').addClass('input-error');
					} else {
						m.find('[data-error="'+i+'"]').addClass('input-error').find('.form-error').text(v).show();
					}
				});
			}
		},
		error: function(data) {console.log(data.responseText);}
	});
}

function login() {
	$('#sign-in .form-error').text('').hide();
	$.ajax({
		url: 'index.php?route=account/login/nr_login',
		type: 'post',
		dataType: 'json',
		data: $('#sign-in form').serialize(),
		success: function(json) {
			checkJson(json);
			if(json.error) $('#sign-in .form-error').text(json.error).show();
		},
		error: function(data) {console.log(data.responseText);}
	});
}

function saveProfile() {
	$('#customer-profile .form-error').text('').hide();
	$('#customer-profile .input-error').removeClass('input-error');
	$.ajax({
		url: 'index.php?route=account/edit/nr_save',
		type: 'post',
		dataType: 'json',
		data: $('#customer-profile').serialize(),
		success: function(json) {
			//console.log(json); /////
			checkJson(json);
			if(json.error) {
				$.each(json.error, function(i, v){
					if(v == 1) {
						$('[data-error="'+i+'"]').addClass('input-error');
					} else {
						$('[data-error="'+i+'"]').addClass('input-error').find('.form-error').text(v).show();
					}
				});
			}
		},
		error: function(data) {console.log(data.responseText);}
	});
}

function deleteWishlist(id) {
	$.ajax({
		url: 'index.php?route=account/wishlist/nrDelete&product_id='+id,
		success: function(data) {
			document.location.reload();
		},
		error: function(data) {console.log(data.responseText);}
	});
}

var cart = {
	//'add': function(product_id, q = 1, o = []) {
	'add': function(data) {
		$('body').append(loader); //console.log(data);
		$.ajax({
			url: 'index.php?route=common/nr_cart/add',
			type: 'post',
			dataType: 'json',
			data: data,
			// data: {
			// 	product_id: product_id,
			// 	quantity: q,
			// 	option: o
			// },
			success: function(json) {
				$('#loader').remove();
				if (json.error) alert('Błąd: nie określono ważnego parametru!'); //json.error
				if (json.success) {
					minicartRefresh(json.number_products);
					//nrShowMessage(json.success, 1);
				}
			},
			error: function(data) {
				$('#loader').remove();
				console.log(data.responseText);
			}
		});
	},
	'update': function (id, q) {
		$('body').append(loader);
		$.ajax({
			url: 'index.php?route=common/nr_cart/edit',
			type: 'post',
			data: {
				cart_id: id,
				quantity: q
			},
			success: function(data) {
				$('#loader').remove();
				if(data) minicartRefresh(data);
			},
			error: function(data) {
				$('#loader').remove();
				console.log(data.responseText);
			}
		});
	},
	'remove': function (id) {
		$('body').append(loader);
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			dataType: 'json',
			data: {key: id},
			success: function(json) {
				$('#loader').remove();
				minicartRefresh(json.number_products);
			},
			error: function(data) {
				$('#loader').remove();
				console.log(data.responseText);
			}
		});
	}
}

function minicartRefresh(n) {
	var ct = $('#cart-total');
	ct.text(n);
	if(n > 0) {
		ct.removeClass('hidden');
	} else {
		ct.addClass('hidden');
	}
	$('[data-cart-list]').load('index.php?route=common/nr_cart/cart_list');
}

function addReview() {
	$('body').append(loader);
	var f = $(document).find('#form-review');
	f.find('.form-error').text('').hide();
	f.find('.input-error').removeClass('input-error');
	console.log(f.serialize());
	$.ajax({
		url: 'index.php?route=product/product/nr_write',
		type: 'post',
		dataType: 'json',
		data: f.serialize(),
		success: function(json) {
			$('#loader').remove();
			if (json.error) {
				$.each(json.error, function(i, v) {
					f.find('[data-error="'+i+'"]').addClass('input-error').find('.form-error').text(v).show();
				});
			}
			if (json.success) {
				Fancybox.close();
				nrShowMessage(json.success, 1);
				f.find('[name="text"]').val('');
				f.find('input[type="radio"]').prop('checked', false);
			}
		},
		error: function(data) {
			$('#loader').remove();
			console.log(data.responseText);
		}
	});
}

function counterUpdate() {
	_dif = _dif - 1;
	if(_dif < 0) {
		if(_td) {
			_dif = 9*60*60;
			_td = 0;
		// } else {
		// 	_dif = 15*60*60;
		// 	_td = 1;
		}
		$('[data-termin]').toggleClass('today');
	}
	var	h = Math.floor((_dif % 86400)/3600),
		m = Math.floor((_dif % 3600)/60),
		s = Math.floor(_dif % 60);
	$('[data-timer]').html(h+':'+String(m).padStart(2, '0')+':'+String(s).padStart(2, '0'));
}

function getAddress(id = 0, main = 0) {
	$('#address .form-error').text('').hide();
	$('#address .input-error').removeClass('input-error');
	$.ajax({
		url: 'index.php?route=account/address/nr_address_form&id='+id,
		success: function(data) {
			$('#address').html(data);
			Fancybox.show(
				[{
					src: '#address',
					type: 'inline',
				}],
				{
					dragToClose: false,
					trapFocus: false,
					placeFocusBack: false,
					hideScrollbar: false
				}
			);
			select_instances = [];
			$('#address').find('[data-nice-select]').each(function(i) {
				select_instances.push(NiceSelect.bind(this, {placeholder: $(this).data('nice-select')}));
			});
		},
		error: function(data) {console.log(data.responseText);}
	});
}

function saveAddress() {
	$('[data-error]').removeClass('input-error').find('.form-error').text('').hide();
	$.ajax({
		url: 'index.php?route=account/address/nr_save',
		type: 'post',
		dataType: 'json',
		data: $('#address-form').serialize(),
		success: function(json) {
			checkJson(json);
			//console.log(json);
			if(json.error) {
				$.each(json.error, function(i, v){
					if(v == 1) {
						$('[data-error="'+i+'"]').addClass('input-error');
					} else {
						$('[data-error="'+i+'"]').addClass('input-error').find('.form-error').text(v).show();
					}
				});
			}
		},
		error: function(data) {console.log(data.responseText);}
	});
}

function getMenuProducts(b, catid) {
	b.load('index.php?route=common/menu/get_products&category_id='+catid);
}

$(document).on('click', '[data-wishlist]', function() {
	var th = $(this);
	$.ajax({
		url: 'index.php?route=account/wishlist/nrWishlist',
		type: 'post',
		dataType: 'json',
		data: {
			product_id: th.data('wishlist'),
		},
		success: function(json) {
			if(json.total > 0) {
				$('#wishlist-total').html(json.total).removeClass('hidden');
			} else {
				$('#wishlist-total').html(json.total).addClass('hidden');
			}
			th.toggleClass('active');
		},
		error: function(data) {console.log(data.responseText);}
	});
});

$('[data-currency-select]').on('change', function() {
	var v = $(this).val();
	$.ajax({
		url: 'index.php?route=common/currency/currency',
		type: 'post',
		data: {code: v},
		success: function() {
			document.location.reload();
		},
		error: function(data) {console.log(data.responseText);}
	});
});

$('[data-get-search]').on('click', function(){
	$('#search').slideToggle();
});
$('[data-search-close]').on('click', function(){
	$('#search').slideUp();
});
$('#button-search-go').on('click', function() {
	url = 'index.php?route=product/search';
	var search = $('#search input[name=\'search\']').prop('value');
	if (search) {
		url += '&search=' + encodeURIComponent(search);
	}
	url += '&sub_category=true';
	url += '&description=true';
	location = url;
});
$('#search input[name=\'search\']').on('keydown', function(e) {
	if (e.keyCode == 13) {
		$('#button-search-go').trigger('click');
	}
});

$(document).on('click', '[data-close-popup]', function() {
	$(this).parents('.popup').find('.carousel__button.is-close').click();
});

$('[data-mail-discount-submit]').on('click', function() {
	var b = $(this).parents('[data-mail-discount]'),
		sc = $('#mail-coupon-success');
	b.find('.form-error').html('').hide();
	if(sc.find('[data-coupon-code]').length) return showModal('#mail-coupon-success');
	
	$.ajax({
		url: 'index.php?route=extension/module/nr_mail_discount/send',
		type: 'post',
		dataType: 'json',
		data: {email: b.find('[name="discount-email"]').val()},
		success: function(json) {
			//console.log(json);
			if(json.error) {
				b.find('.form-error').html(json.error).show();
			} else {
				sc.find('.inner').append('<div class="coupon-code" data-coupon-code>'+json.coupon+'</div>');
				showModal('#mail-coupon-success');
			}
		},
		error: function(data) {console.log(data.responseText);}
	});
});

$('#cart').on('click', function() {
	$('body').addClass('open-cart');
	overlay.fadeIn();
});

$('[data-cart-close]').on('click', function() {
	$('body').removeClass('open-cart');
	overlay.fadeOut();
});

$('[data-cart-add]').on('click', function () {
	const button = $(this);
	const card = button.closest('[data-product-block]');
	const form = button.closest('form[data-product]');
	let data = { quantity: 1 };

	if (card.length) {
		// === Карточка товара в списке ===
		const pid = button.data('pid') || card.find('[data-pid]').data('pid');
		const qty = card.find('[data-input-quantity]').val() || 1;
		data.product_id = pid;
		data.quantity = qty;

		// ищем активную или первую опцию в карточке (если есть кнопка выбора упаковки)
		const optBtn = card.find('.product-item-btn-option.active, .product-item-btn-option').first();
		if (optBtn.length) {
			const oid = optBtn.data('oid');
			const ovid = optBtn.data('ovid');
			if (oid && ovid) {
				data[`option[${oid}]`] = ovid;
			}
		}

	} else if (form.length) {
	// === Страница товара ===
	const container = form.closest('.right'); // <-- важно!
	data.product_id = form.find('[name="product_id"]').val();
	data.quantity = form.find('[data-input-quantity]').val() || 1;

	// собираем опции не из формы, а из контейнера, где они реально отрисованы
	container.find('select, input, textarea').each(function () {
		const input = $(this);
		if (input.is(':checkbox') || input.is(':radio')) {
			if (!input.is(':checked')) return;
		}
		const name = input.attr('name');
		if (name && name.startsWith('option[')) {
			data[name] = input.val();
		}
	});
}

	if (!data.product_id) {
		console.warn('product_id не найден');
		return;
	}

	console.log('Final data:', data);
	cart.add(data);
});






function addtxtcard() { //AG 12.05.2024
	data = {
		product_id: 676,
		quantity: 1,
		txtcard: $('#txtcard').val()
	};
	cart.add(data); //alert($('#txtcard').val());
	//$.cookie("txtcard", $('#txtcard').val());
	//console.log('txt card');
}

$(document).on('click', '[data-qnt-btn]', function(){
	var th = $(this),
		b = th.parents('[data-quantity]'),
		i = b.find('input'),
		v = parseInt(i.val());
	if(th.data('qnt-btn') > 0) {
		v++;
		if(b.is('[data-max]') && b.data('max') > 0 && v > b.data('max')) return false;
	} else {
		if(v < 2) return false;
		v = v - 1;
		if(b.is('[data-min]') && v < b.data('min')) return false;
	}
	i.val(v);
	if(th.parents('[data-cart-item]').length) cart.update(b.data('cart'), v);
	if(typeof(cartUpdate) == 'function') cartUpdate(b.parents('[data-product]'), v);
});

$(document).on('input', '[data-input-quantity]', function(){
	var th = $(this),
		b = th.parents('[data-quantity]'),
		v = parseInt(th.val());
	if(b.is('[data-min]') && v < b.data('min')) th.val(b.data('min'));
	if(b.is('[data-max]') && b.data('max') > 0 && v > b.data('max')) th.val(b.data('max'));
	if(th.parents('[data-cart-item]').length) cart.update(b.data('cart'), v);
	if(typeof(cartUpdate) == 'function') cartUpdate(b.parents('[data-product]'), v);
});

/*$('[data-spoiler-toggler]').on('click', function(e) {
	if(e.target.getAttribute('data-button') != null) return;
	var th = $(this),
		p = th.parent(),
		o = p.hasClass('open');
	th.parents('[data-spoilers]').find('[data-spoiler]').each(function(){
		$(this).removeClass('open').find('[data-spoiler-content]').slideUp();
		if($(this).find('[data-hidden]').length) $(this).find('[data-hidden]').fadeOut();
	});
	if(!o) {
		p.addClass('open').find('[data-spoiler-content]').slideDown();
		if(th.find('[data-hidden]').length) th.find('[data-hidden]').fadeIn();
	}
});*/

$('[data-catalog-root-item]').on('mouseover', function() {
	if($(this).hasClass('active')) return;
	$('[data-catalog-root-item]').removeClass('active');
	var p = $(this).addClass('active').find('[data-menu-products]');
	if(!p.find('.menu-products-wrap').length) getMenuProducts(p, $(this).data('catalog-root-item'));
});

$('[data-menu-toggle]').on('click', function() {
	$('body').toggleClass('menu-open');
});

$('[data-filter-toggle]').on('click', function() {
	$('body').addClass('filter-open');
});

$('body').on('click', '[data-filter-close]', function() {
	$('body').removeClass('filter-open');
});

$('[data-item-trigger]').on('click', function(e) {
	if(window.screen.width > 1449) return true;
	e.preventDefault();
	e.stopPropagation();
	$(this).parents('[data-catalog-root-item]').toggleClass('open').find('.submenu').slideToggle();
	return false;
});

$('[data-pannel-open]').on('click', function(e) {
	$('#column-left').addClass('open');
});

$('[data-pannel-close]').on('click', function(e) {
	$('#column-left').removeClass('open');
});

$('[data-footer-menu]').on('click', function() {
	if(window.screen.width > 450) return false;
	$(this).parent().toggleClass('open').find('nav').slideToggle();
});

$( document ).ready(function() {
    //if(window.screen.width > 1449) {
		$('#menu_ag_1').on('click', function() {
			//$('#menu_panel_ag_1').stop().slideUp();
			$('#menu_panel_ag_2').stop().slideUp();
			$('#menu_panel_ag_3').stop().slideUp();
			$('#menu_panel_ag_1').stop().slideToggle();
		}); 
		$('#menu_ag_2').on('click', function() {
			$('#menu_panel_ag_1').stop().slideUp();
			//$('#menu_panel_ag_2').stop().slideUp();
			$('#menu_panel_ag_3').stop().slideUp();
			$('#menu_panel_ag_2').stop().slideToggle();
		}); 
		$('#menu_ag_3').on('click', function() {
			$('#menu_panel_ag_1').stop().slideUp();
			$('#menu_panel_ag_2').stop().slideUp();
			//$('#menu_panel_ag_3').stop().slideUp();
			$('#menu_panel_ag_3').stop().slideToggle();
		}); 
		getMenuProducts($('[data-catalog-root-item].active [data-menu-products]'), $('[data-catalog-root-item].active').data('catalog-root-item'));
	//}
}); //AG

// if(window.screen.width > 1449) {
// 	getMenuProducts($('[data-catalog-root-item].active [data-menu-products]'), $('[data-catalog-root-item].active').data('catalog-root-item'));
// 	$('[data-menu-parent]').on('click', function() {
// 		$('.main-menu').find('.catalog-pannel').stop().slideUp().find('.catalog-pannel').stop().slideUp().find('.catalog-pannel').stop().slideUp();
// 		$(this).find('.catalog-pannel').stop().slideToggle();
// 	}); //AG
// 	// $('[data-menu-parent]').on('mouseenter', function() {
// 	// 	$(this).find('.catalog-pannel').stop().slideDown();
// 	// }).on('mouseleave', function() {
// 	// 	$(this).find('.catalog-pannel').stop().slideUp();
// 	// });
// }

$(window).on('resize', function() {
	if(window.screen.width > 1449 && !$('[data-catalog-root-item].active .menu-products-wrap').length) {
		getMenuProducts($('[data-catalog-root-item].active [data-menu-products]'), $('[data-catalog-root-item].active').data('catalog-root-item'));
	} else {
		$('.catalog-pannel').removeAttr('style');
	}
	if(window.screen.width > 450) $('.footer-column nav').removeAttr('style');
});

// === Правильная отправка упаковки в корзину ===
$(document).on('click', '.product-item-btn-option', function() {
  var $btn  = $(this),
      pid   = $btn.data('pid'),
      qty   = $btn.closest('[data-product-block]')
                .find('[data-input-quantity]').val() || 1,
      oid   = $btn.data('oid'),
      ovid  = $btn.data('ovid'),
      data  = {
        product_id: pid,
        quantity:   qty
      };

  // Добавляем ключ 'option[<product_option_id>]' с нужным value
  data['option[' + oid + ']'] = ovid;

  cart.add(data);
});


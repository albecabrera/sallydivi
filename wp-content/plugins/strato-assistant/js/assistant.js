var tb_position;

const cardClasses = 'assistant-card animate';
const cardSelector = '.assistant-card.animate';

let log_active = false;
var numItemsToInstall = 0;
var numItemsInstalled = 0;

jQuery( document ).ready( ( $ ) => {

	/**
	 * WP Thickbox tb_position() is being overriden by media-upload.js (known bug: https://core.trac.wordpress.org/ticket/39267)
	 * we fix this by writing our own tb_position() and take the occasion to customize some stuff
	 */
	tb_position = () => {
		const tb_window = $( '#TB_window' );
		const tb_inner = $( '#TB_ajaxContent' );
		const custom_tb_width = 700;
		const custom_tb_height = tb_inner.children( ':first' ).outerHeight( true );

		tb_window
			.addClass(
				'card-lightbox'
			).css( {
			marginLeft: '-' + parseInt( ( custom_tb_width / 2 ), 10 ) + 'px',
			marginTop: '-' + parseInt( ( custom_tb_height / 2 ), 10 ) + 'px',
			width: custom_tb_width + 'px'
		} );

		tb_inner
			.css( {
				width: custom_tb_width + 'px',
				height: 'auto'
			} );
	};

	/**
	 * Temporary logging function to not show console messages by default
	 *
	 * @param message
	 */
	const log = ( message ) => {
		if ( log_active ) {
			console.log( message );
		}
	};

	/**
	 * Show first card with opening animation
	 *
	 * @param firstStep
	 */
	const cardFadeIn = ( firstStep ) => {
		const card = $( cardSelector );
		const firstStepId = firstStep.attr( 'id' ).replace( 'card-', '' );

		card.attr( 'class', cardClasses + ' card-' + firstStepId )
			.css( { transform: 'rotateX(5deg) rotateY(5deg) rotateZ(0deg) scale(.91)' } )
			.addClass( 'morphing-first' );

		setTimeout( () => {
			$( cardSelector )
				.removeClass( 'morphing-first' )
				.css( { transform: 'rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1)' } );
		}, 400 );

		firstStep.show();
	};

	/**
	 * Show a card with transition animation
	 *
	 * @param stepId
	 */
	const cardSwitch = ( stepId ) => {
		const card = $( cardSelector );
		const nextStep = $( '#card-' + stepId );

		card.find( '.active' ).removeClass( 'active' );

		card.attr( 'class', cardClasses + ' card-' + stepId )
			.css( { transform: 'rotateX(-5deg) rotateY(5deg) rotateZ(0deg) scale(.91)' } )
			.addClass( 'morphing' );

		setTimeout( () => {
			card.removeClass( 'morphing' )
				.css( { transform: 'rotateX(0deg) rotateY(0deg) rotateZ(0deg) scale(1)' } );
		}, 200 );

		nextStep.addClass( 'active' );
	};

	/**
	 * Load the preview of a given theme
	 *
	 * @param type
	 * @param theme
	 */
	const loadPreview = ( type, theme ) => {
		const loadedClass = type + '-' + theme + '-loaded';

		if ( ! $( '#theme-preview-loader' ).hasClass( loadedClass ) ) {
			$.ajax( {
				type: 'POST',
				dataType: 'html',
				url: ajax_assistant_object.ajaxurl,
				data: 'site_type=' + type + '&theme=' + theme + '&action=ajaxpreview',

			} ).done( ( response ) => {
				$( '#theme-preview-loader' )
					.removeClass()
					.addClass( loadedClass )
					.html( response );
			} );
		}
	};

	/**
	 * Installs a list of plugins, redirects to customizer when finished
	 *
	 * @param plugins
	 * @param url
	 * @param wp_query
	 * @param site_type
	 */
	function installPlugins(plugins, url, wp_query, site_type) {
		const plugin = plugins.shift();

		log( '=================================' );
		log( 'Installing "' + plugin + '" plugin...' );

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: url,
			data: wp_query + '&site_type=' + site_type + '&asset=' + plugin + '&asset_type=plugin&action=ajaxinstall',

		} ).done( () => {
			log( 'OK' );
		} ).fail( () => {
			log( 'ERROR' );
		} ).always( () => {
			numItemsInstalled++
			updateProgressBar()

			log( '=================================' );
			if (plugins.length > 0) {
				installPlugins(plugins, url, wp_query, site_type);
			} else {
				log( '=== DONE ===' );
				window.location = $("[name='redirect_url']").val();
			}
		} );
	}

	/**
	* Updates the progress bar
	*/
	function updateProgressBar() {
		const bar = document.querySelector( '.determinate' )
		bar.style.width = Math.ceil( ( numItemsInstalled / numItemsToInstall ) * 100 ) + '%';
	}

	/**
	 * Installs the selected theme, launches the plugins install when finished
	 *
	 * @param url
	 * @param wp_query
	 * @param site_type
	 */
	function installAssets(url, wp_query, site_type) {
		const theme = $( "[name='theme']" ).val();
		const plugins = $( "[name='plugins[]']" ).map( ( index, element ) => { return $( element ).val(); } ).get();

		numItemsToInstall = 1 + plugins.length;
		updateProgressBar()

		log( '=================================' );
		log( 'Installing ' + theme + ' theme...' );

		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: url,
			data: wp_query + '&site_type=' + site_type + '&asset=' + theme + '&asset_type=theme&action=ajaxinstall',

		// When it's done (or failed), then install plugins
		} ).done( () => {
			log( 'OK' );
		} ).fail( () => {
			log( 'ERROR' );
		} ).always( () => {
			numItemsInstalled++
			updateProgressBar()

			log( '=================================' );
			installPlugins(plugins, url, wp_query, site_type);
		} );
	}

	/**
	 * Installation of the site type
	 * (selected theme + recommended plugins)
	 */
	const startInstall = () => {
		const url = ajax_assistant_object.ajaxurl;

		// Display progress screen
		cardSwitch( 'install' );

		// Retrieve installation parameters from form
		const wp_query = '_wpnonce=' + $( "[name='_wpnonce']" ).val()
			+ '&_wp_http_referer=' + $( "[name='_wp_http_referer']" ).val();
		const site_type = $( "[name='site_type']" ).val();

		log( '=== BEGIN ===' );

		// Configure according to site type
		log( '=================================' );
		log( 'Configuring website options...' );
		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: url,
			data: wp_query + '&site_type=' + site_type + '&action=ajaxsetup',

		// When it's done (or failed), then install theme and plugins
		} ).done( () => {
			log( 'OK' );
		} ).fail( () => {
			log( 'ERROR' );
		} ).always( () => {
			log( '=================================' );
			installAssets(url, wp_query, site_type);
		} );
	};

	const installHiddenUseCase = () => {
		const url = ajax_assistant_object.ajaxurl;

		cardSwitch( 'install-indeterminate' );

		// Retrieve installation parameters from DOM
		const wp_query = '_wpnonce=' + $( "[name='_wpnonce']" ).val()
			+ '&_wp_http_referer=' + $( "[name='_wp_http_referer']" ).val();


		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: url + '?' + wp_query + '&action=ajaxinstall_hiddenusecase',
			data: {
				"usecase": $( "[name='usecase']" ).val(),
			},
		} ).always( ( response ) => {
			window.location = $( "[name='redirect_to']" ).val();
		} );
	}

	// Open the site type menu (mobile)
	$( '.diys-sidebar-menu-btn' ).on( 'click', ( event ) => {
		event.preventDefault();

		$( '.diys-sidebar-wrapper' ).toggleClass( 'open' );
	} );

	// Load the list of themes for each site type
	$( '.diys-sidebar-tabs a' ).on( 'click', ( event ) => {
		event.preventDefault();

		const element = $( event.currentTarget );
		const type = element.attr( 'id' ).replace( 'site-type-', '' );

		$( '.diys-sidebar-wrapper' ).removeClass( 'open' );
		$( '.current-site-type' ).text( element.text() );

		$( '.diys-sidebar-tabs li' ).removeClass( 'active' );
		element.parent( 'li' ).addClass( 'active' );

		$( '.theme-list' ).removeClass( 'active' );
		$( '#themes-' + type ).addClass( 'active' );

		if ( ! $( '#themes-' + type + ' .theme-list-inner' ).hasClass( 'loaded' ) ) {
			$.ajax( {
				type: 'POST',
				dataType: 'html',
				url: ajax_assistant_object.ajaxurl,
				data: 'site_type=' + type + '&action=ajaxload',

			} ).done( ( response ) => {
				$( '#themes-' + type + ' .theme-list-inner' )
					.addClass( 'loaded' )
					.html( response );
			} );
		}
	} );

	// Open the first card (with the "active" class)
	const firstStep = $( cardSelector + ' .card-step.active' );
	if ( firstStep.length > 0 ) {
		cardFadeIn( firstStep );
	}

	// Pop open the card (using WP thickbox) in the Customizer
	$( window ).on( 'load', () => {
		const customizerCard = $( '#card-congrats-lightbox' );

		if ( customizerCard.length > 0 && typeof tb_show === 'function' ) {
			$( '#TB_window' ).remove();
			$( '#TB_overlay' ).remove();

			tb_show( '', '#TB_inline?inlineId=card-congrats-lightbox&modal=true', null );
		}
	} );

	// Trigger the card next action(s)
	const step = $( cardSelector + ' .card-step' );

	step.on( 'click', '[id^=goto-]', ( event ) => {
		event.preventDefault();

		const element = $( event.currentTarget );

		const nextStepId = element.attr( 'id' ).replace( 'goto-', '' );
		cardSwitch( nextStepId );

		// Show the list of themes of the first site type
		if ( nextStepId === 'design' ) {
			if ( element.data( 'site-type' ) ) {
				$( '.diys-sidebar-tabs a#site-type-' + element.data( 'site-type' ) ).trigger( 'click' );
			} else {
				$( '.diys-sidebar-tabs a:first' ).trigger( 'click' );
			}
		}
		if ( nextStepId === 'preview' ) {
			loadPreview( element.data( 'site-type' ), element.data( 'theme' ) );
		}
	} );

	step.on( 'click', '.theme-btn', ( event ) => {
		event.preventDefault();
		event.stopPropagation();

		startInstall( event.target );
	} );

	step.on( 'click', '#install-hidden-usecase', ( event ) => {
		event.preventDefault();
		event.stopPropagation();

		installHiddenUseCase( event.target );
	} );

	// Show the list of themes of the first site type if we got to the "design" step directly
	let currentUseCase = $( '.diys-sidebar-tabs .current a' );

	if ( ! currentUseCase.length ) {
		currentUseCase = $( '.diys-sidebar-tabs a:first' );
	}
	if ( currentUseCase.is( ':visible' ) ) {
		currentUseCase.trigger( 'click' );
	}

	// Show the preview of the theme if we got to the "preview" step directly
	const urlParams = new URLSearchParams(window.location.search);

	if ( urlParams.has('setup_type' ) && urlParams.has('setup_theme' ) ) {
		loadPreview( urlParams.get('setup_type' ), urlParams.get('setup_theme' ) );
	}

} );
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.components || ! wp.element || ! wp.domReady ) {
		return;
	}

	var el = wp.element.createElement;
	var Button = wp.components.Button;
	var Notice = wp.components.Notice;
	var ComboboxControl = wp.components.ComboboxControl;
	var useState = wp.element.useState;
	var data = window.forgeiaNvidiaSettings || {};

	function getModelOptions( models ) {
		return ( models || [] ).map( function ( model ) {
			return {
				label: model.id,
				value: model.id,
			};
		} );
	}

	function filterOptions( options, filter ) {
		if ( ! filter ) {
			return options;
		}
		var lower = filter.toLowerCase();
		return options.filter( function ( opt ) {
			return opt.label.toLowerCase().indexOf( lower ) !== -1;
		} );
	}

	function hiddenInputName( key ) {
		return data.optionName + '[' + key + ']';
	}

	function App() {
		var labels = data.labels || {};
		var keys = data.keys || {};
		var settings = data.settings || {};
		var textKey = keys.text || 'default_text_generation_model';
		var imageKey = keys.image || 'default_image_generation_model';
		var textState = useState( settings[ textKey ] || '' );
		var imageState = useState( settings[ imageKey ] || '' );
		var textModel = textState[ 0 ];
		var setTextModel = textState[ 1 ];
		var imageModel = imageState[ 0 ];
		var setImageModel = imageState[ 1 ];

		var textFilterState = useState( '' );
		var imageFilterState = useState( '' );
		var textFilterValue = textFilterState[ 0 ];
		var setTextFilterValue = textFilterState[ 1 ];
		var imageFilterValue = imageFilterState[ 0 ];
		var setImageFilterValue = imageFilterState[ 1 ];

		var allTextOptions = getModelOptions( data.textModels );
		var allImageOptions = getModelOptions( data.imageModels );

		var textFilteredOptions = filterOptions( allTextOptions, textFilterValue );
		var imageFilteredOptions = filterOptions( allImageOptions, imageFilterValue );

		return el(
			'div',
			{ className: 'forgeia-nvidia-settings-app' },
			data.loadError
				? el(
						Notice,
						{
							status: 'warning',
							isDismissible: false,
						},
						labels.loadingFailed || ''
				  )
				: null,
			el(
				'div',
				{ className: 'forgeia-nvidia-settings-card' },
				el(
					'div',
					{ className: 'forgeia-nvidia-settings-header' },
					el(
						'div',
						{ className: 'forgeia-nvidia-settings-header-text' },
						el( 'h2', null, labels.heading || 'Default models' ),
						el( 'p', null, labels.description || '' )
					)
				),
				el(
					'div',
					{ className: 'forgeia-nvidia-settings-body' },
					el(
						'div',
						{ className: 'forgeia-nvidia-settings-field' },
						el( 'label', null, labels.textLabel || 'Text generation model' ),
						el(
							'p',
							{ className: 'description' },
							'Default model for chat and text completions.'
						),
						el( 'input', {
							type: 'hidden',
							name: hiddenInputName( textKey ),
							value: textModel,
						} ),
						el( ComboboxControl, {
							value: textModel || null,
							options: textFilteredOptions,
							onChange: function ( v ) { setTextModel( v || '' ); },
							onFilterValueChange: setTextFilterValue,
							placeholder: labels.emptyOption || 'Use provider ordering',
							__nextHasNoMarginBottom: true,
						} )
					),
					el(
						'div',
						{ className: 'forgeia-nvidia-settings-field' },
						el( 'label', null, labels.imageLabel || 'Image generation model' ),
						el(
							'p',
							{ className: 'description' },
							'Default model for image generation.'
						),
						el( 'input', {
							type: 'hidden',
							name: hiddenInputName( imageKey ),
							value: imageModel,
						} ),
						el( ComboboxControl, {
							value: imageModel || null,
							options: imageFilteredOptions,
							onChange: function ( v ) { setImageModel( v || '' ); },
							onFilterValueChange: setImageFilterValue,
							placeholder: labels.emptyOption || 'Use provider ordering',
							__nextHasNoMarginBottom: true,
						} )
					),
					el(
						Button,
						{
							variant: 'primary',
							type: 'submit',
						},
						labels.saveButton || 'Save settings'
					)
				)
			)
		);
	}

	wp.domReady( function () {
		var root = document.getElementById( 'forgeia-nvidia-settings-root' );
		if ( ! root ) {
			return;
		}

		if ( wp.element.createRoot ) {
			wp.element.createRoot( root ).render( el( App ) );
			return;
		}

		wp.element.render( el( App ), root );
	} );
} )( window.wp );

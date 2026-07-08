( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.components || ! wp.element || ! wp.domReady ) {
		return;
	}

	var el = wp.element.createElement;
	var Button = wp.components.Button;
	var Notice = wp.components.Notice;
	var SelectControl = wp.components.SelectControl;
	var useState = wp.element.useState;
	var data = window.forgeiaNvidiaSettings || {};

	function getOptions( models ) {
		var labels = data.labels || {};
		var options = [
			{
				label: labels.emptyOption || 'Use provider ordering',
				value: '',
			},
		];

		( models || [] ).forEach( function ( model ) {
			options.push( {
				label: model.name + ' (' + model.id + ')',
				value: model.id,
			} );
		} );

		return options;
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
						el( SelectControl, {
							value: textModel,
							options: getOptions( data.textModels ),
							onChange: setTextModel,
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
						el( SelectControl, {
							value: imageModel,
							options: getOptions( data.imageModels ),
							onChange: setImageModel,
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

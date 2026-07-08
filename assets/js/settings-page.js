( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.components || ! wp.element || ! wp.domReady ) {
		return;
	}

	var Button = wp.components.Button;
	var Card = wp.components.Card;
	var CardBody = wp.components.CardBody;
	var Notice = wp.components.Notice;
	var Panel = wp.components.Panel;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var createElement = wp.element.createElement;
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

		return createElement(
			'div',
			{ className: 'forgeia-nvidia-settings-app' },
			data.loadError
				? createElement(
						Notice,
						{
							status: 'warning',
							isDismissible: false,
						},
						labels.loadingFailed || 'Model list could not be loaded. Saved values remain available and can still be saved.'
				  )
				: null,
			createElement(
				Card,
				{ className: 'forgeia-nvidia-settings-card' },
				createElement(
					CardBody,
					null,
					createElement(
						Panel,
						null,
						createElement(
							PanelBody,
							{
								title: labels.heading || 'Default models',
								initialOpen: true,
							},
					createElement(
						'p',
						{ className: 'forgeia-nvidia-settings-description' },
						labels.description || 'Choose which NVIDIA models should be preferred when the AI Client auto-discovers a text or image generation model.'
					),
					createElement( 'input', {
						type: 'hidden',
						name: hiddenInputName( textKey ),
						value: textModel,
					} ),
					createElement( SelectControl, {
						label: labels.textLabel || 'Text generation model',
						value: textModel,
						options: getOptions( data.textModels ),
						onChange: setTextModel,
					} ),
					createElement( 'input', {
						type: 'hidden',
						name: hiddenInputName( imageKey ),
						value: imageModel,
					} ),
					createElement( SelectControl, {
						label: labels.imageLabel || 'Image generation model',
						value: imageModel,
						options: getOptions( data.imageModels ),
						onChange: setImageModel,
					} ),
					createElement(
						Button,
						{
							variant: 'primary',
							type: 'submit',
						},
						labels.saveButton || 'Save settings'
					)
				)
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
			wp.element.createRoot( root ).render( createElement( App ) );
			return;
		}

		wp.element.render( createElement( App ), root );
	} );
} )( window.wp );

/**
 * Special Offer — Gutenberg block editor script (no build step).
 *
 * Uses wp.element.createElement (no JSX) + ServerSideRender so the editor
 * preview matches production. The block is dynamic; save() returns null.
 */
( function ( blocks, element, blockEditor, components, ServerSideRender, apiFetch, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var useState = element.useState;
	var useEffect = element.useEffect;
	var __ = i18n.__;

	blocks.registerBlockType( 'amazing-offer/special-offer', {
		apiVersion: 2,
		title: __( 'پیشنهاد ویژه', 'amazing-offer' ),
		description: __( 'نمایش یک طرح «پیشنهاد ویژه».', 'amazing-offer' ),
		icon: 'megaphone',
		category: 'widgets',
		keywords: [ 'offer', 'sale', 'تخفیف', 'پیشنهاد' ],
		attributes: {
			templateId: { type: 'number', 'default': 0 }
		},

		edit: function ( props ) {
			var templateId = props.attributes.templateId || 0;

			var optState = useState( [] );
			var options = optState[ 0 ];
			var setOptions = optState[ 1 ];

			useEffect( function () {
				apiFetch( { path: 'amazing-offer/v1/special-offers' } ).then( function ( items ) {
					setOptions( ( items || [] ).map( function ( t ) {
						var label = t.title + ( t.active ? '' : ' ' + __( '(غیرفعال)', 'amazing-offer' ) ) + ' (#' + t.id + ')';
						return { label: label, value: String( t.id ) };
					} ) );
				} ).catch( function () {
					setOptions( [] );
				} );
			}, [] );

			var controls = el(
				blockEditor.InspectorControls,
				{},
				el(
					components.PanelBody,
					{ title: __( 'تنظیمات طرح', 'amazing-offer' ), initialOpen: true },
					el( components.SelectControl, {
						label: __( 'انتخاب طرح', 'amazing-offer' ),
						value: String( templateId ),
						options: [ { label: __( '— انتخاب طرح —', 'amazing-offer' ), value: '0' } ].concat( options ),
						onChange: function ( v ) {
							props.setAttributes( { templateId: parseInt( v, 10 ) || 0 } );
						}
					} )
				)
			);

			var body;
			if ( templateId ) {
				body = el( ServerSideRender, {
					block: 'amazing-offer/special-offer',
					attributes: { templateId: templateId }
				} );
			} else {
				body = el(
					components.Placeholder,
					{ icon: 'megaphone', label: __( 'پیشنهاد ویژه', 'amazing-offer' ) },
					__( 'یک طرح را از نوار کناری انتخاب کنید.', 'amazing-offer' )
				);
			}

			return el( Fragment, {}, controls, body );
		},

		save: function () {
			return null;
		}
	} );

} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.apiFetch,
	window.wp.i18n
);

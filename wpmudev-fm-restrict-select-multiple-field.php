<?php
/**
 * Plugin Name: [Forminator Pro] - Restrict select the multiple field(s).
 * Description: [Forminator Pro] - Restrict select the multiple field(s) (select/checkbox).
 * Author: Thobk @ WPMUDEV
 * Jira: SLS-918
 * Author URI: https://premium.wpmudev.org
 * License: GPLv2 or later
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} elseif ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}
/**
 * 1. Add custom class wpmudev-option-limit inside STYLING tab of a multiple field's settings: https://share.getcloudapp.com/KoulYdY9
 * 2. Enter the form id(s) in the snippet code bellow that you want to apply this custom code: form_ids: [123, 456]
 * 3. Modify the limit for each MU field:
 * limit: {
 * 'checkbox-2': 5,//[field-id]:[limit]
 * 'select-1': 2
 * }
 */
add_action( 'wp_footer', function(){

	global $post;

	if( ! $post instanceof WP_Post || ! has_shortcode( $post->post_content, 'forminator_form' ) ) {
		return;
	}

	?>
	<style>
		.forminator-ui .wpmudev-option-limit .wpmudev-disabled{
			color:#ddd!important;
		}
		.forminator-ui .wpmudev-option-limit .wpmudev-disabled span[aria-hidden]{
			border-color: #ddd!important;
    	background-color: #ddd!important;
		}
	</style>
	<script type="text/javascript">
		
		($=>{

			const _forminator_restrict_multiple_fields = {
				form_ids: [791, 9034, 64],
				limit: {
					'checkbox-1': 1,//[field-id]:[limit]
					'checkbox-3': 4,
					'checkbox-5': 7,
				},
				run : function( e, form_id ) {
					if( _forminator_restrict_multiple_fields.form_ids.indexOf( form_id ) === -1 ){
						return;
					}
					let _form = $( "#forminator-module-" + form_id );

					_form.find('.wpmudev-option-limit').each(function(){
						let _field = $(this),
								checkbox_fields = _field.find( ":checkbox" );
						if( checkbox_fields.length ){
							checkbox_fields.on('change', function (e) {
								let _parent = $(this).closest('.wpmudev-option-limit'),
										_parent_id = _parent.attr('id'),
										_selected = _parent.find(':checkbox:checked').length;
								if( _parent_id in _forminator_restrict_multiple_fields.limit && _selected >= _forminator_restrict_multiple_fields.limit[ _parent_id ]){

									// save latest value.
									_field.data('latest_value', $(this).val() );
									// disable other options.
									_parent.find(':checkbox:not(:checked)').each(function(){
										$(this).prop('disabled', true).parent().addClass('wpmudev-disabled');
									});
								}else{
									_parent.find(':checkbox:disabled').each(function(){
										$(this).prop('disabled', false).parent().removeClass('wpmudev-disabled');
									});

									_field.removeData('latest_value');
								}
							});
						}

						// auto remove previous value when riched the limit.
						$(this).on('click', '.wpmudev-disabled', function(){
							let _latest_value = _field.data('latest_value') ;
							if( _latest_value ){
								let _previous_opt = $(this).closest('.wpmudev-option-limit').find('input[value="'+ _latest_value +'"');
								if( _previous_opt.length ){
									_previous_opt.trigger('click');
									$(this).removeClass('wpmudev-disabled').find('input:disabled').removeAttr('disabled');
								}
							}
						})
					});
				}
			}

			$(document).ready(function(){
				$.each(_forminator_restrict_multiple_fields.form_ids, function(i, _form_id){
					_forminator_restrict_multiple_fields.run(this,_form_id);
				});
				$(document).on( 'response.success.load.forminator', _forminator_restrict_multiple_fields.run );
			});
		})(jQuery);

	</script>

	<?php
}, 999 );